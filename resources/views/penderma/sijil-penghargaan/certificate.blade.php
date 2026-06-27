<!doctype html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <title>Sijil Penghargaan eBantuanSiswa UKM</title>

    <style>
        @page {
            margin: 0;
            size: A4 landscape;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            color: #0f172a;
        }

        .page {
            position: relative;
            width: 297mm;
            height: 210mm;
            overflow: hidden;
            page-break-inside: avoid;
        }

        .template {
            position: absolute;
            inset: 0;
            width: 297mm;
            height: 210mm;
            object-fit: cover;
            z-index: 1;
        }

        .donor-name {
            position: absolute;
            top: 93mm;
            left: 50%;
            width: 190mm;
            transform: translateX(-50%);
            z-index: 2;
            text-align: center;
            font-family: DejaVu Serif, Georgia, serif;
            font-size: 28pt;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #0b1f44;
            line-height: 1.15;
        }

        .tier-cover {
            position: absolute;
            top: 156mm;
            left: 50%;
            width: 226mm;
            height: 22mm;
            transform: translateX(-50%);
            z-index: 2;
            background: #fffdf0;
        }

        .recognition-tier {
            position: absolute;
            top: 160mm;
            left: 50%;
            width: 220mm;
            transform: translateX(-50%);
            z-index: 3;
            text-align: center;
            font-family: DejaVu Serif, Georgia, serif;
            font-size: 29pt;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #0b1f44;
            line-height: 1.1;
        }

        .certificate-no {
            position: absolute;
            right: 18mm;
            bottom: 11mm;
            z-index: 2;
            font-size: 8pt;
            font-weight: 600;
            color: #334155;
            opacity: 0.75;
        }

        .generated-date {
            position: absolute;
            left: 18mm;
            bottom: 11mm;
            z-index: 2;
            font-size: 8pt;
            font-weight: 600;
            color: #334155;
            opacity: 0.75;
        }
    </style>
</head>

<body>
@php
    $templateFile = $certificateTemplate ?? 'sijil-prihatin.png';
    $recognitionTier = $recognitionTier ?? 'Penyumbang Prihatin';
    $certificateNo = $certificateNo ?? ('CERT-' . now()->format('Ymd'));
@endphp

<div class="page">
    <img
        class="template"
        src="{{ public_path('image/ui/' . $templateFile) }}"
        alt="Sijil Penghargaan"
    >

    <div class="donor-name">
        {{ $donorName }}
    </div>

    <div class="tier-cover"></div>

    <div class="recognition-tier">
        {{ $recognitionTier }}
    </div>

    <div class="generated-date">
        Tarikh: {{ $generatedDate }}
    </div>

    <div class="certificate-no">
        No. Sijil: {{ $certificateNo }}
    </div>
</div>
</body>
</html>
