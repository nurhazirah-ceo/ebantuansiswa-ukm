@extends('layouts.app')

@section('content')

<div class="min-h-screen bg-[linear-gradient(180deg,#f7fbff_0%,#eef4fb_48%,#f8fbff_100%)] py-10">
    <div class="mx-auto max-w-6xl px-6">
        <div class="mb-6">
            <a href="{{ route('penderma.checkout-sumbangan') }}"
               class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                Kembali
            </a>
        </div>

        <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-[0_18px_45px_rgba(15,23,42,0.08)]">
            <div class="bg-[#071633] px-10 py-12 text-white">
                <p class="text-sm font-bold uppercase tracking-[0.25em] text-cyan-200">Serahan Barang Penderma</p>
                <h1 class="mt-5 text-4xl font-extrabold tracking-tight text-white">Hantar Barang Fizikal</h1>
                <p class="mt-4 max-w-4xl text-sm leading-6 text-slate-200">
                    Maklumat barang akan disemak oleh admin sebelum arahan serahan UKM dipaparkan kepada anda.
                </p>
            </div>

            <div class="grid gap-0 lg:grid-cols-[0.9fr_1.1fr]">
                <aside class="border-b border-slate-200 bg-slate-50/80 p-7 lg:border-b-0 lg:border-r">
                    <h2 class="text-xl font-bold text-slate-900">Ringkasan Pilihan</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500">
                        Jika anda datang dari checkout, maklumat item pertama akan dipraisi secara automatik dan masih boleh dikemaskini.
                    </p>

                    <div id="checkoutPhysicalSummary" class="mt-5 rounded-2xl border border-dashed border-slate-300 bg-white px-5 py-5 text-sm text-slate-500">
                        Tiada ringkasan checkout ditemui.
                    </div>

                    <div class="mt-5 space-y-3 text-sm leading-6 text-slate-700">
                        <p class="rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3">
                            Status awal serahan ialah Menunggu Semakan.
                        </p>
                        <p class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3">
                            Alamat UKM hanya dipaparkan selepas admin meluluskan serahan.
                        </p>
                    </div>
                </aside>

                <form method="POST"
                      action="{{ route('penderma.serahan-barang.store') }}"
                      enctype="multipart/form-data"
                      id="physicalDonationForm"
                      class="p-7"
                      data-physical-donation-form
                      data-confirm
                      data-confirm-title="Hantar serahan barang?"
                      data-confirm-text="Maklumat barang akan dihantar untuk semakan admin."
                      data-confirm-button="Ya, hantar"
                      data-confirm-color="#1D4ED8">
                    @csrf

                    @if($errors->any())
                        <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700">
                            <p class="font-semibold">Sila semak semula maklumat serahan.</p>
                            <ul class="mt-2 list-disc space-y-1 pl-5">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="grid gap-5 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label for="item_name" class="block text-sm font-semibold text-slate-900">Nama Barang</label>
                            <input id="item_name"
                                   name="item_name"
                                   type="text"
                                   value="{{ old('item_name') }}"
                                   class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-600 focus:ring-2 focus:ring-blue-100"
                                   required>
                        </div>

                        <div>
                            <label for="category" class="block text-sm font-semibold text-slate-900">Kategori</label>
                            <select id="category"
                                    name="category"
                                    class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-600 focus:ring-2 focus:ring-blue-100"
                                    required>
                                <option value="">Pilih kategori</option>
                                @foreach($categories as $key => $category)
                                    <option value="{{ $key }}" @selected(old('category') === $key)>{{ $category['title'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="quantity" class="block text-sm font-semibold text-slate-900">Kuantiti</label>
                            <input id="quantity"
                                   name="quantity"
                                   type="number"
                                   min="1"
                                   value="{{ old('quantity', 1) }}"
                                   class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-600 focus:ring-2 focus:ring-blue-100"
                                   required>
                        </div>

                        <div>
                            <label for="item_condition" class="block text-sm font-semibold text-slate-900">Keadaan Barang</label>
                            <select id="item_condition"
                                    name="item_condition"
                                    class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-600 focus:ring-2 focus:ring-blue-100"
                                    required>
                                <option value="">Pilih keadaan</option>
                                @foreach($conditions as $condition)
                                    <option value="{{ $condition }}" @selected(old('item_condition') === $condition)>{{ $condition }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="image" class="block text-sm font-semibold text-slate-900">Gambar Barang</label>
                            <input id="image"
                                   name="image"
                                   type="file"
                                   accept="image/*"
                                   class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm file:mr-3 file:rounded-xl file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-blue-700">
                            <p class="mt-2 text-xs text-slate-500">Optional, maksimum 2MB.</p>
                        </div>

                        <div>
                            <label for="donor_phone" class="block text-sm font-semibold text-slate-900">Nombor Telefon</label>
                            <input id="donor_phone"
                                   name="donor_phone"
                                   type="text"
                                   value="{{ old('donor_phone') }}"
                                   class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-600 focus:ring-2 focus:ring-blue-100">
                        </div>

                        <div class="md:col-span-2">
                            <label for="donor_address" class="block text-sm font-semibold text-slate-900">Alamat Penderma</label>
                            <textarea id="donor_address"
                                      name="donor_address"
                                      rows="3"
                                      class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-600 focus:ring-2 focus:ring-blue-100">{{ old('donor_address') }}</textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label for="expected_delivery_date" class="block text-sm font-semibold text-slate-900">Tarikh Serahan Barang</label>
                            <input id="expected_delivery_date"
                                   name="expected_delivery_date"
                                   type="date"
                                   value="{{ old('expected_delivery_date') }}"
                                   class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-600 focus:ring-2 focus:ring-blue-100"
                                   required>
                        </div>

                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-semibold text-slate-900">Catatan Barang</label>
                            <textarea id="description"
                                      name="description"
                                      rows="4"
                                      class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-600 focus:ring-2 focus:ring-blue-100"
                                      placeholder="Contoh: jenama, saiz, aksesori disertakan, atau nota lain.">{{ old('description') }}</textarea>
                        </div>
                    </div>

                    <div class="mt-7 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm text-slate-500">Admin akan menyemak maklumat ini sebelum barang diterima.</p>
                        <button type="submit"
                                class="inline-flex items-center justify-center rounded-2xl bg-[#1D4ED8] px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1E40AF]">
                            Hantar Untuk Semakan
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const cart = JSON.parse(sessionStorage.getItem('checkout_cart') || '[]');
    const donor = JSON.parse(sessionStorage.getItem('checkout_donor') || '{}');
    const summaryBox = document.getElementById('checkoutPhysicalSummary');
    const itemNameInput = document.getElementById('item_name');
    const categoryInput = document.getElementById('category');
    const quantityInput = document.getElementById('quantity');
    const phoneInput = document.getElementById('donor_phone');
    const addressInput = document.getElementById('donor_address');
    const physicalDonationForm = document.getElementById('physicalDonationForm');
    const expectedDeliveryDate = document.getElementById('expected_delivery_date');
    const donationSubmitButton = physicalDonationForm?.querySelector('button[type="submit"]');

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    if (donor.phone && !phoneInput.value) phoneInput.value = donor.phone;
    if (donor.address && !addressInput.value) {
        addressInput.value = [donor.address, donor.city, donor.postcode].filter(Boolean).join(', ');
    }

    function showDeliveryDateRequiredMessage() {
        expectedDeliveryDate.focus();

        if (window.Swal) {
            Swal.fire({
                icon: 'warning',
                title: 'Maklumat belum lengkap',
                text: 'Tarikh serahan barang wajib diisi.',
                confirmButtonText: 'OK',
                confirmButtonColor: '#1D4ED8'
            });
            return;
        }

        alert('Tarikh serahan barang wajib diisi.');
    }

    function blockEmptyDeliveryDate(event) {
        if (!expectedDeliveryDate || expectedDeliveryDate.value.trim() !== '') {
            return false;
        }

        event.preventDefault();
        event.stopPropagation();
        showDeliveryDateRequiredMessage();

        return true;
    }

    donationSubmitButton?.addEventListener('click', blockEmptyDeliveryDate);
    physicalDonationForm?.addEventListener('submit', blockEmptyDeliveryDate);
    expectedDeliveryDate?.addEventListener('invalid', function (event) {
        if (expectedDeliveryDate.validity.valueMissing) {
            event.preventDefault();
            showDeliveryDateRequiredMessage();
        }
    });

    if (!cart.length) return;

    const firstItem = cart[0];

    if (!itemNameInput.value) itemNameInput.value = firstItem.name || '';
    if (!categoryInput.value) categoryInput.value = firstItem.category || '';
    if (!quantityInput.value || quantityInput.value === '1') quantityInput.value = firstItem.qty || 1;

    summaryBox.innerHTML = cart.map(item => `
        <div class="mb-3 last:mb-0 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
            <p class="font-semibold text-slate-900">${escapeHtml(item.name || '-')}</p>
            <p class="mt-1 text-xs text-slate-500">${Number(item.qty || 1)} unit</p>
        </div>
    `).join('');
});
</script>

@endsection
