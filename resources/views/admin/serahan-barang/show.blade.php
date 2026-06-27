@extends('layouts.admin')

@section('page-title', 'Detail Serahan Barang')

@section('content')

<div class="min-h-screen bg-slate-50 py-8">
    <div class="mx-auto max-w-6xl space-y-6 px-6">
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.serahan-barang.index') }}"
               class="inline-flex rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                Kembali
            </a>
        </div>

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

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="bg-[#071633] px-10 py-12 text-white">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm font-bold uppercase tracking-[0.25em] text-cyan-200">Serahan Barang</p>
                        <h1 class="mt-5 text-4xl font-extrabold tracking-tight text-white">{{ $physicalDonation->item_name }}</h1>
                        <p class="mt-4 text-sm leading-6 text-slate-200">{{ $physicalDonation->donor?->name ?? 'Penderma' }} · {{ $physicalDonation->category_label }}</p>
                    </div>
                    <span class="inline-flex w-fit rounded-full px-4 py-2 text-sm font-semibold {{ $physicalDonation->status_badge_class }}">
                        {{ $physicalDonation->status_label }}
                    </span>
                </div>
            </div>

            <div class="grid gap-0 lg:grid-cols-[0.9fr_1.1fr]">
                <aside class="border-b border-slate-200 bg-slate-50 p-7 lg:border-b-0 lg:border-r">
                    <h2 class="text-lg font-bold text-slate-950">Maklumat Penderma</h2>
                    <div class="mt-4 space-y-3 text-sm">
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <p class="text-slate-500">Nama</p>
                            <p class="mt-1 font-semibold text-slate-900">{{ $physicalDonation->donor?->name ?? '-' }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <p class="text-slate-500">Email</p>
                            <p class="mt-1 font-semibold text-slate-900">{{ $physicalDonation->donor?->email ?? '-' }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <p class="text-slate-500">Telefon</p>
                            <p class="mt-1 font-semibold text-slate-900">{{ $physicalDonation->donor_phone ?: '-' }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <p class="text-slate-500">Alamat</p>
                            <p class="mt-1 whitespace-pre-line font-semibold text-slate-900">{{ $physicalDonation->donor_address ?: '-' }}</p>
                        </div>
                    </div>
                </aside>

                <div class="p-7">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm text-slate-500">Kuantiti</p>
                            <p class="mt-1 text-lg font-semibold text-slate-900">{{ number_format($physicalDonation->quantity) }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm text-slate-500">Keadaan Barang</p>
                            <p class="mt-1 text-lg font-semibold text-slate-900">{{ $physicalDonation->item_condition }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm text-slate-500">Kaedah Serahan</p>
                            <p class="mt-1 text-lg font-semibold text-slate-900">{{ $physicalDonation->delivery_method_label }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm text-slate-500">Tarikh Submit</p>
                            <p class="mt-1 text-lg font-semibold text-slate-900">{{ optional($physicalDonation->created_at)->format('d/m/Y h:i A') }}</p>
                        </div>
                    </div>

                    @if($physicalDonation->delivery_method === \App\Models\PhysicalDonation::DELIVERY_COURIER)
                        <div class="mt-5 rounded-2xl border border-cyan-200 bg-cyan-50 p-5 text-sm leading-6 text-cyan-900">
                            <p><span class="font-semibold">Kurier:</span> {{ $physicalDonation->courier_name ?: '-' }}</p>
                            <p><span class="font-semibold">Tracking:</span> {{ $physicalDonation->tracking_number ?: '-' }}</p>
                            <p><span class="font-semibold">Jangkaan sampai:</span> {{ optional($physicalDonation->expected_delivery_date)->format('d/m/Y') ?: '-' }}</p>
                        </div>
                    @endif

                    @if($physicalDonation->description)
                        <div class="mt-5 rounded-2xl border border-slate-200 bg-white p-5 text-sm leading-6 text-slate-700">
                            <p class="font-semibold text-slate-900">Catatan Barang</p>
                            <p class="mt-2">{{ $physicalDonation->description }}</p>
                        </div>
                    @endif

                    @if($physicalDonation->image_path)
                        <img src="{{ asset('storage/' . $physicalDonation->image_path) }}"
                             alt="{{ $physicalDonation->item_name }}"
                             class="mt-5 max-h-96 w-full rounded-2xl border border-slate-200 object-cover">
                    @endif

                    <div class="mt-6 grid gap-4 lg:grid-cols-2">
                        @if($physicalDonation->canReview())
                            <form method="POST"
                                  action="{{ route('admin.serahan-barang.approve', $physicalDonation) }}"
                                  class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5"
                                  data-confirm
                                  data-confirm-title="Luluskan serahan barang?"
                                  data-confirm-text="Status serahan barang akan ditukar kepada diluluskan."
                                  data-confirm-button="Ya, luluskan"
                                  data-confirm-color="#059669">
                                @csrf
                                @method('PUT')
                                <label class="block text-sm font-semibold text-emerald-900">Nota Admin</label>
                                <textarea name="admin_note" rows="3" class="mt-2 w-full rounded-2xl border border-emerald-200 px-4 py-3 text-sm"></textarea>
                                <button type="submit" class="mt-3 w-full rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-700">
                                    Approve
                                </button>
                            </form>

                            <form method="POST"
                                  action="{{ route('admin.serahan-barang.reject', $physicalDonation) }}"
                                  class="rounded-2xl border border-rose-200 bg-rose-50 p-5"
                                  data-confirm
                                  data-confirm-title="Tolak serahan barang?"
                                  data-confirm-text="Sebab penolakan akan direkodkan untuk penderma."
                                  data-confirm-button="Ya, tolak"
                                  data-confirm-color="#dc2626">
                                @csrf
                                @method('PUT')
                                <label class="block text-sm font-semibold text-rose-900">Sebab Penolakan</label>
                                <textarea name="rejection_reason" rows="3" class="mt-2 w-full rounded-2xl border border-rose-200 px-4 py-3 text-sm" required></textarea>
                                <button type="submit" class="mt-3 w-full rounded-2xl bg-rose-600 px-5 py-3 text-sm font-semibold text-white hover:bg-rose-700">
                                    Reject
                                </button>
                            </form>
                        @endif

                        @if($physicalDonation->canMarkReceived())
                            <form method="POST"
                                  action="{{ route('admin.serahan-barang.received', $physicalDonation) }}"
                                  class="rounded-2xl border border-cyan-200 bg-cyan-50 p-5 lg:col-span-2"
                                  data-confirm
                                  data-confirm-title="Sahkan barang diterima?"
                                  data-confirm-text="Rekod serahan akan ditandakan sebagai diterima."
                                  data-confirm-button="Ya, sahkan"
                                  data-confirm-color="#0891b2">
                                @csrf
                                @method('PUT')
                                <label class="block text-sm font-semibold text-cyan-900">Nota Penerimaan</label>
                                <textarea name="admin_note" rows="3" class="mt-2 w-full rounded-2xl border border-cyan-200 px-4 py-3 text-sm">{{ $physicalDonation->admin_note }}</textarea>
                                <button type="submit" class="mt-3 rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white hover:bg-cyan-700">
                                    Sahkan Barang Diterima
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

@endsection
