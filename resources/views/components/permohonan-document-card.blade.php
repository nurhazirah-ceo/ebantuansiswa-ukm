@props(['dokumen'])

@php
    $documentExists = $dokumen->exists_on_storage;
    $documentUrl = $documentExists ? route('permohonan.dokumen.show', $dokumen) : null;
    $downloadUrl = $documentExists ? route('permohonan.dokumen.show', ['dokumen' => $dokumen, 'download' => 1]) : null;
    $extension = strtoupper($dokumen->extension ?: 'FILE');
    $extension = $extension === 'JPEG' ? 'JPG' : $extension;
    $rawType = strtolower(trim((string) $dokumen->jenis_dokumen));

    $documentLabel = match ($rawType) {
        'dokumen 1', 'document 1', 'dokumen_1', 'document_1' => 'Dokumen 1',
        'dokumen 2', 'document 2', 'dokumen_2', 'document_2' => 'Dokumen 2',
        default => $dokumen->jenis_dokumen ?: 'Dokumen Sokongan',
    };
@endphp

<div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
    <div class="flex min-w-0 items-start justify-between gap-4">
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <p class="text-sm font-semibold text-slate-900">
                    {{ $documentLabel }}
                </p>

                @if($documentExists)
                    <span class="inline-flex items-center rounded-md border border-slate-200 bg-white px-2 py-0.5 text-[11px] font-semibold uppercase text-slate-600">
                        {{ $extension }}
                    </span>
                @endif
            </div>

            @if(! $documentExists)
                <p class="mt-1 text-xs font-semibold text-rose-600">
                    Dokumen tidak dijumpai
                </p>
            @endif
        </div>
    </div>

    @if($documentExists)
        <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
            <a href="{{ $documentUrl }}"
               target="_blank"
               rel="noopener"
               class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#071633] px-3 py-2 text-xs font-semibold text-white transition hover:bg-[#102544]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
                <span>Lihat Dokumen</span>
            </a>

            <a href="{{ $downloadUrl }}"
               download
               class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                    <path d="M7 10l5 5 5-5" />
                    <path d="M12 15V3" />
                </svg>
                <span>Muat Turun</span>
            </a>
        </div>
    @endif
</div>
