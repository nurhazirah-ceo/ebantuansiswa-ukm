@extends('layouts.app')

@section('content')

<div class="min-h-screen bg-gradient-to-br from-[#f4f6fb] via-[#eef1f7] to-white py-10">
<div class="max-w-5xl mx-auto px-6">

    <x-page-hero
        class="mb-8"
        eyebrow="Penderma"
        title="Detail Penghantaran Barang"
        description="Lengkapkan tarikh dan masa anggaran untuk hantar sendiri ke pusat bantuan."
    />

    <form id="physicalDeliveryForm"
          method="POST"
          action="{{ route('penderma.serahan-barang.store') }}"
          class="grid lg:grid-cols-[1.2fr_0.8fr] gap-6"
          data-physical-donation-form
          data-confirm
          data-confirm-title="Sahkan penghantaran barang?"
          data-confirm-text="Maklumat penghantaran akan dihantar untuk semakan admin."
          data-confirm-button="Ya, sahkan"
          data-confirm-color="#1D4ED8">
        @csrf

        <input type="hidden" id="itemNameField" name="item_name" value="{{ old('item_name') }}">
        <input type="hidden" id="categoryField" name="category" value="{{ old('category') }}">
        <input type="hidden" id="quantityField" name="quantity" value="{{ old('quantity') }}">
        <input type="hidden" name="item_condition" value="{{ old('item_condition', 'Baharu') }}">
        <input type="hidden" id="donorPhoneField" name="donor_phone" value="{{ old('donor_phone') }}">
        <input type="hidden" id="donorAddressField" name="donor_address" value="{{ old('donor_address') }}">
        <input type="hidden" name="delivery_method" value="{{ \App\Models\PhysicalDonation::DELIVERY_SELF }}">

        <div class="bg-white rounded-3xl border border-slate-200 shadow-xl p-7">
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-slate-800">
                    Maklumat Penghantaran
                </h2>
            </div>

            @if($errors->any())
                <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700">
                    <p class="font-semibold">Sila semak semula maklumat penghantaran.</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="space-y-5">
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <h3 class="text-base font-semibold text-slate-800 mb-4">
                        Maklumat Penyumbang
                    </h3>

                    <div class="grid md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Nama</p>
                            <p id="shippingDonorName" class="mt-2 text-slate-800">-</p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Email</p>
                            <p id="shippingDonorEmail" class="mt-2 text-slate-800">-</p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Nombor Telefon</p>
                            <p id="shippingDonorPhone" class="mt-2 text-slate-800">-</p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Telefon Alternatif</p>
                            <p id="shippingDonorAltPhone" class="mt-2 text-slate-800">-</p>
                        </div>

                        <div class="md:col-span-2">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Alamat Penderma</p>
                            <p id="shippingDonorAddress" class="mt-2 text-slate-800 leading-6">-</p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-slate-700">
                        Lokasi Pusat Bantuan
                    </label>
                    <textarea id="delivery_location"
                              name="delivery_location"
                              rows="4"
                              readonly
                              class="w-full border border-slate-200 bg-slate-50 rounded-2xl px-4 py-3 text-sm">{{ old('delivery_location', 'Pusat Bantuan Pelajar
Universiti Contoh
Blok Hal Ehwal Pelajar
43000 Kajang, Selangor') }}</textarea>
                </div>

                <div class="grid md:grid-cols-2 gap-5">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-slate-700">
                            Tarikh Serahan Barang
                        </label>
                        <input id="expected_delivery_date"
                               name="expected_delivery_date"
                               type="date"
                               value="{{ old('expected_delivery_date') }}"
                               class="w-full border border-slate-200 bg-slate-50 rounded-2xl px-4 py-3 text-sm"
                               required>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-slate-700">
                            Masa Anggaran
                        </label>
                        <select id="delivery_time"
                                name="delivery_time"
                                class="w-full border border-slate-200 bg-slate-50 rounded-2xl px-4 py-3 text-sm">
                            <option value="">-- Pilih Masa Anggaran --</option>
                            <option @selected(old('delivery_time') === '8:00 pagi - 10:00 pagi')>8:00 pagi - 10:00 pagi</option>
                            <option @selected(old('delivery_time') === '10:00 pagi - 12:00 tengah hari')>10:00 pagi - 12:00 tengah hari</option>
                            <option @selected(old('delivery_time') === '12:00 tengah hari - 2:00 petang')>12:00 tengah hari - 2:00 petang</option>
                            <option @selected(old('delivery_time') === '2:00 petang - 4:00 petang')>2:00 petang - 4:00 petang</option>
                            <option @selected(old('delivery_time') === '4:00 petang - 6:00 petang')>4:00 petang - 6:00 petang</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-6 shadow-xl h-fit">
            <div class="mb-5">
                <h2 class="text-lg font-semibold text-slate-800">
                    Jenis Item
                </h2>
            </div>

            <div id="shippingSummaryItems" class="space-y-3 mb-5"></div>

            <div class="border-t border-slate-200 pt-5 space-y-4">
                <div class="flex justify-between text-sm text-slate-500">
                    <span>Jumlah Unit</span>
                    <span id="shippingTotalQty">0 unit</span>
                </div>

                <div class="border-t border-slate-200 pt-4 flex justify-between items-end">
                    <div>
                        <p class="text-sm text-slate-500">Jumlah Sumbangan</p>
                        <p id="shippingTotalAmount" class="text-3xl font-semibold text-[#0B1F3A]">RM 0.00</p>
                    </div>
                    <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">
                        Semakan
                    </span>
                </div>
            </div>

            <div class="mt-6 space-y-3">
                <button type="submit"
                    class="w-full px-5 py-3 rounded-2xl bg-[#10B981] text-white font-semibold hover:bg-[#059669] transition">
                    Sahkan Penghantaran
                </button>

                <a href="{{ route('penderma.menyumbang-bantuan') }}"
                   class="block text-center px-6 py-3 rounded-2xl border border-[#3155E7] bg-[#3155E7] text-white text-sm font-medium hover:bg-[#2647D6] transition shadow-sm">
                    Kembali
                </a>
            </div>
        </div>
    </form>

</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const summaryItems = document.getElementById('shippingSummaryItems');
    const totalQty = document.getElementById('shippingTotalQty');
    const totalAmount = document.getElementById('shippingTotalAmount');
    const donor = JSON.parse(sessionStorage.getItem('checkout_donor') || '{}');
    const cart = JSON.parse(sessionStorage.getItem('checkout_cart') || '[]');
    const summary = JSON.parse(sessionStorage.getItem('checkout_summary') || '{}');
    const itemNameField = document.getElementById('itemNameField');
    const categoryField = document.getElementById('categoryField');
    const quantityField = document.getElementById('quantityField');
    const donorPhoneField = document.getElementById('donorPhoneField');
    const donorAddressField = document.getElementById('donorAddressField');
    const physicalDeliveryForm = document.getElementById('physicalDeliveryForm');
    const expectedDeliveryDate = document.getElementById('expected_delivery_date');
    const deliverySubmitButton = physicalDeliveryForm?.querySelector('button[type="submit"]');

    const categoryAliases = {
        keperluan: 'keperluan_asas',
        keperluan_asas: 'keperluan_asas',
        pembelajaran: 'pembelajaran',
        sukan: 'sukan'
    };

    function formatCurrency(value) {
        return 'RM ' + Number(value || 0).toFixed(2);
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

    deliverySubmitButton?.addEventListener('click', blockEmptyDeliveryDate);
    physicalDeliveryForm?.addEventListener('submit', blockEmptyDeliveryDate);
    expectedDeliveryDate?.addEventListener('invalid', function (event) {
        if (expectedDeliveryDate.validity.valueMissing) {
            event.preventDefault();
            showDeliveryDateRequiredMessage();
        }
    });

    document.getElementById('shippingDonorName').textContent = donor.name || '-';
    document.getElementById('shippingDonorEmail').textContent = donor.email || '-';
    document.getElementById('shippingDonorPhone').textContent = donor.phone || '-';
    document.getElementById('shippingDonorAltPhone').textContent = donor.alt_phone || '-';
    document.getElementById('shippingDonorAddress').textContent = donor.address || '-';
    donorPhoneField.value = donor.phone || donorPhoneField.value;
    donorAddressField.value = [donor.address, donor.city, donor.postcode].filter(Boolean).join(', ') || donorAddressField.value;

    if (cart.length === 0) {
        summaryItems.innerHTML = `
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-5 py-6 text-center">
                <p class="text-sm font-semibold text-slate-700">Tiada item untuk dihantar.</p>
                <p class="mt-2 text-sm text-slate-500">Pilih item sumbangan sebelum mengisi maklumat penghantaran.</p>
            </div>
        `;
        return;
    }

    const firstItem = cart[0];
    const totalQuantity = summary.total_qty || cart.reduce((total, item) => total + (Number(item.qty) || 1), 0);
    const itemNames = cart
        .map(item => `${item.name || '-'} (${Number(item.qty) || 1} unit)`)
        .join(', ');

    itemNameField.value = itemNames.slice(0, 255);
    categoryField.value = categoryAliases[firstItem.category] || firstItem.category || '';
    quantityField.value = totalQuantity;

    cart.forEach(item => {
        summaryItems.innerHTML += `
            <div class="flex justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-4">
                <div>
                    <p class="font-medium text-slate-800">${item.name}</p>
                    <p class="text-xs text-slate-500 mt-1">${item.qty} unit x ${formatCurrency(item.price)}</p>
                </div>
                <p class="font-semibold text-[#0B1F3A]">${formatCurrency(item.qty * item.price)}</p>
            </div>
        `;
    });

    totalQty.textContent = `${summary.total_qty || 0} unit`;
    totalAmount.textContent = formatCurrency(summary.total_amount);
});
</script>

@endsection
