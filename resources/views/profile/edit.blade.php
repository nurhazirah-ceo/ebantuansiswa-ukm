@extends('layouts.app')

@section('content')

<div class="bg-slate-50 py-8 min-h-screen">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <div class="mb-8 rounded-2xl bg-gradient-to-r from-[#071633] to-[#102544] p-7 text-white shadow-xl">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-200">
                Tetapan Akaun
            </p>
            <h1 class="mt-2 text-2xl font-semibold">
                Profil Pengguna
            </h1>
            <p class="mt-2 max-w-2xl text-sm leading-relaxed text-slate-300">
                Kemas kini gambar profil, maklumat akaun dan alamat e-mel anda.
            </p>
        </div>

        <div class="max-w-4xl space-y-6">
            @include('profile.partials.update-profile-information-form')
        </div>

    </div>
</div>

@endsection
