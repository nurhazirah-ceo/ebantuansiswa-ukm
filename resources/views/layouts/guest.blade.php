<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'eBantuanSiswa UKM') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="w-screen h-screen overflow-hidden
           font-sans text-gray-900 antialiased">

    {{ $slot }}

    @unless (request()->routeIs('login') || request()->is('login'))
        @include('components.chatbot')
    @endunless

    
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@php
    $sweetAlert = null;

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

    if (! $sweetAlert && isset($errors) && $errors->any()) {
        $sweetAlert = [
            'icon' => 'error',
            'title' => 'Maklumat tidak lengkap',
            'text' => $errors->first().($errors->count() > 1 ? ' Sila semak semua mesej ralat pada borang.' : ''),
            'confirmButtonText' => 'OK',
            'confirmButtonColor' => '#071633',
        ];
    }
@endphp

@if ($sweetAlert)
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: @json($sweetAlert['icon']),
        title: @json($sweetAlert['title']),
        text: @json($sweetAlert['text']),
        confirmButtonText: @json($sweetAlert['confirmButtonText']),
        confirmButtonColor: @json($sweetAlert['confirmButtonColor'])
    });
});
</script>
@endif

</body>
</html>
