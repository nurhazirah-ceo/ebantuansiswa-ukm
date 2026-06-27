@extends('layouts.app')

@section('content')

@php
    $donorProfile = $donorProfile ?? [];
@endphp

<div class="min-h-screen bg-gradient-to-br from-[#f5f7fb] via-white to-[#eef3fb] py-6">
<div class="max-w-6xl mx-auto px-6">

    <div class="mb-6">
        <x-page-hero
            eyebrow="Penderma"
            title="Checkout Sumbangan"
            description="Lengkapkan maklumat penyumbang dan pilih kaedah sumbangan anda."
        />

        @if(session('payment_info'))
            <div class="mt-4 rounded-2xl border border-blue-200 bg-blue-50 px-5 py-4 text-sm font-semibold text-blue-800">
                {{ session('payment_info') }}
            </div>
        @endif
    </div>

    <div class="grid xl:grid-cols-[minmax(0,1fr)_520px] gap-8 items-start">
        <section class="space-y-6">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900 tracking-tight">
                    Summary
                </h2>
            </div>

            <div class="rounded-[2rem] border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div id="checkoutItems" class="divide-y divide-slate-200"></div>
            </div>

            <div>
                <h2 class="text-2xl font-semibold text-slate-900 tracking-tight">
                    Summary Sumbangan
                </h2>
            </div>

            <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                <div class="space-y-4">
                    <div class="flex items-center justify-between gap-4">
                        <p class="text-base text-slate-500">Jumlah Sumbangan Anda</p>
                        <p id="checkoutSubtotal" class="text-lg font-semibold text-slate-900">RM 0.00</p>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <p class="text-base text-slate-500">Jumlah Unit</p>
                        <p id="checkoutTotalQty" class="text-lg font-semibold text-slate-900">0 unit</p>
                    </div>

                    <div class="border-t border-slate-200 pt-4 flex items-center justify-between gap-4">
                        <p class="text-lg font-semibold text-slate-900">Jumlah Bayaran</p>
                        <p id="checkoutGrandTotal" class="text-2xl font-semibold text-slate-900">RM 0.00</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900 tracking-tight">
                    Bagaimana Anda Ingin Teruskan
                </h2>
                <p class="mt-2 text-sm text-slate-500">
                    Pilih kaedah sumbangan dan isikan maklumat penyumbang.
                </p>
            </div>

            <div class="mt-6">
                <p class="text-lg font-semibold text-slate-900 mb-4">
                    Pilih Cara Sumbangan
                </p>

                <div class="grid gap-3">
                    <button type="button"
                        class="delivery-option-btn inline-flex items-center justify-center px-5 py-3 rounded-2xl border border-slate-200 bg-white text-slate-700 text-sm font-semibold transition hover:border-blue-200 hover:bg-blue-50"
                        data-value="online">
                        Pembayaran Atas Talian
                    </button>
                </div>
            </div>

            <div class="mt-8">
                <h3 class="text-2xl font-semibold text-slate-900 tracking-tight">
                    Maklumat Penyumbang
                </h3>
            </div>

            <form id="checkoutDonorForm"
                class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4"
                novalidate
                data-store-url="{{ route('penderma.sumbangan.store') }}"
                data-success-url="{{ route('penderma.sejarah-sumbangan') }}">
                <div>
                    <label class="block mb-2 text-sm font-medium text-slate-700">
                        Nama
                    </label>
                    <input type="text"
                        id="donorName"
                        value="{{ $donorProfile['name'] ?? optional(Auth::user())->name }}"
                        class="w-full border border-slate-200 bg-white rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-slate-700">
                        Email
                    </label>
                    <input type="email"
                        id="donorEmail"
                        value="{{ $donorProfile['email'] ?? optional(Auth::user())->email }}"
                        class="w-full border border-slate-200 bg-white rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-slate-700">
                        Nombor Telefon
                    </label>
                    <input type="text"
                        id="donorPhone"
                        placeholder="Contoh: 0123456789"
                        value="{{ $donorProfile['phone'] ?? '' }}"
                        class="w-full border border-slate-200 bg-white rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-slate-700">
                        Nombor Alternatif
                    </label>
                    <input type="text"
                        id="donorAltPhone"
                        placeholder="Jika ada"
                        value="{{ $donorProfile['alt_phone'] ?? '' }}"
                        class="w-full border border-slate-200 bg-white rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block mb-2 text-sm font-medium text-slate-700">
                        Alamat
                    </label>
                    <textarea id="donorAddress"
                        rows="4"
                        placeholder="Masukkan alamat penuh penderma"
                        class="w-full border border-slate-200 bg-white rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ $donorProfile['address'] ?? '' }}</textarea>
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-slate-700">
                        Bandar
                    </label>
                    <input type="text"
                        id="donorCity"
                        placeholder="Contoh: Shah Alam"
                        value="{{ $donorProfile['city'] ?? '' }}"
                        class="w-full border border-slate-200 bg-white rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-slate-700">
                        Poskod
                    </label>
                    <input type="text"
                        id="donorPostcode"
                        placeholder="Contoh: 40100"
                        value="{{ $donorProfile['postcode'] ?? '' }}"
                        class="w-full border border-slate-200 bg-white rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-slate-700">
                        Negeri
                    </label>
                    <input type="text"
                        id="donorState"
                        placeholder="Contoh: Selangor"
                        value="{{ $donorProfile['state'] ?? '' }}"
                        class="w-full border border-slate-200 bg-white rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-slate-700">
                        Negara
                    </label>
                    <input type="text"
                        id="donorCountry"
                        placeholder="Contoh: Malaysia"
                        value="{{ $donorProfile['country'] ?? 'Malaysia' }}"
                        class="w-full border border-slate-200 bg-white rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="md:col-span-2 mt-2 grid sm:grid-cols-2 gap-3">
                    <a href="{{ route('penderma.menyumbang-bantuan') }}"
                        class="inline-flex items-center justify-center px-5 py-3 rounded-2xl border border-slate-200 bg-white text-slate-700 text-sm font-semibold hover:bg-slate-50 transition">
                        Kembali
                    </a>

                    <button type="button"
                        id="confirmDonationBtn"
                        class="inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow transition">
                        Sahkan Sumbangan
                    </button>
                </div>
            </form>
        </section>
    </div>

</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkoutItems = document.getElementById('checkoutItems');
    const checkoutSubtotal = document.getElementById('checkoutSubtotal');
    const checkoutTotalQty = document.getElementById('checkoutTotalQty');
    const checkoutGrandTotal = document.getElementById('checkoutGrandTotal');
    const deliveryOptionButtons = document.querySelectorAll('.delivery-option-btn');
    const checkoutDonorForm = document.getElementById('checkoutDonorForm');
    const confirmDonationBtn = document.getElementById('confirmDonationBtn');
    const storeSumbanganUrl = checkoutDonorForm.dataset.storeUrl;
    const successRedirectUrl = checkoutDonorForm.dataset.successUrl;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    let selectedCheckoutMethod = sessionStorage.getItem('checkout_method') || 'online';
    let confirmedCheckoutPayment = false;

    const categoryLabels = {
        keperluan: 'Keperluan Asas',
        keperluan_asas: 'Keperluan Asas',
        pembelajaran: 'Pembelajaran',
        alat_tulis_pembelajaran: 'Alat Tulis Pembelajaran',
        peralatan_pembelajaran: 'Peralatan Pembelajaran',
        sukan: 'Sukan'
    };

    const methodLabels = {
        online: 'Pembayaran Atas Talian'
    };

    if (!Object.prototype.hasOwnProperty.call(methodLabels, selectedCheckoutMethod)) {
        selectedCheckoutMethod = 'online';
    }

    sessionStorage.setItem('checkout_method', selectedCheckoutMethod);

    function formatCurrency(value) {
        return 'RM ' + Number(value || 0).toFixed(2);
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function normalizeCart(rawCart) {
        return rawCart.map(item => ({
            id: item.id,
            name: item.name,
            price: Number(item.price) || 0,
            qty: Math.max(parseInt(item.qty, 10) || 1, 1),
            img: item.img || '',
            category: item.category || 'keperluan_asas'
        }));
    }

    function calculateSummary(cartItems, previousSummary) {
        const categories = [...new Set(cartItems.map(item => item.category))];
        const totalQty = cartItems.reduce((sum, item) => sum + item.qty, 0);
        const totalAmount = cartItems.reduce((sum, item) => sum + (item.price * item.qty), 0);
        const resolvedTotalQty = Number(previousSummary.total_qty || totalQty);
        const resolvedTotalAmount = Number(previousSummary.total_amount || totalAmount);

        return {
            ...previousSummary,
            categories: previousSummary.categories || categories.map(category => categoryLabels[category] || category),
            total_qty: resolvedTotalQty,
            total_amount: resolvedTotalAmount,
            help_total: resolvedTotalAmount
        };
    }

    function setConfirmLoading(isLoading) {
        confirmDonationBtn.disabled = isLoading;
        confirmDonationBtn.classList.toggle('opacity-60', isLoading);
        confirmDonationBtn.classList.toggle('cursor-wait', isLoading);
        confirmDonationBtn.textContent = isLoading ? 'Memproses...' : 'Sahkan Sumbangan';
    }

    function clearCheckoutStorage() {
        localStorage.removeItem('cart');
        [
            'checkout_cart',
            'checkout_summary',
            'checkout_method',
            'checkout_donor'
        ].forEach(key => sessionStorage.removeItem(key));

        window.dispatchEvent(new CustomEvent('cart-updated', {
            detail: []
        }));
    }

    function getResponseErrorMessage(data) {
        if (data?.errors) {
            const errors = Object.values(data.errors).flat().filter(Boolean);

            if (errors.length > 0) {
                return errors.join('\n');
            }
        }

        if (data?.message) {
            return data.message;
        }

        return 'Sumbangan tidak dapat diproses buat masa ini. Sila cuba lagi.';
    }

    function showCheckoutAlert(options = {}) {
        return Swal.fire({
            icon: options.icon || 'error',
            title: options.title || 'Sumbangan Tidak Berjaya',
            text: options.text,
            html: options.html,
            confirmButtonText: options.confirmButtonText || 'Semak Semula',
            confirmButtonColor: options.confirmButtonColor || '#dc2626',
            background: '#ffffff',
            color: '#0f172a',
            width: 420,
            padding: '1.6rem',
            customClass: {
                popup: 'rounded-3xl shadow-xl',
                title: 'text-2xl font-bold text-slate-900',
                confirmButton: 'rounded-2xl px-7 py-3 font-semibold shadow'
            }
        });
    }

    function getDonationAmount() {
        return Number(summary.total_amount || 0);
    }

    function validateCheckoutBeforeSubmit(donorDetails) {
        const missingFields = [
            {
                label: 'Nama',
                value: donorDetails.name,
                element: document.getElementById('donorName')
            },
            {
                label: 'Email',
                value: donorDetails.email,
                element: document.getElementById('donorEmail')
            },
            {
                label: 'Nombor telefon',
                value: donorDetails.phone,
                element: document.getElementById('donorPhone')
            },
            {
                label: 'Alamat',
                value: donorDetails.address,
                element: document.getElementById('donorAddress')
            },
            {
                label: 'Bandar',
                value: donorDetails.city,
                element: document.getElementById('donorCity')
            },
            {
                label: 'Poskod',
                value: donorDetails.postcode,
                element: document.getElementById('donorPostcode')
            },
            {
                label: 'Negeri',
                value: donorDetails.state,
                element: document.getElementById('donorState')
            },
            {
                label: 'Negara',
                value: donorDetails.country,
                element: document.getElementById('donorCountry')
            },
            {
                label: 'Cara sumbangan',
                value: selectedCheckoutMethod,
                element: document.querySelector('.delivery-option-btn')
            }
        ].filter(field => !String(field.value || '').trim());

        if (missingFields.length === 0) {
            const phonePattern = /^01\d{8,9}$/;
            const postcodePattern = /^\d{5}$/;
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const invalidFields = [
                {
                    label: 'Email mesti menggunakan format yang sah',
                    invalid: !emailPattern.test(donorDetails.email),
                    element: document.getElementById('donorEmail')
                },
                {
                    label: 'Nombor telefon mesti mengikut format 0123456789',
                    invalid: !phonePattern.test(donorDetails.phone),
                    element: document.getElementById('donorPhone')
                },
                {
                    label: 'Nombor alternatif mesti mengikut format 0123456789',
                    invalid: donorDetails.alt_phone !== '' && !phonePattern.test(donorDetails.alt_phone),
                    element: document.getElementById('donorAltPhone')
                },
                {
                    label: 'Poskod mesti 5 digit',
                    invalid: !postcodePattern.test(donorDetails.postcode),
                    element: document.getElementById('donorPostcode')
                }
            ].filter(field => field.invalid);

            if (invalidFields.length === 0) {
                return true;
            }

            const invalidListItems = invalidFields
                .map(field => `<li>${escapeHtml(field.label)}</li>`)
                .join('');

            showCheckoutAlert({
                title: 'Format Tidak Sah',
                html: `
                    <div style="font-size:15px; color:#475569; line-height:1.6; text-align:left;">
                        <p style="margin:0 0 0.75rem;">Sila semak format maklumat berikut:</p>
                        <ul style="margin:0; padding-left:1.25rem;">${invalidListItems}</ul>
                    </div>
                `
            }).then(() => {
                invalidFields[0]?.element?.focus();
            });

            return false;
        }

        const listItems = missingFields
            .map(field => `<li>${escapeHtml(field.label)}</li>`)
            .join('');

        showCheckoutAlert({
            title: 'Maklumat Tidak Lengkap',
            html: `
                <div style="font-size:15px; color:#475569; line-height:1.6; text-align:left;">
                    <p style="margin:0 0 0.75rem;">Sila lengkapkan maklumat berikut sebelum meneruskan:</p>
                    <ul style="margin:0; padding-left:1.25rem;">${listItems}</ul>
                </div>
            `
        }).then(() => {
            missingFields[0]?.element?.focus();
        });

        return false;
    }

    async function submitDonationToBackend(cartItems, checkoutSummary, donorDetails) {
        const response = await fetch(storeSumbanganUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                items: cartItems.map(item => ({
                    id: item.id,
                    qty: item.qty
                })),
                cart_payload: JSON.stringify(cartItems),
                donor: donorDetails,
                kaedah_sumbangan: methodLabels[selectedCheckoutMethod] || selectedCheckoutMethod,
                catatan: ''
            })
        });

        const responseText = await response.text();
        let data = {};

        if (responseText) {
            try {
                data = JSON.parse(responseText);
            } catch (error) {
                data = {};
            }
        }

        if (!response.ok) {
            throw new Error(getResponseErrorMessage(data));
        }

        return data;
    }

    function updateDeliveryOptionUI() {
        deliveryOptionButtons.forEach(button => {
            const isActive = button.dataset.value === selectedCheckoutMethod;
            button.classList.toggle('bg-[#5747E8]', isActive);
            button.classList.toggle('text-white', isActive);
            button.classList.toggle('border-[#5747E8]', isActive);
            button.classList.toggle('shadow', isActive);
            button.classList.toggle('bg-white', !isActive);
            button.classList.toggle('text-slate-700', !isActive);
            button.classList.toggle('border-slate-200', !isActive);
        });
    }

    const storedCheckoutCart = JSON.parse(sessionStorage.getItem('checkout_cart') || '[]');
    const storedLocalCart = JSON.parse(localStorage.getItem('cart') || '[]');
    const cart = normalizeCart(storedCheckoutCart.length ? storedCheckoutCart : storedLocalCart);
    const storedSummary = JSON.parse(sessionStorage.getItem('checkout_summary') || '{}');
    const summary = calculateSummary(cart, storedSummary);
    const donor = JSON.parse(sessionStorage.getItem('checkout_donor') || '{}');

    sessionStorage.setItem('checkout_cart', JSON.stringify(cart));
    sessionStorage.setItem('checkout_summary', JSON.stringify(summary));

    function updateTotals() {
        const donationAmount = Number(summary.total_amount || 0);

        checkoutSubtotal.textContent = formatCurrency(donationAmount);
        checkoutTotalQty.textContent = `${summary.total_qty || 0} unit`;
        checkoutGrandTotal.textContent = formatCurrency(donationAmount);
        summary.help_total = donationAmount;
        sessionStorage.setItem('checkout_summary', JSON.stringify(summary));
    }

    if (donor.phone) document.getElementById('donorPhone').value = donor.phone;
    if (donor.alt_phone) document.getElementById('donorAltPhone').value = donor.alt_phone;
    if (donor.address) document.getElementById('donorAddress').value = donor.address;
    if (donor.city) document.getElementById('donorCity').value = donor.city;
    if (donor.postcode) document.getElementById('donorPostcode').value = donor.postcode;
    if (donor.state) document.getElementById('donorState').value = donor.state;
    if (donor.country) document.getElementById('donorCountry').value = donor.country;

    if (cart.length === 0) {
        checkoutItems.innerHTML = `
            <div class="p-6 text-center">
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-5 py-6">
                    <p class="text-sm font-semibold text-slate-700">Tiada item untuk checkout.</p>
                    <p class="mt-2 text-sm text-slate-500">Tambah item sumbangan sebelum meneruskan checkout.</p>
                </div>
            </div>
        `;
        confirmDonationBtn.classList.add('opacity-50', 'cursor-not-allowed');
    } else {
        confirmDonationBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        cart.forEach(item => {
            const categoryLabel = categoryLabels[item.category] || item.category;
            const safeName = escapeHtml(item.name);
            const safeImage = escapeHtml(item.img);
            const safeCategory = escapeHtml(categoryLabel);
            checkoutItems.innerHTML += `
                <div class="p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-4 min-w-0">
                            <div class="w-20 h-20 rounded-3xl border border-slate-200 bg-white overflow-hidden shadow-sm shrink-0">
                                <img src="${safeImage}" alt="${safeName}" class="w-full h-full object-cover">
                            </div>
                            <div class="min-w-0">
                                <p class="text-lg font-semibold text-slate-900">${safeName}</p>
                                <p class="mt-2 text-sm text-slate-500">${safeCategory}</p>
                                <p class="mt-1 text-sm text-slate-500">${item.qty} unit x ${formatCurrency(item.price)}</p>
                            </div>
                        </div>
                        <p class="text-xl font-semibold text-[#5747E8]">${formatCurrency(item.qty * item.price)}</p>
                    </div>
                </div>
            `;
        });
    }

    updateTotals();

    deliveryOptionButtons.forEach(button => {
        button.addEventListener('click', function () {
            selectedCheckoutMethod = button.dataset.value;
            sessionStorage.setItem('checkout_method', selectedCheckoutMethod);
            updateDeliveryOptionUI();
        });
    });

    confirmDonationBtn.addEventListener('click', function () {
        if (confirmDonationBtn.disabled) {
            return;
        }

        checkoutDonorForm.requestSubmit();
    });

    checkoutDonorForm.addEventListener('submit', async function (event) {
        event.preventDefault();

        if (cart.length === 0 || getDonationAmount() <= 0) {
            showCheckoutAlert({
                icon: 'warning',
                title: cart.length === 0 ? 'Tiada Item' : 'Jumlah Sumbangan Tidak Sah',
                text: cart.length === 0
                    ? 'Sila tambah sekurang-kurangnya satu item sebelum sahkan sumbangan.'
                    : 'Jumlah sumbangan mesti melebihi RM0.00 sebelum sahkan sumbangan.',
                confirmButtonText: 'Kembali',
                confirmButtonColor: '#2563eb'
            });
            return;
        }

        const donorDetails = {
            name: document.getElementById('donorName').value,
            email: document.getElementById('donorEmail').value,
            phone: document.getElementById('donorPhone').value.trim(),
            alt_phone: document.getElementById('donorAltPhone').value.trim(),
            address: document.getElementById('donorAddress').value.trim(),
            city: document.getElementById('donorCity').value.trim(),
            postcode: document.getElementById('donorPostcode').value.trim(),
            state: document.getElementById('donorState').value.trim(),
            country: document.getElementById('donorCountry').value.trim()
        };

        if (!validateCheckoutBeforeSubmit(donorDetails)) {
            return;
        }

        sessionStorage.setItem('checkout_method', selectedCheckoutMethod);
        sessionStorage.setItem('checkout_donor', JSON.stringify(donorDetails));

        if (!confirmedCheckoutPayment) {
            Swal.fire({
                icon: 'question',
                title: 'Teruskan pembayaran?',
                text: 'Adakah anda ingin teruskan pembayaran?',
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: 'Tidak',
                confirmButtonColor: '#1D4ED8',
                cancelButtonColor: '#64748b'
            }).then(function (result) {
                if (result.isConfirmed) {
                    confirmedCheckoutPayment = true;
                    checkoutDonorForm.requestSubmit();
                }
            });
            return;
        }

        confirmedCheckoutPayment = false;

        setConfirmLoading(true);

        try {
            const data = await submitDonationToBackend(cart, summary, donorDetails);
            const paymentUrl = data?.redirect_url || data?.payment_url;

            if (!paymentUrl) {
                throw new Error('Pautan pembayaran ToyyibPay tidak diterima. Sila cuba lagi.');
            }

            window.location.href = paymentUrl;
        } catch (error) {
            setConfirmLoading(false);

            showCheckoutAlert({
                title: 'Pembayaran Tidak Dapat Dimulakan',
                text: error.message || 'ToyyibPay tidak dapat dimulakan buat masa ini. Sila cuba lagi.',
                confirmButtonText: 'Cuba Lagi',
                confirmButtonColor: '#2563eb'
            });
        }
    });

    updateDeliveryOptionUI();
});
</script>

@endsection
