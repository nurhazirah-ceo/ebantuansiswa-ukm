@extends('layouts.admin')

@section('page-title', 'Serahan Barang')

@section('content')

@php
    $filterLabels = [
        '' => 'Semua',
        'pending_review' => 'Menunggu Semakan',
        'approved' => 'Diluluskan',
        'awaiting_delivery' => 'Menunggu Serahan',
        'received' => 'Diterima',
        'rejected' => 'Ditolak',
    ];
@endphp

<div class="min-h-screen bg-slate-50 py-8">
    <div class="mx-auto max-w-7xl space-y-7 px-6">
<x-page-hero
    eyebrow="PENDERMA"
    title="Serahan Barang Penderma"
    description="Semak, luluskan, tolak dan sahkan penerimaan barang fizikal daripada penderma."
/>

        @if(session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-amber-700">Menunggu Semakan</p>
                <h2 class="mt-3 text-3xl font-bold text-amber-700">{{ number_format($summary['pending_review']) }}</h2>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-blue-700">Diluluskan</p>
                <h2 class="mt-3 text-3xl font-bold text-blue-700">{{ number_format($summary['approved']) }}</h2>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-cyan-700">Menunggu Serahan</p>
                <h2 class="mt-3 text-3xl font-bold text-cyan-700">{{ number_format($summary['awaiting_delivery']) }}</h2>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-emerald-700">Barang Diterima</p>
                <h2 class="mt-3 text-3xl font-bold text-emerald-700">{{ number_format($summary['received']) }}</h2>
            </div>
        </section>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-slate-200 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-950">Senarai Serahan Barang</h2>
                    
                </div>

                <div class="flex flex-wrap gap-2">
                    @foreach($filterLabels as $key => $label)
                        <a href="{{ $key === '' ? route('admin.serahan-barang.index') : route('admin.serahan-barang.index', ['status' => $key]) }}"
                           class="rounded-full px-4 py-2 text-sm font-semibold transition {{ (string) $status === (string) $key || ($key === '' && blank($status)) ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-100 text-left text-slate-700">
                        <tr>
                            <th class="px-5 py-4 font-semibold">Nama Penderma</th>
                            <th class="px-5 py-4 font-semibold">Nama Barang</th>
                            <th class="px-5 py-4 font-semibold">Kategori</th>
                            <th class="px-5 py-4 font-semibold">Kuantiti</th>
                            <th class="px-5 py-4 font-semibold">Keadaan</th>
                            <th class="px-5 py-4 font-semibold">Status</th>
                            <th class="px-5 py-4 font-semibold">Tarikh</th>
                            <th class="px-5 py-4 font-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($physicalDonations as $donation)
                            <tr class="align-top hover:bg-slate-50">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-900">{{ $donation->donor?->name ?? 'Penderma' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $donation->donor?->email ?? '-' }}</p>
                                </td>
                                <td class="px-5 py-4 font-semibold text-slate-900">{{ $donation->item_name }}</td>
                                <td class="px-5 py-4 text-slate-700">{{ $donation->category_label }}</td>
                                <td class="px-5 py-4 text-slate-700">{{ number_format($donation->quantity) }}</td>
                                <td class="px-5 py-4 text-slate-700">{{ $donation->item_condition }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $donation->status_badge_class }}">
                                        {{ $donation->status_label }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-slate-700">{{ optional($donation->created_at)->format('d/m/Y') }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex min-w-[220px] flex-col gap-2">
                                        <a href="{{ route('admin.serahan-barang.show', $donation) }}"
                                           class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                            View
                                        </a>

                                        @if($donation->canReview())
                                            <form method="POST"
                                                  action="{{ route('admin.serahan-barang.approve', $donation) }}"
                                                  data-confirm
                                                  data-confirm-title="Luluskan serahan barang?"
                                                  data-confirm-text="Status serahan barang akan ditukar kepada diluluskan."
                                                  data-confirm-button="Ya, luluskan"
                                                  data-confirm-color="#059669">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit"
                                                        class="w-full rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                                                    Approve
                                                </button>
                                            </form>

                                            <form method="POST"
                                                  action="{{ route('admin.serahan-barang.reject', $donation) }}"
                                                  class="space-y-2"
                                                  data-confirm
                                                  data-confirm-title="Tolak serahan barang?"
                                                  data-confirm-text="Sebab penolakan akan dihantar kepada rekod penderma."
                                                  data-confirm-button="Ya, tolak"
                                                  data-confirm-color="#dc2626">
                                                @csrf
                                                @method('PUT')
                                                <input name="rejection_reason"
                                                       type="text"
                                                       placeholder="Sebab penolakan"
                                                       class="w-full rounded-xl border border-rose-200 px-3 py-2 text-xs focus:border-rose-500 focus:ring-rose-100"
                                                       required>
                                                <button type="submit"
                                                        class="w-full rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700">
                                                    Reject
                                                </button>
                                            </form>
                                        @elseif($donation->canMarkReceived())
                                            <form method="POST"
                                                  action="{{ route('admin.serahan-barang.received', $donation) }}"
                                                  data-confirm
                                                  data-confirm-title="Sahkan barang diterima?"
                                                  data-confirm-text="Rekod serahan akan ditandakan sebagai diterima."
                                                  data-confirm-button="Ya, sahkan"
                                                  data-confirm-color="#0891b2">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit"
                                                        class="w-full rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-cyan-700">
                                                    Sahkan Barang Diterima
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-12 text-center">
                                    <div class="mx-auto max-w-md rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-8">
                                        <p class="text-sm font-semibold text-slate-700">Tiada rekod serahan barang ditemui.</p>
                                        <p class="mt-2 text-sm text-slate-500">Serahan barang penderma akan dipaparkan selepas dihantar untuk semakan.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 px-6 py-5">
                {{ $physicalDonations->links() }}
            </div>
        </section>
    </div>
</div>

@endsection
