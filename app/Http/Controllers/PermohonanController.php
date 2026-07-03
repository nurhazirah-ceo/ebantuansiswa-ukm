<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\PermohonanBantuan;
use App\Models\PermohonanDokumen;
use App\Models\PermohonanKeluarga;
use App\Models\PermohonanPelajar;
use App\Models\Item;
use App\Support\AssistanceCatalog;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class PermohonanController extends Controller
{
    public function index(Request $request)
    {
        $learningItems = AssistanceCatalog::activeItems(Item::CATEGORY_PEMBELAJARAN);

        return view('pelajar.permohonan.permohonan-pelajar', [
            'lockedBantuanTypes' => Permohonan::bantuanLocksForUser(Auth::id()),
            'selectedJenis' => $request->query('jenis', $request->query('jenis_bantuan')),
            'selectedKategori' => $request->query('kategori'),
            'selectedItem' => $request->query('item'),
            'basicPackages' => AssistanceCatalog::activeItems(Item::CATEGORY_KEPERLUAN_ASAS),
            'learningItems' => AssistanceCatalog::learningStationeryItems($learningItems),
            'learningEquipmentItems' => AssistanceCatalog::learningEquipmentItems($learningItems),
            'sportsItems' => AssistanceCatalog::activeItems(Item::CATEGORY_SUKAN),
        ]);
    }

    public function store(Request $request)
    {
        $student = Auth::user();
        $studentMatrik = strtoupper((string) $student->matrik);
        $studentEmail = strtolower((string) $student->email);
        $studentFaculty = trim((string) $student->fakulti);

        if ($studentFaculty === '') {
            return back()
                ->withErrors([
                    'fakulti' => 'Sila kemas kini fakulti dalam profil pelajar sebelum menghantar permohonan.',
                ])
                ->withInput();
        }

        $request->merge([
            'no_matrik' => strtoupper(trim((string) $request->input('no_matrik'))),
            'email_ukm' => strtolower(trim((string) $request->input('email_ukm'))),
            'fakulti' => $studentFaculty,
        ]);

        $requestedJenisBantuan = (string) $request->input('jenis_bantuan');

        if (Permohonan::isBantuanTypeLockedForUser(Auth::id(), $requestedJenisBantuan)) {
            return back()
                ->withErrors([
                    'jenis_bantuan' => Permohonan::jenisBantuanLabel($requestedJenisBantuan)
                        .' telah diluluskan atau sedang disemak pada semester ini. Sila pilih bantuan lain yang layak.',
                ])
                ->withInput();
        }

        $validated = $request->validate([
            // STEP 1
            'nama_penuh' => 'required|string|max:255',
            'no_matrik' => ['required', 'regex:/^[Aa][0-9]{6}$/', Rule::in([$studentMatrik])],
            'email_ukm' => ['required', 'email', 'max:255', Rule::in([$studentEmail])],
            'no_telefon' => ['required', 'regex:/^01[0-9]{8,9}$/'],
            'fakulti' => ['required', 'string', 'max:255', Rule::in([$studentFaculty])],
            'tahun_pengajian' => ['required', 'string', Rule::in(['Tahun 1', 'Tahun 2', 'Tahun 3', 'Tahun 4'])],

            // STEP 2
            'penjaga_nama' => 'required|string|max:255',
            'penjaga_no_kp' => 'required|digits:12',
            'penjaga_hubungan' => 'required|string|max:100',
            'penjaga_telefon' => ['required', 'regex:/^01[0-9]{8,9}$/'],
            'penjaga_pekerjaan' => 'required|string|max:255',
            'penjaga_pendapatan' => 'required|numeric|min:0',

            'tanggungan' => 'nullable|array',
            'tanggungan.*.nama' => 'required|string|max:255',
            'tanggungan.*.hubungan' => 'required|string|max:100',
            'tanggungan.*.umur' => 'nullable|integer|min:0|max:120',
            'tanggungan.*.status' => 'nullable|string|max:100',
            'tanggungan.*.kesihatan' => 'nullable|string|max:100',
            'tanggungan.*.pendapatan' => 'nullable|numeric|min:0',

            // STEP 3
            'jenis_bantuan' => 'required|string|in:bantuan_asas_hidup,bantuan_pembelajaran,bantuan_sukan,bantuan_musibah',
            'kategori_bantuan' => 'required|string|in:keperluan_asas,alat_tulis_pembelajaran,peralatan_pembelajaran,sukan',
            'bantuan_data' => 'required|array',

            'bantuan_data.pakej_item_id' => [
                Rule::requiredIf(fn () => $request->input('kategori_bantuan') === 'keperluan_asas'),
                'nullable',
                'integer',
                Rule::exists('items', 'id')->where(fn ($query) => $query
                    ->where('kategori_bantuan', Item::CATEGORY_KEPERLUAN_ASAS)
                    ->where('is_active', true)
                    ->where('status', 'aktif')),
            ],
            'bantuan_data.pakej' => [
                'nullable',
                'string',
                'max:255',
            ],
            'bantuan_data.jumlah_ahli' => [
                Rule::requiredIf(fn () => $request->input('kategori_bantuan') === 'keperluan_asas'),
                'nullable',
                'integer',
                'min:0',
                'max:999',
            ],
            'bantuan_data.nama_ketua' => [
                Rule::requiredIf(fn () => $request->input('kategori_bantuan') === 'keperluan_asas'),
                'nullable',
                'string',
                'max:255',
            ],
            'bantuan_data.no_matrik_ketua' => [
                Rule::requiredIf(fn () => $request->input('kategori_bantuan') === 'keperluan_asas'),
                'nullable',
                'regex:/^[Aa][0-9]{6}$/',
            ],
            'bantuan_data.alamat_rumah' => [
                Rule::requiredIf(fn () => $request->input('kategori_bantuan') === 'keperluan_asas'),
                'nullable',
                'string',
                'max:1000',
            ],
            'bantuan_data.bandar' => [
                Rule::requiredIf(fn () => $request->input('kategori_bantuan') === 'keperluan_asas'),
                'nullable',
                'string',
                'max:150',
            ],
            'bantuan_data.poskod' => [
                Rule::requiredIf(fn () => $request->input('kategori_bantuan') === 'keperluan_asas'),
                'nullable',
                'digits:5',
            ],
            'bantuan_data.negeri' => [
                Rule::requiredIf(fn () => $request->input('kategori_bantuan') === 'keperluan_asas'),
                'nullable',
                'string',
                'max:100',
            ],
            'bantuan_data.jenis_kediaman' => [
                Rule::requiredIf(fn () => $request->input('kategori_bantuan') === 'keperluan_asas'),
                'nullable',
                'string',
                Rule::in(['Rumah Sewa', 'Kolej Kediaman', 'Rumah Keluarga']),
            ],
            'bantuan_data.ahli_rumah' => 'nullable|array',
            'bantuan_data.ahli_rumah.*.nama' => 'required_with:bantuan_data.ahli_rumah|string|max:255',
            'bantuan_data.ahli_rumah.*.no_matrik' => ['required_with:bantuan_data.ahli_rumah', 'regex:/^[Aa][0-9]{6}$/'],
            'bantuan_data.ahli_rumah.*.fakulti' => 'required_with:bantuan_data.ahli_rumah|string|max:255',

            'bantuan_data.learning_type' => [
                Rule::requiredIf(fn () => $request->input('kategori_bantuan') === 'alat_tulis_pembelajaran'),
                'nullable',
                Rule::in(['individu', 'group']),
            ],
            'bantuan_data.individu.items' => 'nullable|array',
            'bantuan_data.individu.items.*.item_id' => 'nullable|integer',
            'bantuan_data.individu.items.*.selected' => 'nullable|string|max:150',
            'bantuan_data.individu.items.*.qty' => 'nullable|integer|min:1|max:999',
            'bantuan_data.individu.justifikasi' => [
                Rule::requiredIf(fn () => $request->input('kategori_bantuan') === 'alat_tulis_pembelajaran'
                    && $request->input('bantuan_data.learning_type') === 'individu'),
                'nullable',
                'string',
                'max:2000',
            ],
            'bantuan_data.group.nama_group' => [
                Rule::requiredIf(fn () => $request->input('kategori_bantuan') === 'alat_tulis_pembelajaran'
                    && $request->input('bantuan_data.learning_type') === 'group'),
                'nullable',
                'string',
                'max:255',
            ],
            'bantuan_data.group.bil_ahli' => [
                Rule::requiredIf(fn () => $request->input('kategori_bantuan') === 'alat_tulis_pembelajaran'
                    && $request->input('bantuan_data.learning_type') === 'group'),
                'nullable',
                'integer',
                'min:1',
                'max:999',
            ],
            'bantuan_data.group.items' => 'nullable|array',
            'bantuan_data.group.items.*.item_id' => 'nullable|integer',
            'bantuan_data.group.items.*.selected' => 'nullable|string|max:150',
            'bantuan_data.group.items.*.qty' => 'nullable|integer|min:1|max:999',

            'bantuan_data.peralatan' => [
                Rule::requiredIf(fn () => $request->input('kategori_bantuan') === 'peralatan_pembelajaran'),
                'nullable',
                'string',
                'max:150',
            ],
            'bantuan_data.sebab' => [
                Rule::requiredIf(fn () => $request->input('kategori_bantuan') === 'peralatan_pembelajaran'),
                'nullable',
                'string',
                Rule::in(['Rosak', 'Tiada kemampuan membeli', 'Lain-lain']),
            ],
            'bantuan_data.justifikasi' => [
                Rule::requiredIf(fn () => in_array($request->input('kategori_bantuan'), ['peralatan_pembelajaran', 'sukan'], true)),
                'nullable',
                'string',
                'max:2000',
            ],

            'bantuan_data.peringkat' => [
                Rule::requiredIf(fn () => $request->input('kategori_bantuan') === 'sukan'),
                'nullable',
                Rule::in(['fakulti', 'universiti', 'kebangsaan', 'antarabangsa']),
            ],
            'bantuan_data.nama_kelab_pasukan' => [
                Rule::requiredIf(fn () => $request->input('kategori_bantuan') === 'sukan'),
                'nullable',
                'string',
                'max:255',
            ],
            'bantuan_data.bilangan_peserta' => [
                Rule::requiredIf(fn () => $request->input('kategori_bantuan') === 'sukan'),
                'nullable',
                'integer',
                'min:1',
                'max:999',
            ],
            'bantuan_data.items' => 'nullable|array',
            'bantuan_data.items.*.item_id' => 'nullable|integer',
            'bantuan_data.items.*.selected' => 'nullable|string|max:150',
            'bantuan_data.items.*.qty' => 'nullable|integer|min:1|max:999',

            // STEP 4
            'dokumen_tambahan' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',

            'dokumen_wajib' => 'required|array|size:2',
            'dokumen_wajib.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120',

            'dokumen_wajib.dokumen_1' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',

            'dokumen_wajib.dokumen_2' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'no_matrik.in' => 'No matrik mesti sepadan dengan akaun pelajar yang sedang log masuk.',
            'email_ukm.in' => 'Email UKM mesti sepadan dengan akaun pelajar yang sedang log masuk.',
            'bantuan_data.individu.justifikasi.required' => 'Sila isi Justifikasi Ringkas sebelum meneruskan permohonan.',
            'bantuan_data.individu.justifikasi.max' => 'Justifikasi Ringkas tidak boleh melebihi 2000 aksara.',
            'bantuan_data.justifikasi.required' => 'Sila isi Justifikasi Ringkas sebelum meneruskan permohonan.',
            'bantuan_data.justifikasi.max' => 'Justifikasi Ringkas tidak boleh melebihi 2000 aksara.',
        ]);

        $validated['no_matrik'] = $studentMatrik;
        $validated['email_ukm'] = $studentEmail;
        $validated['fakulti'] = $studentFaculty;

        $kategoriByJenis = [
            'bantuan_asas_hidup' => ['keperluan_asas'],
            'bantuan_pembelajaran' => ['alat_tulis_pembelajaran', 'peralatan_pembelajaran'],
            'bantuan_sukan' => ['sukan'],
            'bantuan_musibah' => ['keperluan_asas', 'peralatan_pembelajaran'],
        ];

        if (! in_array($validated['kategori_bantuan'], $kategoriByJenis[$validated['jenis_bantuan']] ?? [], true)) {
            return back()
                ->withErrors(['kategori_bantuan' => 'Kategori bantuan tidak sah untuk jenis bantuan yang dipilih.'])
                ->withInput();
        }

        $aidSpecificErrors = $this->aidSpecificValidationErrors($validated);

        if ($aidSpecificErrors !== []) {
            return back()
                ->withErrors($aidSpecificErrors)
                ->withInput();
        }

        $prefix = match ($validated['kategori_bantuan']) {
            'keperluan_asas' => 'KA',
            'alat_tulis_pembelajaran' => 'AT',
            'peralatan_pembelajaran' => 'PL',
            'sukan' => 'SK',
            default => 'PM',
        };

        $bantuanData = $this->normalizeBantuanData(
            $validated['kategori_bantuan'],
            $validated['bantuan_data'] ?? []
        );

        if (isset($bantuanData['no_matrik_ketua'])) {
            $bantuanData['no_matrik_ketua'] = $studentMatrik;
        }

        if (isset($bantuanData['individu']) && is_array($bantuanData['individu'])) {
            $bantuanData['individu']['no_matrik'] = $studentMatrik;
        }

        unset(
            $bantuanData['group']['nama_wakil'],
            $bantuanData['group']['no_matrik_wakil'],
            $bantuanData['nama_wakil'],
            $bantuanData['no_matrik_wakil'],
            $bantuanData['jawatan']
        );

        if ($validated['kategori_bantuan'] === 'sukan') {
            unset($bantuanData['kategori'], $bantuanData['nama_organisasi'], $bantuanData['tarikh']);
        }

        $storedFiles = [];

        try {
            DB::transaction(function () use ($request, $validated, $prefix, $bantuanData, &$storedFiles) {
                $permohonan = Permohonan::create([
                    'user_id' => Auth::id(),
                    'no_kelompok' => $this->temporaryNoKelompok(),
                    'tarikh_mohon' => now(),
                    'jenis_bantuan' => $validated['jenis_bantuan'],
                    'status' => 'Sedang Disemak',
                    'catatan' => 'Permohonan sedang disemak secara keseluruhan.',
                ]);

                $permohonan->update([
                    'no_kelompok' => $this->finalNoKelompok($prefix, $permohonan->id),
                ]);

                PermohonanPelajar::create([
                    'permohonan_id' => $permohonan->id,
                    'nama_penuh' => $validated['nama_penuh'],
                    'no_matrik' => strtoupper($validated['no_matrik']),
                    'email_ukm' => $validated['email_ukm'],
                    'no_telefon' => $validated['no_telefon'],
                    'fakulti' => $validated['fakulti'],
                    'tahun_pengajian' => $validated['tahun_pengajian'],
                ]);

                PermohonanKeluarga::create([
                    'permohonan_id' => $permohonan->id,
                    'jenis' => 'penjaga',
                    'nama' => $validated['penjaga_nama'],
                    'no_kp' => $validated['penjaga_no_kp'],
                    'hubungan' => $validated['penjaga_hubungan'],
                    'telefon' => $validated['penjaga_telefon'],
                    'pekerjaan' => $validated['penjaga_pekerjaan'],
                    'umur' => null,
                    'status' => null,
                    'kesihatan' => null,
                    'pendapatan' => $validated['penjaga_pendapatan'],
                ]);

                foreach ($request->input('tanggungan', []) as $item) {
                    PermohonanKeluarga::create([
                        'permohonan_id' => $permohonan->id,
                        'jenis' => 'tanggungan',
                        'nama' => $item['nama'],
                        'no_kp' => null,
                        'hubungan' => $item['hubungan'],
                        'umur' => $item['umur'] ?? null,
                        'status' => $item['status'] ?? null,
                        'kesihatan' => $item['kesihatan'] ?? null,
                        'pendapatan' => $item['pendapatan'] ?? 0,
                    ]);
                }

                PermohonanBantuan::create([
                    'permohonan_id' => $permohonan->id,
                    'jenis_bantuan' => $validated['jenis_bantuan'],
                    'kategori_bantuan' => $validated['kategori_bantuan'],
                    'data' => $bantuanData,
                ]);

                foreach ($request->file('dokumen_wajib', []) as $jenis => $file) {
                    $path = $this->storeApplicationDocument($permohonan, $file, $jenis);
                    $storedFiles[] = ['disk' => 'local', 'path' => $path];

                    PermohonanDokumen::create([
                        'permohonan_id' => $permohonan->id,
                        'jenis_dokumen' => ucwords(str_replace('_', ' ', $jenis)),
                        'file_path' => $path,
                    ]);
                }

                if ($request->hasFile('dokumen_tambahan')) {
                    $path = $this->storeApplicationDocument(
                        $permohonan,
                        $request->file('dokumen_tambahan'),
                        'dokumen_tambahan'
                    );
                    $storedFiles[] = ['disk' => 'local', 'path' => $path];

                    PermohonanDokumen::create([
                        'permohonan_id' => $permohonan->id,
                        'jenis_dokumen' => 'Dokumen Tambahan',
                        'file_path' => $path,
                    ]);
                }
            });
        } catch (\Throwable $exception) {
            foreach ($storedFiles as $storedFile) {
                Storage::disk($storedFile['disk'])->delete($storedFile['path']);
            }

            throw $exception;
        }

        return back()
            ->with('success', 'Permohonan berjaya dihantar!')
            ->with('sweet_alert_redirect_url', route('status-permohonan.index'));
    }

    private function aidSpecificValidationErrors(array $validated): array
    {
        $data = $validated['bantuan_data'] ?? [];

        return match ($validated['kategori_bantuan']) {
            'keperluan_asas' => $this->basicAidValidationErrors($data),
            'alat_tulis_pembelajaran' => $this->learningAidValidationErrors($data),
            'peralatan_pembelajaran' => $this->singleItemValidationErrors(
                $data['peralatan'] ?? null,
                'bantuan_data.peralatan',
                AssistanceCatalog::learningEquipmentItems(),
                'Sila pilih peralatan pembelajaran yang aktif.'
            ),
            'sukan' => $this->selectedItemsValidationErrors(
                $data['items'] ?? [],
                'bantuan_data.items',
                'Sila pilih sekurang-kurangnya satu item peralatan sukan.',
                AssistanceCatalog::activeItems(Item::CATEGORY_SUKAN)
            ),
            default => [],
        };
    }

    private function basicAidValidationErrors(array $data): array
    {
        $errors = [];
        $package = Item::query()
            ->aktif()
            ->kategoriBantuan(Item::CATEGORY_KEPERLUAN_ASAS)
            ->find($data['pakej_item_id'] ?? null);

        if (! $package) {
            return ['bantuan_data.pakej_item_id' => 'Sila pilih pakej bantuan yang aktif.'];
        }

        $packageLimit = AssistanceCatalog::basicPackageLimit($package);
        $memberCount = count($data['ahli_rumah'] ?? []);

        if ($packageLimit > 0 && $memberCount > $packageLimit) {
            $errors['bantuan_data.ahli_rumah'] = 'Jumlah ahli rumah melebihi had pakej bantuan yang dipilih.';
        }

        if ((int) ($data['jumlah_ahli'] ?? 0) > $packageLimit) {
            $errors['bantuan_data.jumlah_ahli'] = 'Jumlah ahli rumah melebihi had pakej bantuan yang dipilih.';
        }

        return $errors;
    }

    private function learningAidValidationErrors(array $data): array
    {
        if (($data['learning_type'] ?? null) === 'group') {
            return $this->selectedItemsValidationErrors(
                $data['group']['items'] ?? [],
                'bantuan_data.group.items',
                'Sila pilih sekurang-kurangnya satu item pembelajaran untuk kelab, persatuan atau kelas.',
                AssistanceCatalog::learningStationeryItems()
            );
        }

        return $this->selectedItemsValidationErrors(
            $data['individu']['items'] ?? [],
            'bantuan_data.individu.items',
            'Sila pilih sekurang-kurangnya satu item pembelajaran untuk permohonan individu.',
            AssistanceCatalog::learningStationeryItems()
        );
    }

    private function selectedItemsValidationErrors(
        array $items,
        string $field,
        string $requiredMessage,
        ?Collection $allowedItems = null
    ): array
    {
        $errors = [];
        $allowedItems ??= collect();
        $allowedIds = $allowedItems->keyBy('id');
        $allowedNames = $allowedItems
            ->mapWithKeys(fn (Item $item) => [$this->normalizeItemName($item->nama_item) => $item])
            ->all();

        $selectedItems = collect($items)
            ->filter(fn ($item) => filled($item['selected'] ?? null));

        if ($selectedItems->isEmpty()) {
            return [$field => $requiredMessage];
        }

        foreach ($selectedItems as $index => $item) {
            if (! isset($item['qty']) || (int) $item['qty'] < 1) {
                $errors[$field.'.'.$index.'.qty'] = 'Kuantiti item yang dipilih mestilah sekurang-kurangnya 1.';
            }

            if ($allowedItems->isNotEmpty()) {
                $itemId = (int) ($item['item_id'] ?? 0);
                $selectedName = $this->normalizeItemName($item['selected'] ?? '');
                $isAllowed = $itemId > 0
                    ? $allowedIds->has($itemId)
                    : array_key_exists($selectedName, $allowedNames);

                if (! $isAllowed) {
                    $errors[$field.'.'.$index.'.selected'] = 'Item yang dipilih tidak lagi aktif dalam katalog bantuan.';
                }
            }
        }

        return $errors;
    }

    private function singleItemValidationErrors(?string $selected, string $field, Collection $allowedItems, string $message): array
    {
        if (blank($selected)) {
            return [$field => $message];
        }

        $normalized = $this->normalizeItemName($selected);
        $exists = $allowedItems
            ->contains(fn (Item $item) => $this->normalizeItemName($item->nama_item) === $normalized);

        return $exists ? [] : [$field => 'Item yang dipilih tidak lagi aktif dalam katalog bantuan.'];
    }

    private function normalizeBantuanData(string $kategoriBantuan, array $data): array
    {
        if ($kategoriBantuan === 'keperluan_asas') {
            $package = Item::query()
                ->aktif()
                ->kategoriBantuan(Item::CATEGORY_KEPERLUAN_ASAS)
                ->find($data['pakej_item_id'] ?? null);

            if ($package) {
                $data['pakej_item_id'] = $package->id;
                $data['pakej'] = $package->nama_item;
                $data['pakej_limit'] = AssistanceCatalog::basicPackageLimit($package);
            }
        }

        if ($kategoriBantuan === 'alat_tulis_pembelajaran') {
            $allowedItems = AssistanceCatalog::learningStationeryItems()->keyBy('id');
            $type = $data['learning_type'] ?? 'individu';
            $bucket = $type === 'group' ? 'group' : 'individu';

            $data[$bucket]['items'] = $this->normalizeSelectedItems(
                $data[$bucket]['items'] ?? [],
                $allowedItems
            );
        }

        if ($kategoriBantuan === 'peralatan_pembelajaran') {
            $equipment = $this->findAllowedItemByName($data['peralatan'] ?? null, AssistanceCatalog::learningEquipmentItems());

            if ($equipment) {
                $data['peralatan_item_id'] = $equipment->id;
                $data['peralatan'] = $equipment->nama_item;
            }
        }

        if ($kategoriBantuan === 'sukan') {
            $data['items'] = $this->normalizeSelectedItems(
                $data['items'] ?? [],
                AssistanceCatalog::activeItems(Item::CATEGORY_SUKAN)->keyBy('id')
            );
        }

        return $data;
    }

    private function normalizeSelectedItems(array $items, Collection $allowedItemsById): array
    {
        return collect($items)
            ->map(function (array $item) use ($allowedItemsById) {
                $catalogItem = $allowedItemsById->get((int) ($item['item_id'] ?? 0));

                if ($catalogItem && filled($item['selected'] ?? null)) {
                    $item['item_id'] = $catalogItem->id;
                    $item['selected'] = $catalogItem->nama_item;
                }

                return $item;
            })
            ->all();
    }

    private function findAllowedItemByName(?string $name, Collection $allowedItems): ?Item
    {
        $normalized = $this->normalizeItemName($name);

        return $allowedItems
            ->first(fn (Item $item) => $this->normalizeItemName($item->nama_item) === $normalized);
    }

    private function normalizeItemName(?string $name): string
    {
        return (string) str($name ?? '')
            ->squish()
            ->lower();
    }

    private function temporaryNoKelompok(): string
    {
        return 'TMP-'.(string) Str::uuid();
    }

    private function finalNoKelompok(string $prefix, int $id): string
    {
        return sprintf('%s-%s-%06d', $prefix, now()->format('Y'), $id);
    }

    private function storeApplicationDocument(Permohonan $permohonan, UploadedFile $file, string $documentKey): string
    {
        return Storage::disk('local')->putFileAs(
            $this->applicationDocumentDirectory($permohonan),
            $file,
            $this->applicationDocumentFileName($documentKey, $file)
        );
    }

    private function applicationDocumentDirectory(Permohonan $permohonan): string
    {
        return sprintf(
            'dokumen_permohonan/user_%d/permohonan_%d',
            $permohonan->user_id,
            $permohonan->id
        );
    }

    private function applicationDocumentFileName(string $documentKey, UploadedFile $file): string
    {
        $extension = strtolower($file->extension() ?: $file->getClientOriginalExtension());
        $extension = $extension === 'jpeg' ? 'jpg' : $extension;

        return $documentKey.'.'.$extension;
    }
}
