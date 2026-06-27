@extends('layouts.app')

@section('content')

@php
    $showDeliveryForm = $physicalDonation->canUpdateDelivery();
    $referenceNumber = 'BRS-'
        . (optional($physicalDonation->created_at)->format('Ymd') ?: now()->format('Ymd'))
        . '-'
        . str_pad((string) $physicalDonation->id, 3, '0', STR_PAD_LEFT);
@endphp

<div class="min-h-screen bg-[linear-gradient(180deg,#f7fbff_0%,#eef4fb_48%,#f8fbff_100%)] pt-6 pb-10">
    <div class="mx-auto max-w-5xl px-6">
        @if($errors->any())
            <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-[0_18px_45px_rgba(15,23,42,0.08)]">
            <div class="bg-[#071633] px-7 py-7 text-white sm:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.25em] text-cyan-200">Serahan Barang</p>
                        <h1 class="mt-4 text-3xl font-extrabold tracking-tight text-white">{{ $physicalDonation->item_name }}</h1>
                        <p class="mt-3 text-sm leading-6 text-slate-200">{{ $physicalDonation->category_label }} &middot; {{ $physicalDonation->quantity }} unit</p>
                    </div>
                    <span class="inline-flex w-fit rounded-full px-4 py-2 text-sm font-semibold {{ $physicalDonation->status_badge_class }}">
                        {{ $physicalDonation->status_label }}
                    </span>
                </div>
            </div>

            <div class="grid gap-0 lg:grid-cols-[0.9fr_1.1fr]">
                <aside class="border-b border-slate-200 bg-slate-50/80 p-7 lg:border-b-0 lg:border-r">
                    <h2 class="text-xl font-bold text-slate-900">Maklumat Barang</h2>

                    <div class="mt-5 space-y-4 text-sm">
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <p class="text-slate-500">Keadaan Barang</p>
                            <p class="mt-1 font-semibold text-slate-900">{{ $physicalDonation->item_condition }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <p class="text-slate-500">Tarikh Submit</p>
                            <p class="mt-1 font-semibold text-slate-900">{{ optional($physicalDonation->created_at)->format('d/m/Y h:i A') }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <p class="text-slate-500">No. Rujukan</p>
                            <p class="mt-1 font-semibold text-slate-900">{{ $referenceNumber }}</p>
                        </div>
                    </div>

                    @if($physicalDonation->image_path)
                        <img src="{{ asset('storage/' . $physicalDonation->image_path) }}"
                             alt="{{ $physicalDonation->item_name }}"
                             class="mt-5 max-h-80 w-full rounded-2xl border border-slate-200 object-cover">
                    @endif
                </aside>

                <div class="p-7">
                    @if($physicalDonation->status === \App\Models\PhysicalDonation::STATUS_PENDING_REVIEW)
                        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm leading-6 text-amber-800">
                            Sumbangan barang anda sedang menunggu semakan admin.
                        </div>
                    @endif

                    @if($physicalDonation->status === \App\Models\PhysicalDonation::STATUS_REJECTED)
                        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm leading-6 text-rose-800">
                            <p class="font-semibold">Sebab penolakan</p>
                            <p class="mt-2">{{ $physicalDonation->rejection_reason ?: 'Tidak dinyatakan.' }}</p>
                        </div>
                    @endif

                    @if(in_array($physicalDonation->status, [
                        \App\Models\PhysicalDonation::STATUS_APPROVED,
                        \App\Models\PhysicalDonation::STATUS_AWAITING_DELIVERY,
                        \App\Models\PhysicalDonation::STATUS_RECEIVED,
                    ], true))
                        <div class="rounded-2xl border border-blue-200 bg-blue-50 px-5 py-4 text-sm leading-6 text-blue-900">
                            <p class="font-semibold">Alamat serahan UKM</p>
                            <p class="mt-2">
                                Pejabat Hal Ehwal Pelajar<br>
                                Universiti Kebangsaan Malaysia<br>
                                43600 UKM Bangi<br>
                                Selangor
                            </p>
                            <p class="mt-4 font-semibold">Waktu serahan</p>
                            <p class="mt-2">
                                Isnin - Jumaat<br>
                                9.00 pagi - 4.00 petang
                            </p>
                            <p class="mt-4 font-semibold">Arahan serahan barang</p>
                            <p class="mt-2">
                                Sila bawa barang dalam keadaan baik dan tunjukkan rekod serahan ini kepada petugas pentadbiran semasa penghantaran.
                            </p>
                        </div>
                    @endif

                    @if($physicalDonation->status === \App\Models\PhysicalDonation::STATUS_RECEIVED)
                        <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">
                            Barang telah diterima oleh pihak admin.
                        </div>
                    @endif

                    @if($showDeliveryForm)
                        <form method="POST"
                              action="{{ route('penderma.serahan-barang.delivery', $physicalDonation) }}"
                              id="deliveryUpdateForm"
                              class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            @csrf
                            @method('PUT')

                            <h2 class="text-lg font-bold text-slate-900">Kemaskini Maklumat Serahan</h2>

                            <div class="mt-5 grid gap-4 md:grid-cols-2">
                                <div class="md:col-span-2">
                                    <label for="delivery_method" class="block text-sm font-semibold text-slate-900">Kaedah Serahan</label>
                                    <select id="delivery_method"
                                            name="delivery_method"
                                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-600 focus:ring-2 focus:ring-blue-100"
                                            required>
                                        <option value="">Pilih kaedah</option>
                                        @foreach($deliveryMethods as $value => $label)
                                            <option value="{{ $value }}" @selected(old('delivery_method', $physicalDonation->delivery_method) === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div data-courier-field>
                                    <label for="courier_name" class="block text-sm font-semibold text-slate-900">Nama Kurier</label>
                                    <input id="courier_name"
                                           name="courier_name"
                                           type="text"
                                           value="{{ old('courier_name', $physicalDonation->courier_name) }}"
                                           class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-600 focus:ring-2 focus:ring-blue-100">
                                </div>

                                <div data-courier-field>
                                    <label for="tracking_number" class="block text-sm font-semibold text-slate-900">Nombor Tracking</label>
                                    <input id="tracking_number"
                                           name="tracking_number"
                                           type="text"
                                           value="{{ old('tracking_number', $physicalDonation->tracking_number) }}"
                                           class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-600 focus:ring-2 focus:ring-blue-100">
                                </div>

                                <div data-courier-field class="md:col-span-2">
                                    <label for="expected_delivery_date" class="block text-sm font-semibold text-slate-900">Tarikh Jangkaan Sampai</label>
                                    <input id="expected_delivery_date"
                                           name="expected_delivery_date"
                                           type="date"
                                           value="{{ old('expected_delivery_date', optional($physicalDonation->expected_delivery_date)->format('Y-m-d')) }}"
                                           class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-600 focus:ring-2 focus:ring-blue-100">
                                </div>
                            </div>

                            <button type="button"
                                    id="deliveryUpdateButton"
                                    class="mt-5 inline-flex items-center justify-center rounded-2xl bg-[#1D4ED8] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1E40AF]">
                                Kemaskini Maklumat Serahan
                            </button>
                        </form>
                    @elseif($physicalDonation->status === \App\Models\PhysicalDonation::STATUS_AWAITING_DELIVERY)
                        <div class="mt-5 rounded-2xl border border-cyan-200 bg-cyan-50 px-5 py-4 text-sm leading-6 text-cyan-800">
                            Maklumat serahan telah dikemaskini dan sedang menunggu pengesahan admin.
                        </div>
                    @endif

                    @if($physicalDonation->description)
                        <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm leading-6 text-slate-700">
                            <p class="font-semibold text-slate-900">Catatan Barang</p>
                            <p class="mt-2">{{ $physicalDonation->description }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const methodSelect = document.getElementById('delivery_method');
    const courierFields = document.querySelectorAll('[data-courier-field]');
    const deliveryUpdateForm = document.getElementById('deliveryUpdateForm');
    const deliveryUpdateButton = document.getElementById('deliveryUpdateButton');
    let confirmedDeliveryUpdate = false;

    function updateCourierFields() {
        const isCourier = methodSelect?.value === 'pos_kurier';
        courierFields.forEach(field => field.classList.toggle('hidden', !isCourier));
    }

    methodSelect?.addEventListener('change', updateCourierFields);
    updateCourierFields();

    deliveryUpdateButton?.addEventListener('click', function () {
        deliveryUpdateForm?.requestSubmit();
    });

    deliveryUpdateForm?.addEventListener('submit', function (event) {
        if (confirmedDeliveryUpdate) {
            confirmedDeliveryUpdate = false;
            return;
        }

        event.preventDefault();

        Swal.fire({
            icon: 'question',
            title: 'Kemaskini maklumat serahan?',
            text: 'Pastikan maklumat serahan barang adalah betul.',
            showCancelButton: true,
            confirmButtonText: 'Ya, kemaskini',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#1D4ED8',
            cancelButtonColor: '#64748b'
        }).then(function (result) {
            if (result.isConfirmed) {
                confirmedDeliveryUpdate = true;
                deliveryUpdateForm.requestSubmit();
            }
        });
    });
});
</script>

@endsection
