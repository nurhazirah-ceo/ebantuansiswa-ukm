@extends('layouts.app')

@section('content')

<div class="bg-slate-50 py-8 min-h-screen">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <div class="mb-8 rounded-2xl bg-gradient-to-r from-[#071633] to-[#102544] p-7 text-white shadow-xl">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-200">
                Tetapan Akaun
            </p>
            <h1 class="mt-2 text-2xl font-semibold">
                Tukar Kata Laluan
            </h1>
            <p class="mt-2 max-w-2xl text-sm leading-relaxed text-slate-300">
                Kemas kini kata laluan akaun anda untuk keselamatan.
            </p>
        </div>

        <div class="max-w-4xl space-y-6">
            @include('profile.partials.update-password-form')
        </div>

    </div>
</div>

@endsection
