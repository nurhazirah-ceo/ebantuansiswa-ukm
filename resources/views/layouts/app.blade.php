<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'eBantuanSiswa UKM') }}</title>

    <!-- POPPINS FONT -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
          rel="stylesheet">

    <!-- VITE -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-[Poppins] antialiased bg-gray-100">

<div class="min-h-screen">

    {{-- NAVBAR --}}
    @include('layouts.navigation')

    {{-- ================= CART (PENDERMA SAHAJA) ================= --}}
    @if(auth()->check() && auth()->user()->role === 'penderma')

        <!-- OVERLAY -->
        <div id="cartOverlay"
             class="fixed inset-0 bg-black/40 hidden z-40">
        </div>

        <!-- CART PANEL -->
        <div id="cartPanel"
             class="fixed top-0 right-0 h-full w-96 bg-white shadow-2xl transform translate-x-full transition-all duration-300 ease-in-out z-50 flex flex-col">

            <!-- HEADER -->
            <div class="p-5 border-b flex justify-between items-center">

                <h2 class="font-semibold text-lg text-[#0B1F3A]">
                    Senarai Sumbangan
                </h2>

                <button onclick="toggleCart()"
                        class="text-gray-500 hover:text-black text-xl">
                    ✕
                </button>

            </div>

            <!-- ITEMS -->
            <div id="cartItems"
                 class="flex-1 overflow-y-auto p-5 space-y-4">
            </div>

            <!-- FOOTER -->
            <div class="p-5 border-t">

                <div class="flex justify-between font-semibold mb-4">
                    <span>Total</span>
                    <span id="cartTotal">RM 0.00</span>
                </div>

                <button onclick="goToCheckout()"
                        class="w-full bg-[#0B1F3A] text-white py-3 rounded-xl hover:opacity-90 transition">
                    Teruskan
                </button>

            </div>

        </div>

    @endif
    {{-- ================= END CART ================= --}}

    <!-- PAGE -->
    <main class="{{ auth()->check() && auth()->user()->role === 'admin' ? 'main-content admin-main-content' : '' }}">
        @isset($slot)
            {{ $slot }}
        @endisset

        @yield('content')
    </main>

</div>

<!-- SWEETALERT -->
 
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@php
    $sweetAlert = null;
    $sweetAlertRedirectUrl = session('sweet_alert_redirect_url');

    foreach ([
        'success' => ['icon' => 'success', 'title' => 'Berjaya'],
        'error' => ['icon' => 'error', 'title' => 'Ralat'],
        'warning' => ['icon' => 'warning', 'title' => 'Perhatian'],
        'info' => ['icon' => 'info', 'title' => 'Makluman'],
    ] as $key => $meta) {
        if (session($key)) {
            $sweetAlert = [
                'icon' => $meta['icon'],
                'title' => $meta['title'],
                'text' => session($key),
                'confirmButtonText' => 'OK',
                'confirmButtonColor' => '#071633',
            ];
            break;
        }
    }

    if (! $sweetAlert && session('status') === 'verification-link-sent') {
        $sweetAlert = [
            'icon' => 'success',
            'title' => 'Emel dihantar',
            'text' => 'Pautan pengesahan baharu telah dihantar ke emel anda.',
            'confirmButtonText' => 'OK',
            'confirmButtonColor' => '#071633',
        ];
    }

    if (! $sweetAlert && $errors->any()) {
        $sweetAlert = [
            'icon' => 'error',
            'title' => 'Maklumat tidak lengkap',
            'text' => $errors->first().($errors->count() > 1 ? ' Sila semak semua mesej ralat pada borang.' : ''),
            'confirmButtonText' => 'OK',
            'confirmButtonColor' => '#071633',
        ];
    }
@endphp

@if($sweetAlert)
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire(@json($sweetAlert)).then(function (result) {
        const redirectUrl = @json($sweetAlertRedirectUrl);

        if (result.isConfirmed && redirectUrl) {
            window.location.href = redirectUrl;
        }
    });
});
</script>
@endif

<!-- ================= CART SCRIPT ================= -->
<script>

// ================= BASIC =================
function getCart() {
    return JSON.parse(localStorage.getItem('cart')) || [];
}

function saveCart(cart) {
    localStorage.setItem('cart', JSON.stringify(cart));

    window.dispatchEvent(new CustomEvent('cart-updated', {
        detail: cart
    }));
}

// ================= UI =================
function toggleCart() {

    let panel = document.getElementById('cartPanel');
    let overlay = document.getElementById('cartOverlay');

    panel?.classList.toggle('translate-x-full');
    overlay?.classList.toggle('hidden');
}

// OPEN CART
document.getElementById('cartBtn')
    ?.addEventListener('click', toggleCart);

// CLOSE BY OVERLAY
document.getElementById('cartOverlay')
    ?.addEventListener('click', toggleCart);

// ================= COUNT =================
function updateCartCount() {

    let cart = getCart();

    let count = cart.reduce((sum, item) => sum + item.qty, 0);

    let el = document.getElementById('cartCount');

    if (el) {
        el.innerText = count;
    }
}

// ================= RENDER =================
function renderCart() {

    let cart = JSON.parse(localStorage.getItem('cart')) || [];

    let container = document.getElementById('cartItems');

    let total = 0;

    if (!container) return;

    container.innerHTML = '';

    // EMPTY
    if (cart.length === 0) {

        container.innerHTML = `
            <div class="text-center text-gray-400 py-10">
                🛒 Tiada item dalam senarai
            </div>
        `;

        document.getElementById('cartTotal').innerText = 'RM 0.00';

        updateCartCount();

        return;
    }

    // ITEMS
    cart.forEach(item => {

        total += item.price * item.qty;

        container.innerHTML += `
        <div class="flex gap-3 border-b pb-4">

            <img src="${item.img}" 
                 class="w-14 h-14 object-cover rounded-xl border">

            <div class="flex-1">

                <div class="font-medium text-sm text-slate-800">
                    ${item.name}
                </div>

                <div class="flex items-center gap-2 mt-2">

                    <button onclick="changeQty(${item.id}, 'dec')" 
                        class="w-6 h-6 bg-gray-200 rounded hover:bg-gray-300">
                        -
                    </button>

                    <span class="text-sm">
                        ${item.qty}
                    </span>

                    <button onclick="changeQty(${item.id}, 'inc')" 
                        class="w-6 h-6 bg-gray-200 rounded hover:bg-gray-300">
                        +
                    </button>

                </div>

                <div class="text-xs text-gray-500 mt-1">
                    RM ${item.price} / unit
                </div>

            </div>

            <div class="text-right">

                <div class="font-semibold text-sm text-slate-800">
                    RM ${(item.price * item.qty).toFixed(2)}
                </div>

                <button onclick="removeItem(${item.id})"
                    class="text-red-500 text-xs mt-1 hover:underline">
                    Buang
                </button>

            </div>

        </div>
        `;
    });

    document.getElementById('cartTotal').innerText =
        'RM ' + total.toFixed(2);

    updateCartCount();
}

// ================= ACTION =================
function changeQty(id, action) {

    let cart = getCart();

    let item = cart.find(i => i.id == id);

    if (!item) return;

    if (action === 'inc') item.qty++;

    if (action === 'dec') item.qty--;

    if (item.qty <= 0) {
        cart = cart.filter(i => i.id != id);
    }

    saveCart(cart);

    renderCart();
}

function removeItem(id) {

    let cart = getCart().filter(item => item.id != id);

    saveCart(cart);

    renderCart();
}

function goToCheckout() {

    let cart = getCart();

    if (cart.length === 0) {

        Swal.fire({
            icon: 'warning',
            title: 'Senarai kosong',
            text: 'Tambah sekurang-kurangnya satu item sebelum teruskan.'
        });

        return;
    }

    window.location.href = "{{ route('penderma.menyumbang-bantuan') }}";
}

// ================= INIT =================
document.addEventListener('DOMContentLoaded', function () {
    renderCart();
});

</script>

<script>
document.addEventListener('submit', function (event) {
    const form = event.target.closest('form[data-confirm]');

    if (!form || form.dataset.confirmed === 'true') {
        if (form) {
            delete form.dataset.confirmed;
        }
        return;
    }

    event.preventDefault();

    Swal.fire({
        icon: form.dataset.confirmIcon || 'question',
        title: form.dataset.confirmTitle || 'Sahkan tindakan?',
        text: form.dataset.confirmText || 'Pastikan maklumat adalah betul sebelum diteruskan.',
        showCancelButton: true,
        confirmButtonText: form.dataset.confirmButton || 'Ya, teruskan',
        cancelButtonText: form.dataset.cancelButton || 'Batal',
        confirmButtonColor: form.dataset.confirmColor || '#1D4ED8',
        cancelButtonColor: '#64748b'
    }).then(function (result) {
        if (result.isConfirmed) {
            form.dataset.confirmed = 'true';
            form.requestSubmit(event.submitter || undefined);
        }
    });
});
</script>


</body>
</html>
