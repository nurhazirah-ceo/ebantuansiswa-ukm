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
    @include('layouts.partials.sweet-alert')
</head>

<body
    class="w-screen h-screen overflow-hidden
           font-sans text-gray-900 antialiased">

    {{ $slot }}

    @unless (request()->routeIs('login') || request()->is('login'))
        @include('components.chatbot')
    @endunless

</body>
</html>
