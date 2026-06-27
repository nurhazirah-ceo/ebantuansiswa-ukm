@extends('layouts.app')

@section('content')

<div class="min-h-screen bg-gradient-to-br from-[#f4f6fb] via-[#eef1f7] to-white py-4 md:py-6">
<div class="max-w-6xl mx-auto px-6">

    <x-page-hero
        class="mb-5"
        eyebrow="Penderma"
        title="Borang Bantuan"
        description="Semak item yang dipilih sebelum meneruskan sumbangan."
    />

    <div id="donationForm"
        class="space-y-6"
        data-donor-name="{{ optional(Auth::user())->name ?? '' }}"
        data-donor-email="{{ optional(Auth::user())->email ?? '' }}"
        data-checkout-url="{{ route('penderma.checkout-sumbangan') }}">
        <input type="hidden" id="cartPayload" name="cart_payload">
        <div id="hiddenCartInputs"></div>

        <div class="bg-white rounded-3xl border border-slate-200 shadow-xl p-7">
            <div class="flex items-start justify-between gap-4 flex-wrap mb-6">
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900 tracking-tight">
                        Maklumat Sumbangan
                    </h2>
                    <p class="text-sm text-slate-500 mt-2">
                        Ubah unit, buang item, atau tambah item lain sebelum teruskan.
                    </p>
                </div>

                <a href="{{ route('penderma.sumbangan') }}"
                   class="inline-flex items-center px-5 py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium shadow transition">
                    + Tambah Item Lain
                </a>
            </div>

            <div id="emptyCartState" class="hidden rounded-2xl border border-dashed border-amber-300 bg-amber-50 px-5 py-5 text-center">
                <p class="text-sm font-semibold text-amber-900">Tiada item dalam cart trolley.</p>
                <p class="mt-2 text-sm text-amber-800">Kembali ke halaman sumbangan untuk tambah item terlebih dahulu.</p>
            </div>

            <div id="donationFields" class="space-y-6">
                <div class="rounded-[2rem] border border-slate-200 bg-gradient-to-br from-white to-slate-50/80 shadow-sm overflow-hidden">
                    <div class="hidden lg:grid lg:grid-cols-[minmax(0,1.8fr)_160px_170px_150px_110px] gap-4 px-7 py-4 border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                        <div>Produk</div>
                        <div>Kategori</div>
                        <div>Kuantiti</div>
                        <div>Harga</div>
                        <div class="text-right">Tindakan</div>
                    </div>

                    <div id="cartSelectedItems" class="divide-y divide-slate-200"></div>
                </div>

                <div class="grid gap-5 lg:grid-cols-2">
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-6 shadow-sm">
                        <div class="space-y-5">
                            <div class="flex items-center justify-between gap-4">
                                <p class="text-lg font-semibold text-slate-900">Kategori Dipilih</p>
                                <p id="kategoriDipilihAuto" class="text-base font-semibold text-slate-900 text-right">-</p>
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <p class="text-lg font-semibold text-slate-900">Jumlah Unit</p>
                                <p id="jumlahUnitAuto" class="text-lg font-semibold text-slate-900 text-right">0 Unit</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-slate-900 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between gap-4">
                            <p class="text-lg font-semibold text-black">Jumlah Sumbangan</p>
                            <p id="jumlahBayaranAuto" class="text-2xl font-semibold text-black text-right">RM 0.00</p>
                        </div>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <a href="{{ route('penderma.sumbangan') }}"
                        class="inline-flex items-center justify-center px-8 py-3.5 rounded-2xl bg-[#111827] hover:bg-[#0f172a] text-white text-sm md:text-[15px] font-semibold shadow transition">
                        Kembali ke Jenis Bantuan
                    </a>

                    <button type="button"
                        id="submitBtn"
                        class="w-full px-8 py-3.5 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white text-sm md:text-[15px] font-semibold shadow transition">
                        Terus Sumbang
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const categoryLabels = {
        keperluan: 'Keperluan Asas',
        keperluan_asas: 'Keperluan Asas',
        pembelajaran: 'Pembelajaran',
        alat_tulis_pembelajaran: 'Alat Tulis Pembelajaran',
        peralatan_pembelajaran: 'Peralatan Pembelajaran',
        sukan: 'Sukan'
    };

    const emptyCartState = document.getElementById('emptyCartState');
    const donationFields = document.getElementById('donationFields');
    const submitBtn = document.getElementById('submitBtn');
    const selectedCategoriesField = document.getElementById('kategoriDipilihAuto');
    const totalUnitsField = document.getElementById('jumlahUnitAuto');
    const cartPayload = document.getElementById('cartPayload');
    const hiddenCartInputs = document.getElementById('hiddenCartInputs');
    const cartSelectedItems = document.getElementById('cartSelectedItems');
    const jumlahBayaranField = document.getElementById('jumlahBayaranAuto');
    const donationForm = document.getElementById('donationForm');
    const donorName = donationForm.dataset.donorName || '';
    const donorEmail = donationForm.dataset.donorEmail || '';
    const checkoutUrl = donationForm.dataset.checkoutUrl;

    function getCart() {
        try {
            const cart = JSON.parse(localStorage.getItem('cart') || '[]');
            return Array.isArray(cart) ? cart : [];
        } catch (error) {
            localStorage.removeItem('cart');
            return [];
        }
    }

    function saveCart(cart) {
        localStorage.setItem('cart', JSON.stringify(cart));
        window.dispatchEvent(new CustomEvent('cart-updated', { detail: cart }));
    }

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

    function inferCategory(item) {
        if (item.category) return item.category;

        const id = Number(item.id);
        if (id >= 200) return 'sukan';
        if (id >= 100) return 'pembelajaran';

        return 'keperluan_asas';
    }

    function normalizeCart(cart) {
        return cart.map(item => ({
            id: item.id,
            name: item.name,
            price: Number(item.price) || 0,
            qty: Math.max(parseInt(item.qty, 10) || 1, 1),
            img: item.img || '',
            category: inferCategory(item)
        }));
    }

    function getDonorDetails() {
        return {
            name: donorName,
            email: donorEmail,
            phone: '',
            alt_phone: '',
            address: '',
            city: '',
            postcode: '',
            state: '',
            country: 'Malaysia'
        };
    }

    function changeItemQty(id, nextQty) {
        let cart = normalizeCart(getCart());
        cart = cart.map(item => item.id == id
            ? { ...item, qty: Math.max(nextQty, 1) }
            : item
        );
        saveCart(cart);
    }

    function removeItem(id) {
        const cart = normalizeCart(getCart()).filter(item => item.id != id);
        saveCart(cart);
    }

    function getTotalAmount(cart) {
        return cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
    }

    function persistCheckoutSnapshot(cart, summary) {
        sessionStorage.setItem('checkout_cart', JSON.stringify(cart));
        sessionStorage.setItem('checkout_summary', JSON.stringify(summary));
        sessionStorage.setItem('checkout_method', '');
        sessionStorage.setItem('checkout_donor', JSON.stringify(getDonorDetails()));
    }

    function renderCheckoutFromCart() {
        const cart = normalizeCart(getCart());

        if (cart.length === 0) {
            emptyCartState.classList.remove('hidden');
            donationFields.classList.add('hidden');
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            cartSelectedItems.innerHTML = '';
            hiddenCartInputs.innerHTML = '';
            cartPayload.value = '';
            selectedCategoriesField.textContent = '-';
            totalUnitsField.textContent = '0 Unit';
            jumlahBayaranField.textContent = 'RM 0.00';
            return;
        }

        emptyCartState.classList.add('hidden');
        donationFields.classList.remove('hidden');
        submitBtn.disabled = false;
        submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');

        const categories = [...new Set(cart.map(item => item.category))];
        const totalQty = cart.reduce((sum, item) => sum + item.qty, 0);
        const totalAmount = getTotalAmount(cart);

        selectedCategoriesField.textContent = categories
            .map(category => categoryLabels[category] || category)
            .join(', ');
        totalUnitsField.textContent = `${totalQty} Unit`;
        jumlahBayaranField.textContent = formatCurrency(totalAmount);

        cartSelectedItems.innerHTML = '';
        hiddenCartInputs.innerHTML = '';
        cartPayload.value = JSON.stringify(cart);

        cart.forEach((item, index) => {
            const categoryLabel = categoryLabels[item.category] || item.category;
            const subtotal = item.price * item.qty;
            const safeName = escapeHtml(item.name);
            const safeImage = escapeHtml(item.img);
            const safeCategory = escapeHtml(categoryLabel);
            const safeItemCategory = escapeHtml(item.category);

            cartSelectedItems.innerHTML += `
                <div class="px-5 py-5 lg:px-7 lg:py-6">
                    <div class="grid gap-5 lg:grid-cols-[minmax(0,1.8fr)_160px_170px_150px_110px] lg:items-center">
                        <div class="flex items-start gap-4 min-w-0">
                            <div class="w-24 h-24 rounded-3xl border border-slate-200 bg-white overflow-hidden shadow-sm shrink-0">
                                <img src="${safeImage}" alt="${safeName}" class="w-full h-full object-cover">
                            </div>
                            <div class="min-w-0 pt-1">
                                <p class="text-lg font-semibold text-slate-900 truncate">${safeName}</p>
                                <p class="text-xs text-slate-400 mt-2">ID Item: ${item.id}</p>
                            </div>
                        </div>

                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400 lg:hidden mb-2">Kategori</p>
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-blue-50 text-blue-700 text-sm font-medium border border-blue-100">
                                ${safeCategory}
                            </span>
                        </div>

                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400 lg:hidden mb-2">Kuantiti</p>
                            <div class="inline-flex items-center rounded-full border border-slate-200 bg-white px-2 py-1 shadow-sm">
                                <button type="button" class="qty-btn w-10 h-10 rounded-full text-lg text-slate-700 hover:bg-slate-100 transition" data-action="dec" data-id="${item.id}">-</button>
                                <input type="number" min="1" value="${item.qty}" class="qty-input w-14 text-center bg-transparent text-base font-semibold text-slate-800 focus:outline-none" data-id="${item.id}">
                                <button type="button" class="qty-btn w-10 h-10 rounded-full text-lg text-slate-700 hover:bg-slate-100 transition" data-action="inc" data-id="${item.id}">+</button>
                            </div>
                        </div>

                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400 lg:hidden mb-2">Harga</p>
                            <p class="text-sm text-slate-500">${formatCurrency(item.price)} / unit</p>
                            <p class="text-xl font-semibold text-[#0B1F3A] mt-1">${formatCurrency(subtotal)}</p>
                        </div>

                        <div class="flex lg:justify-end">
                            <button type="button" class="remove-cart-item w-full lg:w-auto px-4 py-2.5 rounded-2xl border border-red-200 text-red-600 text-sm font-medium hover:bg-red-50 transition" data-id="${item.id}">
                                Buang
                            </button>
                        </div>
                    </div>
                </div>
            `;

            hiddenCartInputs.innerHTML += `
                <input type="hidden" name="items[${index}][id]" value="${item.id}">
                <input type="hidden" name="items[${index}][name]" value="${safeName}">
                <input type="hidden" name="items[${index}][category]" value="${safeItemCategory}">
                <input type="hidden" name="items[${index}][price]" value="${item.price}">
                <input type="hidden" name="items[${index}][qty]" value="${item.qty}">
            `;
        });

        persistCheckoutSnapshot(cart, {
            categories: categories.map(category => categoryLabels[category] || category),
            total_qty: totalQty,
            total_amount: totalAmount,
            help_total: totalAmount
        });
    }

    cartSelectedItems.addEventListener('click', function (event) {
        const qtyBtn = event.target.closest('.qty-btn');
        const removeBtn = event.target.closest('.remove-cart-item');

        if (qtyBtn) {
            const id = qtyBtn.dataset.id;
            const input = cartSelectedItems.querySelector(`.qty-input[data-id="${id}"]`);
            const currentQty = parseInt(input?.value || 1, 10) || 1;
            const nextQty = qtyBtn.dataset.action === 'inc' ? currentQty + 1 : currentQty - 1;
            changeItemQty(id, nextQty);
            return;
        }

        if (removeBtn) {
            removeItem(removeBtn.dataset.id);
        }
    });

    cartSelectedItems.addEventListener('change', function (event) {
        const input = event.target.closest('.qty-input');

        if (!input) return;

        changeItemQty(input.dataset.id, Math.max(parseInt(input.value, 10) || 1, 1));
    });

    submitBtn.addEventListener('click', function (event) {
        event.preventDefault();

        const cart = normalizeCart(getCart());

        if (cart.length === 0) return;

        const categories = [...new Set(cart.map(item => item.category))];
        const totalQty = cart.reduce((sum, item) => sum + item.qty, 0);
        const totalAmount = getTotalAmount(cart);

        persistCheckoutSnapshot(cart, {
            categories: categories.map(category => categoryLabels[category] || category),
            total_qty: totalQty,
            total_amount: totalAmount,
            help_total: totalAmount
        });

        window.location.href = checkoutUrl;
    });

    window.addEventListener('cart-updated', renderCheckoutFromCart);
    renderCheckoutFromCart();
});
</script>

@endsection
