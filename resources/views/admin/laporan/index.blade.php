@extends('layouts.admin')

@section('page-title', 'Laporan')

@section('content')
<div class="min-h-screen bg-slate-50 py-8">
    <div class="mx-auto max-w-7xl space-y-7 px-6">
       <x-page-hero
    eyebrow="ANALISIS"
    title="Laporan Pentadbiran"
    description="Pusat laporan permohonan, agihan, sumbangan dan inventori sistem."
/>

        <section class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
            @foreach($reports as $report)
                <a href="{{ $report['href'] }}"
                   class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-sky-200 hover:shadow-xl">
                    <div class="flex items-start justify-between gap-4">
                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                            {{ $report['status'] }}
                        </span>
                        <span class="text-sm font-semibold text-sky-600 transition group-hover:translate-x-1">
                            Lihat Laporan →
                        </span>
                    </div>

                    <h2 class="mt-6 text-lg font-bold text-slate-950">{{ $report['title'] }}</h2>
                    <p class="mt-2 min-h-16 text-sm leading-6 text-slate-500">{{ $report['desc'] }}</p>

                    <div class="mt-6 rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">{{ $report['meta'] }}</p>
                        <p class="mt-2 text-3xl font-extrabold text-slate-950">{{ $report['total_label'] }}</p>
                    </div>
                </a>
            @endforeach
        </section>
    </div>
</div>
@endsection
