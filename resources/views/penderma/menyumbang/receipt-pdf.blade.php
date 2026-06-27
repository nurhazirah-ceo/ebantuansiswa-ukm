@php
    $receiptDate = $sumbangan->paid_at ?? $sumbangan->updated_at ?? $sumbangan->created_at;
    $paymentPayload = $sumbangan->payment_payload ?? [];
    $rawPaymentMethod = (string) ($sumbangan->kaedah_sumbangan ?? '');
    $normalizedPaymentMethod = strtolower(trim($rawPaymentMethod));
    $isOnlinePayment = in_array($normalizedPaymentMethod, ['simulasi', 'simulasi pembayaran', 'pembayaran atas talian'], true)
        || data_get($paymentPayload, 'method') === 'simulasi';
    $paymentMethod = $isOnlinePayment
        ? 'Pembayaran Atas Talian'
        : ($sumbangan->kaedah_sumbangan ?: 'ToyyibPay');
    $statusLabel = $sumbangan->status === 'selesai' ? 'Selesai' : ucfirst(str_replace('_', ' ', $sumbangan->status));
    $donorSnapshot = $sumbangan->donor_snapshot ?? [];
    $donorName = data_get($donorSnapshot, 'name') ?: (optional($sumbangan->user)->name ?? 'Penderma');
    $donorEmail = data_get($donorSnapshot, 'email') ?: (optional($sumbangan->user)->email ?? '-');
    $donorPhone = data_get($donorSnapshot, 'phone') ?: '-';
    $donorAltPhone = data_get($donorSnapshot, 'alt_phone') ?: null;
    $donorCityLine = trim((string) data_get($donorSnapshot, 'postcode') . ' ' . (string) data_get($donorSnapshot, 'city'));
    $donorAddress = collect([
        data_get($donorSnapshot, 'address'),
        $donorCityLine,
        data_get($donorSnapshot, 'state'),
        data_get($donorSnapshot, 'country'),
    ])
        ->filter(fn ($line) => filled($line))
        ->implode("\n");
    $noSumbangan = $sumbangan->no_sumbangan ?? ('SMB-' . str_pad($sumbangan->id, 6, '0', STR_PAD_LEFT));
    $categoryLabels = collect(\App\Models\Item::DONATION_CATEGORIES)
        ->mapWithKeys(fn ($category, $key) => [$key => $category['title']]);
    $categoryText = $sumbangan->items
        ->pluck('kategori_bantuan')
        ->unique()
        ->map(fn ($category) => $categoryLabels->get($category, $category))
        ->implode(', ');
    $categoryText = $categoryText !== '' ? $categoryText : 'Sumbangan Barang';
@endphp
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Resit Sumbangan {{ $noSumbangan }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 12px; line-height: 1.45; }
        .header { border-bottom: 2px solid #071633; padding-bottom: 16px; margin-bottom: 22px; }
        .brand { font-size: 20px; font-weight: 700; color: #071633; margin: 0; }
        .title { font-size: 16px; font-weight: 700; margin: 4px 0 0; }
        .meta { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .meta td { width: 50%; vertical-align: top; padding: 6px 0; }
        .label { color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: .04em; }
        .value { font-weight: 700; margin-top: 2px; }
        .preline { white-space: pre-line; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.items th { background: #e2e8f0; text-align: left; padding: 9px; font-size: 11px; }
        table.items td { border-bottom: 1px solid #e2e8f0; padding: 9px; }
        .right { text-align: right; }
        .total { margin-top: 18px; text-align: right; font-size: 16px; font-weight: 700; }
        .footer { margin-top: 28px; color: #64748b; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <p class="brand">eBantuanSiswa UKM</p>
        <p class="title">Resit Sumbangan</p>
    </div>

    <table class="meta">
        <tr>
            <td>
                <div class="label">No. Sumbangan</div>
                <div class="value">{{ $noSumbangan }}</div>
            </td>
            <td>
                <div class="label">Tarikh</div>
                <div class="value">{{ optional($receiptDate)->format('d/m/Y h:i A') }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Nama Penderma</div>
                <div class="value">{{ $donorName }}</div>
            </td>
            <td>
                <div class="label">Email</div>
                <div class="value">{{ $donorEmail }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Telefon</div>
                <div class="value">{{ $donorPhone }}</div>
            </td>
            <td>
                <div class="label">Telefon Alternatif</div>
                <div class="value">{{ $donorAltPhone ?: '-' }}</div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div class="label">Alamat Penyumbang</div>
                <div class="value preline">{{ $donorAddress ?: '-' }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Jenis Sumbangan</div>
                <div class="value">{{ $categoryText }}</div>
            </td>
            <td>
                <div class="label">Kaedah Bayaran</div>
                <div class="value">{{ $paymentMethod }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Status</div>
                <div class="value">{{ $statusLabel }}</div>
            </td>
            <td>
                <div class="label">Jumlah Bayaran</div>
                <div class="value">RM{{ number_format((float) $sumbangan->jumlah_keseluruhan, 2) }}</div>
            </td>
        </tr>
    </table>

    <div class="label">Senarai item disumbang</div>
    <table class="items">
        <thead>
            <tr>
                <th>Item</th>
                <th>Kategori</th>
                <th class="right">Kuantiti</th>
                <th class="right">Harga Unit</th>
                <th class="right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sumbangan->items as $item)
                <tr>
                    <td>{{ $item->nama_item }}</td>
                    <td>{{ $categoryLabels->get($item->kategori_bantuan, $item->kategori_bantuan) }}</td>
                    <td class="right">{{ $item->kuantiti }}</td>
                    <td class="right">RM{{ number_format((float) $item->harga_unit, 2) }}</td>
                    <td class="right">RM{{ number_format((float) $item->jumlah, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        Jumlah Bayaran: RM{{ number_format((float) $sumbangan->jumlah_keseluruhan, 2) }}
    </div>

    <div class="footer">
        Resit ini dijana oleh sistem eBantuanSiswa UKM.
    </div>
</body>
</html>
