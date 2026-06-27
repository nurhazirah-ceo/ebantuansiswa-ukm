@extends('layouts.app')

@section('content')

<div class="max-w-7xl mx-auto px-8 py-8">

    <x-page-hero
        class="mb-5"
        eyebrow="Bantuan"
        title="Alatan Sukan"
        description="Senarai bantuan peralatan sukan untuk disumbangkan."
    />

    <!-- Back -->
    <div class="mb-8">
        <a href="{{ route('penderma.sumbangan') }}"
           aria-label="Kembali"
           class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#3155E7] text-2xl font-black leading-none text-white shadow-md transition hover:bg-[#2647D6]">
            &larr;
        </a>
    </div>

    <!-- GRID -->
    @include('penderma.sumbangan.partials.item-grid')
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function getCart() {
        return JSON.parse(localStorage.getItem('cart')) || [];
    }

    function saveCart(cart) {
        localStorage.setItem('cart', JSON.stringify(cart));
        window.dispatchEvent(new CustomEvent('cart-updated', {
            detail: cart
        }));
    }

    function updateAddToCartButtons() {
        const cart = getCart();

        document.querySelectorAll('.add-to-cart').forEach(btn => {
            const exists = cart.find(item => item.id == btn.dataset.id);
            btn.textContent = exists ? 'Ubah Dalam Senarai' : 'Tambah ke Senarai';
        });
    }

    document.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.addEventListener('click', function () {
            let id = this.dataset.id;
            let name = this.dataset.name;
            let price = this.dataset.price;
            let category = this.dataset.category;
            let img = this.dataset.img;

            let cart = getCart();
            let existing = cart.find(item => item.id == id);

            if (existing) {
                existing.qty++;
                existing.category = existing.category || category;
            } else {
                cart.push({ id, name, price, category, img, qty: 1 });
            }

            saveCart(cart);

            if (typeof renderCart === 'function') {
                renderCart();
            }

            updateAddToCartButtons();

            let image = this.closest('.bg-white').querySelector('.item-image');
            let cartIcon = document.getElementById('cartBtn');

            if (image && cartIcon) {
                let clone = image.cloneNode(true);
                clone.classList.add('fixed', 'z-50');
                document.body.appendChild(clone);

                let rect = image.getBoundingClientRect();
                clone.style.left = rect.left + 'px';
                clone.style.top = rect.top + 'px';
                clone.style.width = '80px';

                let cartRect = cartIcon.getBoundingClientRect();

                clone.animate([
                    { transform: 'translate(0,0) scale(1)', opacity: 1 },
                    { transform: `translate(${cartRect.left - rect.left}px, ${cartRect.top - rect.top}px) scale(0.2)`, opacity: 0.2 }
                ], {
                    duration: 600,
                    easing: 'ease-in-out'
                });

                setTimeout(() => clone.remove(), 600);
            }

            this.classList.add('scale-110');
            setTimeout(() => {
                this.classList.remove('scale-110');
            }, 150);
        });
    });

    updateAddToCartButtons();
    window.addEventListener('cart-updated', updateAddToCartButtons);
});
</script>

@endsection
