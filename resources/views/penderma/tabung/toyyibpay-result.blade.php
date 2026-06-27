@extends('layouts.app')

@section('content')

<div class="min-h-screen bg-[linear-gradient(180deg,#f7fbff_0%,#eef4fb_48%,#f8fbff_100%)] px-6 py-12">
    <div class="mx-auto max-w-lg rounded-[2rem] border border-slate-200 bg-white p-8 text-center shadow-[0_18px_45px_rgba(15,23,42,0.08)]">
        <p class="text-xs font-bold uppercase tracking-[0.25em] text-blue-700">
            ToyyibPay
        </p>
        <h1 class="mt-3 text-2xl font-semibold tracking-tight text-slate-950">
            Mengemas kini status pembayaran
        </h1>
        <p class="mt-3 text-sm leading-6 text-slate-500">
            Sila sahkan mesej pembayaran untuk melihat status sumbangan tabung anda.
        </p>

        <a href="{{ $redirectUrl }}"
           class="mt-6 inline-flex items-center justify-center rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700">
            Lihat Status Pembayaran
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: @json($alertIcon),
        title: @json($alertTitle),
        text: @json($alertMessage),
        confirmButtonText: 'OK',
        confirmButtonColor: '#1D4ED8',
        allowOutsideClick: false,
        allowEscapeKey: false,
        background: '#ffffff',
        color: '#0f172a',
        customClass: {
            popup: 'rounded-3xl shadow-xl',
            title: 'text-2xl font-semibold text-slate-900',
            confirmButton: 'rounded-2xl px-7 py-3 font-semibold shadow'
        }
    }).then(function () {
        window.location.href = @json($redirectUrl);
    });
});
</script>

@endsection
