@php
    $receiptDate = $cashDonation->paid_at ?? $cashDonation->updated_at ?? $cashDonation->created_at;
    $statusLabel = $cashDonation->payment_status === \App\Models\CashDonation::STATUS_SUCCESS
        ? 'Selesai'
        : ucfirst(str_replace('_', ' ', (string) $cashDonation->payment_status));
    $donorName = optional($cashDonation->user)->name ?? 'Penderma';
    $donorEmail = optional($cashDonation->user)->email ?? '-';
    $note = $cashDonation->message ?: '-';
    $isLegacyDemoRecord = (bool) data_get($cashDonation->raw_response, 'demo_mode', false);
    $paymentMethod = $cashDonation->bill_code
        ? 'ToyyibPay'
        : ($isLegacyDemoRecord ? 'Simulasi Pembayaran' : 'Pembayaran Atas Talian');
@endphp
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Resit Sumbangan {{ $reference }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 12px; line-height: 1.45; }
        .header { border-bottom: 2px solid #071633; padding-bottom: 16px; margin-bottom: 22px; }
        .brand { font-size: 20px; font-weight: 700; color: #071633; margin: 0; }
        .title { font-size: 16px; font-weight: 700; margin: 4px 0 0; }
        .meta { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .meta td { width: 50%; vertical-align: top; padding: 6px 0; }
        .label { color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: .04em; }
        .value { font-weight: 700; margin-top: 2px; }
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
                <div class="value">{{ $reference }}</div>
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
                <div class="label">Jenis Sumbangan</div>
                <div class="value">Tabung Bantuan</div>
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
                <div class="value">RM{{ number_format((float) $cashDonation->amount, 2) }}</div>
            </td>
        </tr>
    </table>

    <div class="label">Butiran sumbangan</div>
    <table class="items">
        <thead>
            <tr>
                <th>Butiran</th>
                <th>Catatan</th>
                <th class="right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Sumbangan Tabung Bantuan Pelajar</td>
                <td>{{ $note }}</td>
                <td class="right">RM{{ number_format((float) $cashDonation->amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="total">
        Jumlah Bayaran: RM{{ number_format((float) $cashDonation->amount, 2) }}
    </div>

    @if($isLegacyDemoRecord)
        <div class="footer">
            Resit ini ialah rekod simulasi lama dan bukan bukti bayaran gateway sebenar.
        </div>
    @endif
</body>
</html>
