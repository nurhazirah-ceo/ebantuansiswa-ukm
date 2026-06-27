@extends('layouts.admin')

@section('page-title', 'Semak Permohonan')
@section('page-subtitle', 'Semak perkembangan dan keputusan permohonan bantuan pelajar.')

@section('content')
<div class="min-h-screen bg-[#f8f5f8]">
    <div class="max-w-7xl mx-auto px-6 py-8">
        @php
            $formatPermohonanLabel = fn ($value) => filled($value)
                ? ucwords(str_replace(['_', '-'], ' ', (string) $value))
                : '-';

            $tabConfig = [
                'jumlah' => [
                    'label' => 'Jumlah',
                    'count' => $stats['jumlah'],
                    'button_class' => 'border-slate-200 bg-white text-slate-700',
                    'count_class' => 'bg-slate-100 text-slate-700',
                    'ring_class' => 'ring-slate-300',
                    'items' => $permohonan,
                ],
                'dalam-semak' => [
                    'label' => 'Dalam Semakan',
                    'count' => $stats['dalam_semakan'],
                    'button_class' => 'border-amber-200 bg-amber-50 text-amber-700',
                    'count_class' => 'bg-white text-amber-700',
                    'ring_class' => 'ring-amber-300',
                    'items' => $permohonan
                        ->where('status_key', \App\Models\Permohonan::STATUS_DALAM_SEMAKAN)
                        ->where('lewat_diproses', false),
                ],
                'lulus' => [
                    'label' => 'Diluluskan',
                    'count' => $stats['lulus'],
                    'button_class' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                    'count_class' => 'bg-white text-emerald-700',
                    'ring_class' => 'ring-emerald-300',
                    'items' => $permohonan->where('status_key', \App\Models\Permohonan::STATUS_DILULUSKAN),
                ],
                'gagal' => [
                    'label' => 'Ditolak / Gagal',
                    'count' => $stats['gagal'],
                    'button_class' => 'border-rose-200 bg-rose-50 text-rose-700',
                    'count_class' => 'bg-white text-rose-700',
                    'ring_class' => 'ring-rose-300',
                    'items' => $permohonan->where('status_key', \App\Models\Permohonan::STATUS_DITOLAK_GAGAL),
                ],
                'selesai' => [
                    'label' => 'Selesai',
                    'count' => $stats['selesai'],
                    'button_class' => 'border-sky-200 bg-sky-50 text-sky-700',
                    'count_class' => 'bg-white text-sky-700',
                    'ring_class' => 'ring-sky-300',
                    'items' => $permohonan->whereIn('status_key', [
                        \App\Models\Permohonan::STATUS_DILULUSKAN,
                        \App\Models\Permohonan::STATUS_DITOLAK_GAGAL,
                    ]),
                ],
                'lewat' => [
                    'label' => 'Lewat Diproses',
                    'count' => $stats['lewat_diproses'],
                    'button_class' => 'border-red-700 bg-red-700 text-white',
                    'count_class' => 'bg-white/20 text-white',
                    'ring_class' => 'ring-red-300',
                    'items' => $permohonan->where('lewat_diproses', true),
                ],
            ];
        @endphp

        <x-page-hero
            eyebrow="Permohonan"
            title="Status Permohonan"
            description="Semak perkembangan dan keputusan permohonan bantuan pelajar."
        />

        <div class="mt-8 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-lg">
            <div class="bg-[#071633] px-8 py-5 text-white">
                <div class="flex justify-end">
                

                    <form method="GET" action="{{ route('admin.permohonan.index') }}" class="w-full lg:max-w-md">
                        <label for="search" class="sr-only">Cari permohonan</label>
                        <div class="flex items-center gap-3 rounded-2xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur transition focus-within:border-white/30">
                            <svg class="h-4 w-4 text-slate-200" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 3.473 9.765l3.63 3.631a.75.75 0 1 0 1.06-1.06l-3.63-3.632A5.5 5.5 0 0 0 9 3.5ZM5 9a4 4 0 1 1 8 0a4 4 0 0 1-8 0Z" clip-rule="evenodd" />
                            </svg>
                            <input id="search"
                                   name="search"
                                   type="text"
                                   value="{{ $search }}"
                                   placeholder="Cari pelajar, kelompok atau bantuan"
                                   class="w-full border-0 bg-transparent p-0 text-sm text-white placeholder:text-slate-300 focus:outline-none focus:ring-0">
                            <button type="submit"
                                    class="inline-flex items-center justify-center rounded-xl bg-white px-3.5 py-2 text-sm font-semibold text-[#13284c] transition hover:bg-slate-100">
                                Cari
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="border-b border-slate-200 bg-white px-6 py-4">
                <div class="flex flex-wrap gap-3" id="permohonan-tabs">
                    @foreach($tabConfig as $tabKey => $tab)
                        @php $isUrgent = $tabKey === 'lewat'; @endphp
                        <button type="button"
                                class="permohonan-tab inline-flex items-center gap-2 rounded-full border px-4 py-2 text-xs font-semibold shadow-sm transition hover:-translate-y-0.5 {{ $tab['button_class'] }} {{ $isUrgent ? 'animate-pulse shadow-red-200' : '' }}"
                                data-tab="{{ $tabKey }}"
                                data-ring="{{ $tab['ring_class'] }}"
                                aria-pressed="{{ $loop->first ? 'true' : 'false' }}">
                            <span class="permohonan-tab-dot hidden h-2.5 w-2.5 rounded-full {{ str_replace('ring-', 'bg-', $tab['ring_class']) }}"></span>
                            @if($isUrgent)
                                <span aria-hidden="true">⚠️</span>
                            @endif
                            {{ $tab['label'] }}
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ $tab['count_class'] }}">{{ $tab['count'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            @foreach($tabConfig as $tabKey => $tab)
                <section class="permohonan-panel {{ $loop->first ? '' : 'hidden' }}" data-panel="{{ $tabKey }}">
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-[#edf2f8]">
                                <tr>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700">No Kelompok</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700">No Matrik</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700">Jenis Bantuan</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700">Status Permohonan</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700">Tempoh</th>
                                    <th class="px-6 py-4 text-center text-sm font-semibold text-slate-700">Semak Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                @forelse($tab['items'] as $item)
                                    @php
                                        $hariPermohonan = $item->tarikh_mohon
                                            ? \Carbon\Carbon::parse($item->tarikh_mohon)->startOfDay()->diffInDays(now()->startOfDay())
                                            : null;

                                        $noMatrik = $item->pelajar?->no_matrik ?? $item->user?->matrik ?? '-';
                                        $jenisBantuan = $item->bantuan?->jenis_bantuan ?? $item->jenis_bantuan;
                                        $kategoriBantuan = $item->bantuan?->kategori_bantuan ?? $item->kategori ?? $item->pakej;
                                        $statusLabel = $item->lewat_diproses ? 'Lewat Diproses' : $item->status_label;
                                        $statusClass = $item->lewat_diproses
                                            ? 'bg-indigo-100 text-indigo-700'
                                            : $item->status_badge_class;
                                    @endphp
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="px-6 py-5 text-sm font-semibold text-blue-700">
                                            {{ $item->no_kelompok ?? '-' }}
                                        </td>
                                        <td class="px-6 py-5 text-sm text-slate-700">
                                            <div>{{ $noMatrik }}</div>
                                        </td>
                                        <td class="px-6 py-5 text-sm text-slate-800">
                                            <div>{{ $formatPermohonanLabel($jenisBantuan) }}</div>
                                            @if(filled($kategoriBantuan))
                                                <div class="mt-1 text-xs text-slate-400">
                                                    {{ $formatPermohonanLabel($kategoriBantuan) }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-5">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                                                {{ $statusLabel }}
                                            </span>
                                            <div class="mt-2 text-xs text-slate-500">
                                                {{ $item->catatan ?: 'Tiada catatan tambahan untuk permohonan ini.' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-5 text-sm text-slate-700">
                                            <div>{{ $hariPermohonan !== null ? $hariPermohonan . ' hari' : '-' }}</div>
                                        </td>
                                        <td class="px-6 py-5 text-center">
                                            <a href="{{ route('admin.permohonan.show', $item) }}"
                                               class="inline-flex items-center justify-center bg-[#071633] hover:bg-[#102544] text-white text-sm font-medium px-4 py-2 rounded-xl transition shadow-sm">
                                                Lihat
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                                            <div class="mx-auto max-w-md rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-8">
                                                <h3 class="text-lg font-semibold text-slate-900">Tiada rekod untuk kategori ini</h3>
                                                <p class="mt-2 text-sm leading-6 text-slate-500">
                                                    Belum ada permohonan yang sepadan dengan pilihan {{ $tab['label'] }}.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            @endforeach
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('.permohonan-tab');
    const panels = document.querySelectorAll('.permohonan-panel');

    const setActiveTab = (tabKey) => {
        tabs.forEach((tab) => {
            const isActive = tab.dataset.tab === tabKey;
            const dot = tab.querySelector('.permohonan-tab-dot');
            const ringClass = tab.dataset.ring;

            tab.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            tab.classList.toggle('ring-2', isActive);
            tab.classList.toggle('ring-offset-2', isActive);

            ['ring-slate-300', 'ring-amber-300', 'ring-emerald-300', 'ring-rose-300', 'ring-sky-300', 'ring-red-300'].forEach((cls) => {
                tab.classList.remove(cls);
            });

            if (isActive && ringClass) {
                tab.classList.add(ringClass);
            }

            if (dot) {
                dot.classList.toggle('hidden', !isActive);
            }
        });

        panels.forEach((panel) => {
            panel.classList.toggle('hidden', panel.dataset.panel !== tabKey);
        });
    };

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => setActiveTab(tab.dataset.tab));
    });

    if (tabs.length > 0) {
        setActiveTab(tabs[0].dataset.tab);
    }
});
</script>
@endsection
