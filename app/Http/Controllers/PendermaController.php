<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\CashDonation;
use App\Models\Item;
use App\Models\Permohonan;
use App\Models\Sumbangan;
use App\Models\User;
use App\Support\DonorRecognition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PendermaController extends Controller
{
    public function sumbangan()
    {
        return view('penderma.sumbangan.index');
    }

    public function keperluanSumbang()
    {
        return $this->categoryPage(
            Item::CATEGORY_KEPERLUAN_ASAS,
            'penderma.sumbangan.keperluan-sumbang'
        );
    }

    public function pembelajaranSumbang()
    {
        return $this->categoryPage(
            Item::CATEGORY_PEMBELAJARAN,
            'penderma.sumbangan.pembelajaran-sumbang'
        );
    }

    public function sukanSumbang()
    {
        return $this->categoryPage(
            Item::CATEGORY_SUKAN,
            'penderma.sumbangan.sukan-sumbang'
        );
    }

    public function menyumbangBantuan()
    {
        $items = Item::query()
            ->aktif()
            ->orderBy('susunan')
            ->orderBy('nama_item')
            ->get();

        return view('penderma.menyumbang.index', compact('items'));
    }

    public function hantarBarang()
    {
        return view('penderma.menyumbang.hantar-barang');
    }

    public function checkoutSumbangan()
    {
        $user = Auth::user();
        $user?->loadMissing('donor.address');

        return view('penderma.menyumbang.checkout', [
            'donorProfile' => $this->donorProfileForCheckout($user),
        ]);
    }

    public function pembayaranSandbox()
    {
        return redirect()
            ->route('penderma.checkout-sumbangan')
            ->with('payment_info', 'Sila lengkapkan pembayaran melalui ToyyibPay.');
    }

    public function pembayaranSimulasi()
    {
        return redirect()
            ->route('penderma.checkout-sumbangan')
            ->with('payment_info', 'Pembayaran simulasi tidak lagi digunakan. Sila teruskan melalui ToyyibPay.');
    }

    public function pembayaranSimulasiSuccess(Request $request)
    {
        return redirect()
            ->route('penderma.checkout-sumbangan')
            ->with('payment_error', 'Pembayaran simulasi telah dinyahaktifkan. Sila teruskan melalui ToyyibPay.');
    }

    public function pembayaranSimulasiCancel(Request $request)
    {
        return redirect()
            ->route('penderma.checkout-sumbangan')
            ->with('payment_info', 'Pembayaran atas talian dibatalkan sebelum ToyyibPay dimulakan.');
    }

    public function storeSumbangan(Request $request)
    {
        $request->merge([
            'items' => $this->cartItemsFromRequest($request),
        ]);

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:999999'],
            'kaedah_sumbangan' => ['required', 'string', 'max:100'],
            'donor' => ['required', 'array'],
            'donor.name' => ['required', 'string', 'max:255'],
            'donor.email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore(Auth::id()),
            ],
            'donor.phone' => ['required', 'string', 'regex:/^01\d{8,9}$/', 'max:30'],
            'donor.alt_phone' => ['nullable', 'string', 'regex:/^01\d{8,9}$/', 'max:30'],
            'donor.address' => ['required', 'string', 'max:500'],
            'donor.city' => ['required', 'string', 'max:100'],
            'donor.postcode' => ['required', 'string', 'regex:/^\d{5}$/', 'max:20'],
            'donor.state' => ['required', 'string', 'max:100'],
            'donor.country' => ['required', 'string', 'max:100'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ], [
            'items.required' => 'Sila tambah sekurang-kurangnya satu item sumbangan.',
            'items.min' => 'Sila tambah sekurang-kurangnya satu item sumbangan.',
            'kaedah_sumbangan.required' => 'Sila pilih cara sumbangan.',
            'donor.required' => 'Maklumat penyumbang diperlukan.',
            'donor.name.required' => 'Nama penyumbang diperlukan.',
            'donor.email.required' => 'Emel penyumbang diperlukan.',
            'donor.email.email' => 'Emel penyumbang mesti menggunakan format yang sah.',
            'donor.email.unique' => 'Emel ini telah digunakan oleh akaun lain.',
            'donor.phone.required' => 'Nombor telefon diperlukan.',
            'donor.phone.regex' => 'Sila masukkan nombor telefon yang sah. Contoh: 0123456789',
            'donor.alt_phone.regex' => 'Sila masukkan nombor telefon alternatif yang sah. Contoh: 0123456789',
            'donor.address.required' => 'Alamat diperlukan.',
            'donor.city.required' => 'Bandar diperlukan.',
            'donor.postcode.required' => 'Poskod diperlukan.',
            'donor.postcode.regex' => 'Sila masukkan poskod yang sah (5 digit).',
            'donor.state.required' => 'Negeri diperlukan.',
            'donor.country.required' => 'Negara diperlukan.',
        ]);

        $validated['donor'] = $this->normalizeDonorSnapshot($validated['donor']);
        $validated['catatan'] = $this->buildSumbanganCatatan(
            $validated['donor'],
            $validated['catatan'] ?? null
        );
        $this->assertToyyibPayConfigured();

        $cartItems = collect($validated['items'])
            ->map(fn (array $item) => [
                'id' => (int) $item['id'],
                'qty' => (int) $item['qty'],
            ])
            ->groupBy('id')
            ->map(fn ($group, $id) => [
                'id' => (int) $id,
                'qty' => $group->sum('qty'),
            ])
            ->values();

        if ($cartItems->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Sila tambah sekurang-kurangnya satu item sumbangan.',
            ]);
        }

        $sumbangan = DB::transaction(function () use ($cartItems, $validated) {
            $itemIds = $cartItems->pluck('id');
            $items = Item::query()
                ->whereIn('id', $itemIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $missingIds = $itemIds
                ->reject(fn (int $id) => $items->has($id))
                ->values();

            if ($missingIds->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'items' => 'Item sumbangan tidak ditemui: ' . $missingIds->implode(', '),
                ]);
            }

            $inactiveItem = $items->first(fn (Item $item) => ! $item->is_active || $item->status !== 'aktif');

            if ($inactiveItem) {
                throw ValidationException::withMessages([
                    'items' => 'Item "' . $inactiveItem->nama_item . '" tidak aktif untuk sumbangan.',
                ]);
            }

            $jumlahUnit = (int) $cartItems->sum('qty');
            $jumlahKeseluruhan = $cartItems->reduce(function (float $total, array $cartItem) use ($items) {
                $item = $items->get($cartItem['id']);

                return $total + ((float) $item->harga * (int) $cartItem['qty']);
            }, 0.0);

            if ($jumlahKeseluruhan <= 0) {
                throw ValidationException::withMessages([
                    'items' => 'Jumlah sumbangan mesti melebihi RM0.00.',
                ]);
            }

            $sumbangan = Sumbangan::create([
                'user_id' => Auth::id(),
                'jumlah_unit' => $jumlahUnit,
                'jumlah_keseluruhan' => $jumlahKeseluruhan,
                'status' => Sumbangan::STATUS_MENUNGGU_BAYARAN,
                'kaedah_sumbangan' => $validated['kaedah_sumbangan'] ?? null,
                'catatan' => $validated['catatan'] ?? null,
                'donor_snapshot' => $validated['donor'],
            ]);

            $sumbangan->update([
                'no_sumbangan' => $this->generateNoSumbangan($sumbangan),
            ]);

            $cartItems->each(function (array $cartItem) use ($items, $sumbangan) {
                $item = $items->get($cartItem['id']);
                $qty = (int) $cartItem['qty'];
                $hargaUnit = (float) $item->harga;

                $sumbangan->items()->create([
                    'item_id' => $item->id,
                    'nama_item' => $item->nama_item,
                    'kategori_bantuan' => $item->kategori_bantuan,
                    'harga_unit' => $hargaUnit,
                    'kuantiti' => $qty,
                    'jumlah' => $hargaUnit * $qty,
                ]);
            });

            $this->syncDonorProfileFromSnapshot(Auth::user(), $validated['donor']);

            return $sumbangan->load('items');
        });

        try {
            $bill = $this->createToyyibPayBill($sumbangan, $validated['donor']);

            $sumbangan->update([
                'toyyibpay_bill_code' => $bill['bill_code'],
                'payment_status' => 'bill_created',
                'payment_payload' => $bill['raw'],
            ]);
        } catch (ValidationException $exception) {
            $sumbangan->update([
                'status' => 'dibatalkan',
                'payment_status' => 'bill_creation_failed',
                'payment_payload' => [
                    'errors' => $exception->errors(),
                ],
                'cancelled_at' => now(),
            ]);

            throw $exception;
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pautan pembayaran ToyyibPay telah dicipta. Sila lengkapkan bayaran di ToyyibPay.',
                'payment_url' => $bill['payment_url'],
                'redirect_url' => $bill['payment_url'],
                'sumbangan' => [
                    'id' => $sumbangan->id,
                    'no_sumbangan' => $sumbangan->no_sumbangan,
                    'jumlah_unit' => $sumbangan->jumlah_unit,
                    'jumlah_keseluruhan' => $sumbangan->jumlah_keseluruhan,
                    'status' => $sumbangan->status,
                    'toyyibpay_bill_code' => $bill['bill_code'],
                ],
            ]);
        }

        return redirect()->away($bill['payment_url']);
    }

    public function sejarahSumbangan()
    {
        $records = $this->donorDonationHistoryRecords();

        $historyStats = [
            'active_count' => $records->where('status_category', 'pending')->count(),
            'completed_count' => $records->where('status_category', 'success')->count(),
            'total_amount' => (float) $records->sum('amount'),
        ];

        return view('penderma.sejarah-sumbangan.index', compact('records', 'historyStats'));
    }

    public function exportSejarahSumbangan()
    {
        $rows = $this->donorDonationHistoryRecords()
            ->map(fn (array $record) => [
                $record['no_sumbangan'],
                $record['type_label'],
                $record['date_label'],
                $record['unit_label'],
                'RM' . number_format((float) $record['amount'], 2),
                $record['status_label'],
            ])
            ->values()
            ->all();

        $xlsx = $this->buildSimpleXlsx([
            'ID Sumbangan',
            'Jenis',
            'Tarikh',
            'Jumlah Unit',
            'Jumlah',
            'Status',
        ], $rows);

        return response($xlsx, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="sejarah-sumbangan-penderma.xlsx"',
            'Content-Length' => strlen($xlsx),
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    public function sejarahSumbanganShow($id)
    {
        $sumbangan = Sumbangan::query()
            ->with('items.item')
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        $distributionImpact = $this->distributionImpactFor($sumbangan);

        return view('penderma.sejarah-sumbangan.show', compact('sumbangan', 'distributionImpact'));
    }

    private function donorDonationHistoryRecords()
    {
        $itemDonationRecords = Sumbangan::query()
            ->with('items')
            ->where('user_id', Auth::id())
            ->orderByRaw('COALESCE(paid_at, created_at) DESC')
            ->orderByDesc('id')
            ->get()
            ->map(function (Sumbangan $record) {
                $statusCategory = $record->status === Sumbangan::STATUS_SELESAI
                    ? 'success'
                    : (in_array($record->status, Sumbangan::PENDING_CONFIRMATION_STATUSES, true) ? 'pending' : 'failed');

                $noSumbangan = $record->no_sumbangan ?? ('SMB-' . str_pad($record->id, 6, '0', STR_PAD_LEFT));
                $transactionAt = $record->paid_at ?? $record->created_at;

                return [
                    'id' => 'item-' . $record->id,
                    'type' => 'item',
                    'type_label' => 'Sumbangan Barang',
                    'no_sumbangan' => $noSumbangan,
                    'date' => $transactionAt,
                    'date_label' => optional($transactionAt)->format('d/m/Y'),
                    'sort_timestamp' => $transactionAt?->getTimestamp() ?? 0,
                    'sort_id' => (int) $record->id,
                    'unit_label' => number_format((int) $record->jumlah_unit) . ' Unit',
                    'amount' => (float) $record->jumlah_keseluruhan,
                    'status_key' => $record->status,
                    'status_label' => $this->sumbanganStatusLabel($record->status),
                    'status_class' => $this->sumbanganStatusClass($record->status),
                    'status_category' => $statusCategory,
                    'view_url' => route('penderma.sejarah-sumbangan.show', ['id' => $record->id]),
                    'action_label' => 'Lihat',
                    'search_text' => strtolower($noSumbangan . ' Sumbangan Barang ' . optional($transactionAt)->format('d/m/Y') . ' ' . $record->jumlah_unit . ' ' . $record->jumlah_keseluruhan . ' ' . $this->sumbanganStatusLabel($record->status)),
                ];
            });

        $cashDonationRecords = CashDonation::query()
            ->where('user_id', Auth::id())
            ->orderByRaw('COALESCE(paid_at, created_at) DESC')
            ->orderByDesc('id')
            ->get()
            ->map(function (CashDonation $record) {
                $reference = $this->cashDonationReference($record);
                $transactionAt = $record->paid_at ?? $record->created_at;

                return [
                    'id' => 'cash-' . $record->id,
                    'type' => 'cash',
                    'type_label' => 'Sumbangan Tabung',
                    'no_sumbangan' => $reference,
                    'date' => $transactionAt,
                    'date_label' => optional($transactionAt)->format('d/m/Y'),
                    'sort_timestamp' => $transactionAt?->getTimestamp() ?? 0,
                    'sort_id' => (int) $record->id,
                    'unit_label' => '-',
                    'amount' => (float) $record->amount,
                    'status_key' => $record->payment_status,
                    'status_label' => $this->cashDonationStatusLabel($record->payment_status),
                    'status_class' => $this->cashDonationStatusClass($record->payment_status),
                    'status_category' => $record->payment_status === CashDonation::STATUS_SUCCESS
                        ? 'success'
                        : ($record->payment_status === CashDonation::STATUS_PENDING ? 'pending' : 'failed'),
                    'view_url' => route('penderma.tabung.receipt', $record),
                    'action_label' => 'Lihat',
                    'search_text' => strtolower($reference . ' Sumbangan Tabung ' . optional($transactionAt)->format('d/m/Y') . ' ' . $record->amount . ' ' . $this->cashDonationStatusLabel($record->payment_status)),
                ];
            });

        return $itemDonationRecords
            ->concat($cashDonationRecords)
            ->sort(fn (array $left, array $right) => [
                $right['sort_timestamp'] ?? 0,
                $right['sort_id'] ?? 0,
            ] <=> [
                $left['sort_timestamp'] ?? 0,
                $left['sort_id'] ?? 0,
            ])
            ->values();
    }

    private function buildSimpleXlsx(array $headings, array $rows): string
    {
        $sheetRows = [$this->xlsxRow(1, $headings)];

        foreach ($rows as $index => $row) {
            $sheetRows[] = $this->xlsxRow($index + 2, $row);
        }

        $sheetData = implode('', $sheetRows);

        return $this->buildStoredZip([
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
                . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
                . '<Default Extension="xml" ContentType="application/xml"/>'
                . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
                . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
                . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
                . '</Types>',
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
                . '</Relationships>',
            'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
                . '<sheets><sheet name="Sejarah Sumbangan" sheetId="1" r:id="rId1"/></sheets>'
                . '</workbook>',
            'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
                . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
                . '</Relationships>',
            'xl/styles.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
                . '<fonts count="1"><font><sz val="11"/><name val="Calibri"/></font></fonts>'
                . '<fills count="1"><fill><patternFill patternType="none"/></fill></fills>'
                . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
                . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
                . '<cellXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/></cellXfs>'
                . '</styleSheet>',
            'xl/worksheets/sheet1.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
                . '<cols><col min="1" max="1" width="24" customWidth="1"/><col min="2" max="2" width="20" customWidth="1"/><col min="3" max="6" width="16" customWidth="1"/></cols>'
                . '<sheetData>' . $sheetData . '</sheetData>'
                . '</worksheet>',
        ]);
    }

    private function xlsxRow(int $rowNumber, array $values): string
    {
        $cells = '';

        foreach (array_values($values) as $index => $value) {
            $cellReference = $this->excelColumnName($index + 1) . $rowNumber;
            $cells .= '<c r="' . $cellReference . '" t="inlineStr"><is><t>'
                . $this->escapeXml((string) $value)
                . '</t></is></c>';
        }

        return '<row r="' . $rowNumber . '">' . $cells . '</row>';
    }

    private function excelColumnName(int $columnNumber): string
    {
        $name = '';

        while ($columnNumber > 0) {
            $columnNumber--;
            $name = chr(65 + ($columnNumber % 26)) . $name;
            $columnNumber = intdiv($columnNumber, 26);
        }

        return $name;
    }

    private function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function buildStoredZip(array $files): string
    {
        $localFiles = '';
        $centralDirectory = '';
        $offset = 0;
        $fileCount = count($files);
        $date = getdate();
        $dosTime = ($date['hours'] << 11) | ($date['minutes'] << 5) | intdiv($date['seconds'], 2);
        $dosDate = (($date['year'] - 1980) << 9) | ($date['mon'] << 5) | $date['mday'];

        foreach ($files as $name => $contents) {
            $name = str_replace('\\', '/', $name);
            $contents = (string) $contents;
            $nameLength = strlen($name);
            $contentLength = strlen($contents);
            $crc = (int) hexdec(hash('crc32b', $contents));

            $localHeader = pack(
                'VvvvvvVVVvv',
                0x04034b50,
                20,
                0,
                0,
                $dosTime,
                $dosDate,
                $crc,
                $contentLength,
                $contentLength,
                $nameLength,
                0
            ) . $name . $contents;

            $centralDirectory .= pack(
                'VvvvvvvVVVvvvvvVV',
                0x02014b50,
                20,
                20,
                0,
                0,
                $dosTime,
                $dosDate,
                $crc,
                $contentLength,
                $contentLength,
                $nameLength,
                0,
                0,
                0,
                0,
                0,
                $offset
            ) . $name;

            $localFiles .= $localHeader;
            $offset += strlen($localHeader);
        }

        $endCentralDirectory = pack(
            'VvvvvVVv',
            0x06054b50,
            0,
            0,
            $fileCount,
            $fileCount,
            strlen($centralDirectory),
            strlen($localFiles),
            0
        );

        return $localFiles . $centralDirectory . $endCentralDirectory;
    }

    public function receiptSumbangan($id)
    {
        $sumbangan = $this->receiptSumbanganRecord($id);

        if ($sumbangan->status !== 'selesai') {
            return redirect()
                ->route('penderma.sejarah-sumbangan.show', ['id' => $sumbangan->id])
                ->with('payment_info', 'Pembayaran sumbangan ini belum disahkan sebagai berjaya.');
        }

        return view('penderma.menyumbang.receipt', compact('sumbangan'));
    }

    public function downloadReceiptSumbangan($id)
    {
        $sumbangan = $this->receiptSumbanganRecord($id);

        if ($sumbangan->status !== 'selesai') {
            return redirect()
                ->route('penderma.sejarah-sumbangan.show', ['id' => $sumbangan->id])
                ->with('payment_info', 'Pembayaran sumbangan ini belum disahkan sebagai berjaya.');
        }

        $noSumbangan = $sumbangan->no_sumbangan ?? ('SMB-' . str_pad($sumbangan->id, 6, '0', STR_PAD_LEFT));
        $fileName = 'resit-sumbangan-' . Str::of($noSumbangan)
            ->replace(['/', '\\'], '-')
            ->replaceMatches('/[^A-Za-z0-9\-]+/', '-')
            ->trim('-')
            ->toString() . '.pdf';

        return Pdf::loadView('penderma.menyumbang.receipt-pdf', [
            'sumbangan' => $sumbangan,
        ])
            ->setPaper('a4')
            ->download($fileName);
    }

    private function receiptSumbanganRecord($id): Sumbangan
    {
        return Sumbangan::query()
            ->with(['items.item', 'user'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);
    }

    public function sijilPenghargaan()
    {
        return $this->downloadSijilPenghargaan();
    }

    public function downloadSijilPenghargaan()
    {
        $user = Auth::user();
        $totalCompletedAmount = (float) Sumbangan::query()
            ->where('user_id', $user->id)
            ->where('status', 'selesai')
            ->sum('jumlah_keseluruhan')
            + (float) CashDonation::query()
                ->where('user_id', $user->id)
                ->successful()
                ->sum('amount');

        if ($totalCompletedAmount <= 0) {
            return redirect()
                ->route('dashboard.penderma')
                ->with('info', 'Sijil penghargaan tersedia selepas sumbangan pertama anda selesai direkodkan.');
        }

        $level = DonorRecognition::tierForAmount($totalCompletedAmount);
        $fileName = 'sijil-penghargaan-' . now()->format('Ymd') . '.pdf';

        return Pdf::loadView('penderma.sijil-penghargaan.certificate', [
            'donorName' => $user->name ?? 'Penderma',
            'recognitionTier' => $level,
            'certificateTemplate' => DonorRecognition::certificateTemplateForTier($level),
            'totalDonationAmount' => $totalCompletedAmount,
            'generatedDate' => now()->format('d/m/Y'),
            'logoDataUri' => $this->certificateLogoDataUri(),
        ])
            ->setPaper('a4', 'landscape')
            ->download($fileName);
    }

    public function buktiAgihan(Sumbangan $sumbangan, Permohonan $permohonan)
    {
        abort_unless((int) $sumbangan->user_id === (int) Auth::id(), 404);
        abort_unless($sumbangan->status === 'selesai', 403);

        $permohonan->loadMissing('bantuan');

        abort_unless($permohonan->status_agihan_key === Permohonan::STATUS_AGIHAN_SELESAI, 404);
        abort_unless(filled($permohonan->bukti_agihan), 404);

        $matchingCategories = Permohonan::kategoriBantuanMatchesForDonationCategories(
            $this->donationCategoriesFor($sumbangan)
        );

        abort_unless(
            in_array($permohonan->bantuan?->kategori_bantuan, $matchingCategories, true),
            403
        );
        abort_unless(Storage::disk('local')->exists($permohonan->bukti_agihan), 404);

        return Storage::disk('local')->response($permohonan->bukti_agihan);
    }

    public function toyyibPayCallback(Request $request)
    {
        $payload = $request->all();

        if (! $this->isValidToyyibPayHash($payload)) {
            Log::warning('ToyyibPay callback rejected because hash validation failed.', [
                'payload' => $payload,
            ]);

            return response('Invalid callback hash', 403);
        }

        $sumbangan = $this->findSumbanganForToyyibPayPayload($payload);

        if (! $sumbangan) {
            Log::warning('ToyyibPay callback received for unknown donation.', [
                'payload' => $payload,
            ]);

            return response('Donation not found', 404);
        }

        $this->applyToyyibPayPaymentStatus($sumbangan, $this->paymentDataFromToyyibPayPayload($payload));

        return response('OK');
    }

    public function toyyibPayReturn(Request $request)
    {
        $payload = $request->all();
        $sumbangan = $this->findSumbanganForToyyibPayPayload($payload);

        if (! $sumbangan || (int) $sumbangan->user_id !== (int) Auth::id()) {
            return redirect()
                ->route('penderma.sejarah-sumbangan')
                ->with('payment_error', 'Rekod pembayaran ToyyibPay tidak ditemui.');
        }

        $paymentData = $this->paymentDataFromToyyibPayPayload($payload);
        $transaction = $this->fetchToyyibPayTransaction($paymentData['bill_code']);

        if ($transaction) {
            $paymentData = $this->paymentDataFromToyyibPayPayload(array_merge(
                $transaction,
                [
                    'billcode' => $paymentData['bill_code'],
                    'order_id' => $paymentData['order_id'] ?: ($transaction['billExternalReferenceNo'] ?? null),
                    'return_payload' => $payload,
                ]
            ));
        }

        if ($transaction || $this->isFailedPaymentStatus($paymentData['status'])) {
            $this->applyToyyibPayPaymentStatus($sumbangan, $paymentData);
        }

        $sumbangan->refresh();

        if ($sumbangan->status === 'selesai') {
            return redirect()
                ->route('penderma.sumbangan.receipt', ['id' => $sumbangan->id])
                ->with('payment_success', 'Sumbangan berjaya direkodkan.')
                ->with('clear_checkout', true);
        }

        if ($sumbangan->status === 'dibatalkan') {
            return redirect()
                ->route('penderma.sejarah-sumbangan.show', ['id' => $sumbangan->id])
                ->with('payment_error', 'Pembayaran tidak berjaya atau telah dibatalkan.');
        }

        return redirect()
            ->route('penderma.sejarah-sumbangan.show', ['id' => $sumbangan->id])
            ->with('payment_info', 'Pembayaran sedang disahkan. Sila semak semula sebentar lagi.');
    }

    private function categoryPage(string $kategoriBantuan, string $view)
    {
        $items = Item::query()
            ->aktif()
            ->kategoriBantuan($kategoriBantuan)
            ->orderBy('susunan')
            ->orderBy('nama_item')
            ->get();

        return view($view, [
            'items' => $items,
            'categoryKey' => $kategoriBantuan,
        ]);
    }

    private function distributionImpactFor(Sumbangan $sumbangan)
    {
        if ($sumbangan->status !== 'selesai') {
            return collect();
        }

        $donationCategories = $this->donationCategoriesFor($sumbangan);
        $matchingCategories = Permohonan::kategoriBantuanMatchesForDonationCategories($donationCategories);

        if (empty($matchingCategories)) {
            return collect();
        }

        return Permohonan::query()
            ->with([
                'pelajar:id,permohonan_id,nama_penuh,no_matrik,fakulti',
                'bantuan:id,permohonan_id,jenis_bantuan,kategori_bantuan',
            ])
            ->where('status_agihan', Permohonan::STATUS_AGIHAN_SELESAI)
            ->whereNotNull('tarikh_agihan')
            ->whereHas('bantuan', function ($query) use ($matchingCategories) {
                $query->whereIn('kategori_bantuan', $matchingCategories);
            })
            ->latest('tarikh_agihan')
            ->latest('id')
            ->get()
            ->map(function (Permohonan $permohonan) use ($sumbangan) {
                return [
                    'id' => $permohonan->id,
                    'masked_name' => $this->maskStudentName($permohonan->pelajar?->nama_penuh),
                    'masked_no_matrik' => $this->maskNoMatrik($permohonan->pelajar?->no_matrik),
                    'fakulti' => $permohonan->pelajar?->fakulti ?: '-',
                    'jenis_bantuan' => Permohonan::jenisBantuanLabel($permohonan->bantuan?->jenis_bantuan ?? $permohonan->jenis_bantuan),
                    'kategori_bantuan' => Permohonan::kategoriBantuanLabel($permohonan->bantuan?->kategori_bantuan),
                    'tarikh_agihan' => $permohonan->tarikh_agihan?->format('d/m/Y h:i A') ?? '-',
                    'status' => 'Telah Disalurkan',
                    'bukti_url' => filled($permohonan->bukti_agihan)
                        ? route('penderma.agihan-bukti', ['sumbangan' => $sumbangan, 'permohonan' => $permohonan])
                        : null,
                ];
            });
    }

    private function donationCategoriesFor(Sumbangan $sumbangan): array
    {
        $items = $sumbangan->relationLoaded('items')
            ? $sumbangan->items
            : $sumbangan->items()->get(['kategori_bantuan']);

        return $items
            ->pluck('kategori_bantuan')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function maskStudentName(?string $name): string
    {
        $parts = Str::of($name ?? '')
            ->squish()
            ->explode(' ')
            ->filter(fn (string $part) => $part !== '')
            ->values();

        if ($parts->isEmpty()) {
            return 'Pelajar';
        }

        return $parts
            ->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)) . '****')
            ->implode(' ');
    }

    private function maskNoMatrik(?string $noMatrik): string
    {
        $clean = Str::of($noMatrik ?? '')
            ->squish()
            ->upper()
            ->toString();

        if ($clean === '') {
            return '-';
        }

        return Str::substr($clean, 0, 3) . '****';
    }

    private function certificateLogoDataUri(): ?string
    {
        $path = public_path('image/branding/ebantuansiswa.jpg');

        if (! is_readable($path)) {
            return null;
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'image/jpeg';

        return 'data:' . $mime . ';base64,' . base64_encode($contents);
    }

    private function assertToyyibPayConfigured(): void
    {
        if (blank(config('services.toyyibpay.secret_key')) || blank(config('services.toyyibpay.category_code'))) {
            throw ValidationException::withMessages([
                'toyyibpay' => 'Tetapan ToyyibPay belum lengkap. Sila tetapkan TOYYIBPAY_SECRET_KEY dan TOYYIBPAY_CATEGORY_CODE.',
            ]);
        }
    }

    private function createToyyibPayBill(Sumbangan $sumbangan, array $donor): array
    {
        $this->assertToyyibPayConfigured();

        $billCodeReference = $sumbangan->no_sumbangan ?: ('SMB-' . $sumbangan->id);
        $amountInCents = (int) round(((float) $sumbangan->jumlah_keseluruhan) * 100);
        $payload = [
            'userSecretKey' => config('services.toyyibpay.secret_key'),
            'categoryCode' => config('services.toyyibpay.category_code'),
            'billName' => $this->toyyibPayText('Sumbangan ' . $sumbangan->id, 30),
            'billDescription' => $this->toyyibPayText('Sumbangan eBantuanSiswa UKM ' . $billCodeReference, 100),
            'billPriceSetting' => 1,
            'billPayorInfo' => 1,
            'billAmount' => $amountInCents,
            'billReturnUrl' => route('penderma.toyyibpay.return'),
            'billCallbackUrl' => route('penderma.toyyibpay.callback'),
            'billExternalReferenceNo' => $billCodeReference,
            'billTo' => $donor['name'] ?? Auth::user()?->name ?? 'Penderma',
            'billEmail' => $donor['email'] ?? Auth::user()?->email ?? '',
            'billPhone' => $donor['phone'] ?? '',
            'billSplitPayment' => 0,
            'billSplitPaymentArgs' => '',
            'billPaymentChannel' => config('services.toyyibpay.payment_channel', '0'),
            'billContentEmail' => 'Terima kasih atas sumbangan anda.',
            'billChargeToCustomer' => config('services.toyyibpay.charge_to_customer', '1'),
        ];

        try {
            $response = Http::asForm()
                ->timeout(30)
                ->post($this->toyyibPayApiUrl('createBill'), $payload);
        } catch (\Throwable $exception) {
            throw ValidationException::withMessages([
                'toyyibpay' => 'ToyyibPay tidak dapat dihubungi. Sila cuba lagi.',
            ]);
        }

        $data = $response->json();
        $billCode = is_array($data)
            ? (data_get($data, '0.BillCode') ?: data_get($data, 'BillCode'))
            : null;

        if (! $response->successful() || blank($billCode)) {
            throw ValidationException::withMessages([
                'toyyibpay' => $this->toyyibPayErrorMessage($data),
            ]);
        }

        return [
            'bill_code' => $billCode,
            'payment_url' => $this->toyyibPayPaymentUrl($billCode),
            'raw' => [
                'request' => collect($payload)
                    ->except('userSecretKey')
                    ->all(),
                'response' => $data,
            ],
        ];
    }

    private function toyyibPayApiUrl(string $endpoint): string
    {
        return rtrim((string) config('services.toyyibpay.base_url', 'https://dev.toyyibpay.com'), '/')
            . '/index.php/api/'
            . ltrim($endpoint, '/');
    }

    private function toyyibPayPaymentUrl(string $billCode): string
    {
        return rtrim((string) config('services.toyyibpay.base_url', 'https://dev.toyyibpay.com'), '/')
            . '/'
            . ltrim($billCode, '/');
    }

    private function toyyibPayText(string $value, int $limit): string
    {
        $clean = preg_replace('/[^A-Za-z0-9 _]+/', ' ', $value) ?: 'Sumbangan';
        $clean = trim(preg_replace('/\s+/', ' ', $clean) ?: 'Sumbangan');

        return Str::limit($clean !== '' ? $clean : 'Sumbangan', $limit, '');
    }

    private function toyyibPayErrorMessage(mixed $data): string
    {
        if (is_array($data)) {
            $message = data_get($data, '0.msg')
                ?: data_get($data, '0.Message')
                ?: data_get($data, 'msg')
                ?: data_get($data, 'Message')
                ?: data_get($data, '0.error')
                ?: data_get($data, 'error');

            if (filled($message)) {
                return (string) $message;
            }
        }

        return 'Bill ToyyibPay tidak dapat dicipta. Sila cuba lagi.';
    }

    private function isValidToyyibPayHash(array $payload): bool
    {
        $receivedHash = (string) ($payload['hash'] ?? '');

        if ($receivedHash === '') {
            return false;
        }

        $expectedHash = md5(
            (string) config('services.toyyibpay.secret_key')
            . (string) ($payload['status'] ?? $payload['status_id'] ?? '')
            . (string) ($payload['order_id'] ?? '')
            . (string) ($payload['refno'] ?? '')
            . 'ok'
        );

        return hash_equals($expectedHash, $receivedHash);
    }

    private function findSumbanganForToyyibPayPayload(array $payload): ?Sumbangan
    {
        $paymentData = $this->paymentDataFromToyyibPayPayload($payload);

        if (blank($paymentData['bill_code']) && blank($paymentData['order_id'])) {
            return null;
        }

        return Sumbangan::query()
            ->with('items')
            ->where(function ($query) use ($paymentData) {
                $query
                    ->when($paymentData['bill_code'], fn ($inner, string $billCode) => $inner->where('toyyibpay_bill_code', $billCode))
                    ->when($paymentData['order_id'], fn ($inner, string $orderId) => $inner->orWhere('no_sumbangan', $orderId));
            })
            ->first();
    }

    private function fetchToyyibPayTransaction(?string $billCode): ?array
    {
        if (blank($billCode) || blank(config('services.toyyibpay.secret_key'))) {
            return null;
        }

        try {
            $response = Http::asForm()
                ->timeout(20)
                ->post($this->toyyibPayApiUrl('getBillTransactions'), [
                    'billCode' => $billCode,
                ]);
        } catch (\Throwable $exception) {
            Log::warning('ToyyibPay transaction lookup failed.', [
                'bill_code' => $billCode,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();

        return is_array($data) ? data_get($data, '0') : null;
    }

    private function paymentDataFromToyyibPayPayload(array $payload): array
    {
        $status = (string) (
            $payload['status']
            ?? $payload['status_id']
            ?? $payload['billpaymentStatus']
            ?? ''
        );

        return [
            'status' => $status,
            'bill_code' => $payload['billcode'] ?? $payload['billCode'] ?? $payload['BillCode'] ?? null,
            'order_id' => $payload['order_id'] ?? $payload['billExternalReferenceNo'] ?? null,
            'reference' => $payload['refno']
                ?? $payload['transaction_id']
                ?? $payload['billpaymentInvoiceNo']
                ?? $payload['fpx_transaction_id']
                ?? null,
            'amount' => $payload['amount'] ?? $payload['billpaymentAmount'] ?? null,
            'raw' => $payload,
        ];
    }

    private function applyToyyibPayPaymentStatus(Sumbangan $sumbangan, array $paymentData): void
    {
        if ($this->isSuccessfulPaymentStatus($paymentData['status'])) {
            $this->markSumbanganAsPaid($sumbangan, $paymentData);

            return;
        }

        if ($this->isFailedPaymentStatus($paymentData['status'])) {
            $this->markSumbanganAsCancelled($sumbangan, $paymentData);

            return;
        }

        $sumbangan->update([
            'payment_status' => $paymentData['status'] ?: 'pending',
            'payment_reference' => $paymentData['reference'] ?: $sumbangan->payment_reference,
            'payment_payload' => $paymentData['raw'],
        ]);
    }

    private function markSumbanganAsPaid(Sumbangan $sumbangan, array $paymentData): void
    {
        DB::transaction(function () use ($sumbangan, $paymentData) {
            $locked = Sumbangan::query()
                ->with('items')
                ->lockForUpdate()
                ->findOrFail($sumbangan->id);

            if ($locked->status !== 'selesai') {
                $items = Item::query()
                    ->whereIn('id', $locked->items->pluck('item_id')->filter()->all())
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($locked->items as $sumbanganItem) {
                    $item = $items->get($sumbanganItem->item_id);

                    if ($item) {
                        $item->increment('stok_disumbang', (int) $sumbanganItem->kuantiti);
                    }
                }
            }

            $locked->update([
                'no_sumbangan' => $locked->no_sumbangan ?: $this->generateNoSumbangan($locked),
                'status' => 'selesai',
                'payment_status' => $paymentData['status'] ?: '1',
                'payment_reference' => $paymentData['reference'] ?: $locked->payment_reference,
                'toyyibpay_bill_code' => $paymentData['bill_code'] ?: $locked->toyyibpay_bill_code,
                'payment_payload' => $paymentData['raw'],
                'paid_at' => $locked->paid_at ?: now(),
                'cancelled_at' => null,
            ]);
        });
    }

    private function markSumbanganAsCancelled(Sumbangan $sumbangan, array $paymentData): void
    {
        DB::transaction(function () use ($sumbangan, $paymentData) {
            $locked = Sumbangan::query()
                ->lockForUpdate()
                ->findOrFail($sumbangan->id);

            if ($locked->status === 'selesai') {
                return;
            }

            $locked->update([
                'status' => 'dibatalkan',
                'payment_status' => $paymentData['status'] ?: '3',
                'payment_reference' => $paymentData['reference'] ?: $locked->payment_reference,
                'toyyibpay_bill_code' => $paymentData['bill_code'] ?: $locked->toyyibpay_bill_code,
                'payment_payload' => $paymentData['raw'],
                'cancelled_at' => $locked->cancelled_at ?: now(),
            ]);
        });
    }

    private function isSuccessfulPaymentStatus(?string $status): bool
    {
        return (string) $status === '1';
    }

    private function isFailedPaymentStatus(?string $status): bool
    {
        return (string) $status === '3';
    }

    private function cartItemsFromRequest(Request $request): array
    {
        $items = $request->input('items');

        if (is_array($items)) {
            return $items;
        }

        $payload = $request->input('cart_payload');

        if (! is_string($payload) || trim($payload) === '') {
            return [];
        }

        $decoded = json_decode($payload, true);

        return json_last_error() === JSON_ERROR_NONE && is_array($decoded)
            ? $decoded
            : [];
    }

    private function donorProfileForCheckout(?User $user): array
    {
        $donor = $user?->donor;
        $address = $donor?->address;

        return [
            'name' => $user?->name ?? '',
            'email' => $user?->email ?? '',
            'phone' => $donor?->phone ?? '',
            'alt_phone' => $donor?->alt_phone ?? '',
            'address' => collect([
                $address?->address_line_1,
                $address?->address_line_2,
            ])
                ->filter(fn ($line) => filled($line))
                ->implode("\n"),
            'city' => $address?->city ?? '',
            'postcode' => $address?->postcode ?? '',
            'state' => $address?->state ?? '',
            'country' => $address?->country ?? 'Malaysia',
        ];
    }

    private function normalizeDonorSnapshot(array $donor): array
    {
        return [
            'name' => trim((string) ($donor['name'] ?? '')),
            'email' => trim((string) ($donor['email'] ?? '')),
            'phone' => trim((string) ($donor['phone'] ?? '')),
            'alt_phone' => trim((string) ($donor['alt_phone'] ?? '')),
            'address' => trim((string) ($donor['address'] ?? '')),
            'city' => trim((string) ($donor['city'] ?? '')),
            'postcode' => trim((string) ($donor['postcode'] ?? '')),
            'state' => trim((string) ($donor['state'] ?? '')),
            'country' => trim((string) ($donor['country'] ?? '')),
        ];
    }

    private function syncDonorProfileFromSnapshot(?User $user, array $snapshot): void
    {
        if (! $user) {
            return;
        }

        $user->loadMissing('donor.address');

        $user->update([
            'name' => $snapshot['name'],
            'email' => $snapshot['email'],
        ]);

        $donor = $user->donor;

        if (! $donor) {
            return;
        }

        $donor->update([
            'phone' => $snapshot['phone'],
            'alt_phone' => $snapshot['alt_phone'] !== '' ? $snapshot['alt_phone'] : null,
        ]);

        $donor->address()->updateOrCreate(
            ['donor_id' => $donor->id],
            array_merge(
                $this->addressLinesFromSnapshot($snapshot['address']),
                [
                    'city' => $snapshot['city'],
                    'postcode' => $snapshot['postcode'],
                    'state' => $snapshot['state'],
                    'country' => $snapshot['country'],
                ]
            )
        );
    }

    private function addressLinesFromSnapshot(string $address): array
    {
        $normalized = trim((string) preg_replace("/\r\n?/", "\n", $address));
        $lines = collect(explode("\n", $normalized))
            ->map(fn (string $line) => trim($line))
            ->filter()
            ->values();

        $firstLine = $lines->first() ?: $normalized;
        $line1 = Str::limit($firstLine, 255, '');
        $overflow = strlen($firstLine) > strlen($line1)
            ? trim(substr($firstLine, strlen($line1)))
            : '';
        $line2 = collect([
            $overflow,
            $lines->slice(1)->implode(', '),
        ])
            ->filter(fn (string $line) => $line !== '')
            ->implode(', ');

        if ($line2 === '' && strlen($normalized) > strlen($line1)) {
            $line2 = trim(substr($normalized, strlen($line1)));
        }

        return [
            'address_line_1' => $line1,
            'address_line_2' => $line2 !== '' ? Str::limit($line2, 255, '') : null,
        ];
    }

    private function buildSumbanganCatatan(array $donor, ?string $extraNote = null): string
    {
        $phone = trim((string) ($donor['phone'] ?? ''));
        $altPhone = trim((string) ($donor['alt_phone'] ?? ''));
        $address = trim((string) ($donor['address'] ?? ''));
        $city = trim((string) ($donor['city'] ?? ''));
        $postcode = trim((string) ($donor['postcode'] ?? ''));
        $state = trim((string) ($donor['state'] ?? ''));
        $country = trim((string) ($donor['country'] ?? ''));
        $cityPostcode = trim($postcode . ' ' . $city);
        $addressParts = collect([$address, $cityPostcode, $state, $country])
            ->filter(fn (string $part) => $part !== '')
            ->implode(', ');

        $lines = [
            $phone !== '' ? 'Telefon: ' . $phone : '',
            $altPhone !== '' ? 'Telefon alternatif: ' . $altPhone : '',
            'Alamat: ' . $addressParts,
            trim((string) $extraNote),
        ];

        return collect($lines)
            ->filter(fn (string $line) => $line !== '')
            ->implode("\n");
    }

    private function generateNoSumbangan(Sumbangan $sumbangan): string
    {
        return sprintf('SMB/%s/%06d', now()->format('Ymd'), $sumbangan->id);
    }

    private function sumbanganStatusLabel(?string $status): string
    {
        return match ($status) {
            Sumbangan::STATUS_SELESAI => 'Selesai',
            Sumbangan::STATUS_MENUNGGU_BAYARAN => 'Menunggu Bayaran',
            Sumbangan::STATUS_DALAM_SEMAKAN => 'Dalam Semakan',
            'menunggu_penghantaran' => 'Menunggu Penghantaran',
            'dibatalkan' => 'Dibatalkan',
            'ditolak' => 'Ditolak',
            default => filled($status)
                ? Str::of($status)->replace(['_', '-'], ' ')->squish()->title()->toString()
                : 'Belum Lengkap',
        };
    }

    private function sumbanganStatusClass(?string $status): string
    {
        return match ($status) {
            Sumbangan::STATUS_SELESAI => 'bg-green-100 text-green-700',
            Sumbangan::STATUS_MENUNGGU_BAYARAN => 'bg-amber-100 text-amber-700',
            Sumbangan::STATUS_DALAM_SEMAKAN => 'bg-yellow-100 text-yellow-700',
            'menunggu_penghantaran' => 'bg-blue-100 text-blue-700',
            'dibatalkan', 'ditolak' => 'bg-red-100 text-red-700',
            default => 'bg-slate-100 text-slate-700',
        };
    }

    private function cashDonationStatusLabel(?string $status): string
    {
        return match ($status) {
            CashDonation::STATUS_SUCCESS => 'Selesai',
            CashDonation::STATUS_PENDING => 'Menunggu Bayaran',
            CashDonation::STATUS_FAILED => 'Gagal',
            default => filled($status)
                ? Str::of($status)->replace(['_', '-'], ' ')->squish()->title()->toString()
                : 'Belum Lengkap',
        };
    }

    private function cashDonationStatusClass(?string $status): string
    {
        return match ($status) {
            CashDonation::STATUS_SUCCESS => 'bg-green-100 text-green-700',
            CashDonation::STATUS_PENDING => 'bg-amber-100 text-amber-700',
            CashDonation::STATUS_FAILED => 'bg-red-100 text-red-700',
            default => 'bg-slate-100 text-slate-700',
        };
    }

    private function cashDonationReference(CashDonation $cashDonation): string
    {
        return sprintf('TAB/%s/%06d', ($cashDonation->created_at ?? now())->format('Ymd'), $cashDonation->id);
    }
}
