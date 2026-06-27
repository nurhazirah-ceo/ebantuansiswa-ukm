<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Login Pelajar | eBantuanSiswa UKM</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

<!-- BACKGROUND -->
<div class="relative min-h-screen flex items-center justify-center px-4"
     style="background-image: url('{{ asset('image/branding/ukm.jpg') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;">

    <!-- OVERLAY -->
    <div class="absolute inset-0 bg-black/30"></div>

    <!-- KAD PUTIH -->
    <div class="relative z-10 bg-white max-w-md
                rounded-2xl shadow-xl px-10 py-8 text-center">

        <!-- TAJUK -->
        <h1 class="text-xl font-semibold text-gray-800">
            Login Pelajar
        </h1>

        <p class="text-sm text-gray-500 mt-2">
            Sila log masuk menggunakan nombor matrik
        </p>

        <!-- ERROR -->
        @if ($errors->any())
            <div class="mt-4 bg-red-100 text-red-700 p-3 rounded text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- FORM -->
        <form method="POST" action="{{ route('login') }}" class="mt-6">
            @csrf

            <!-- MATRIK -->
            <div class="text-left">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nombor Matrik
                </label>
                <input
                    type="text"
                    name="matrik"
                    value="{{ old('matrik') }}"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2
                           focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    required
                >
            </div>

            <!-- PASSWORD -->
            <div class="mt-4 text-left">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Kata Laluan
                </label>
                <div class="relative" data-password-field>
                    <input
                        type="password"
                        name="password"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 pr-11
                               focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        required
                    >
                    <x-password-toggle />
                </div>
            </div>

            <!-- BUTTON -->
            <button
                type="submit"
                class="w-full mt-6 py-3 bg-slate-800 text-black
                       rounded-lg font-semibold
                       hover:bg-slate-900 transition">
                Log Masuk
            </button>
        </form>

        <!-- LINK -->
        <p class="text-sm text-gray-500 mt-6">
            Bukan pelajar?
            <a href="{{ route('login') }}"
               class="text-indigo-600 font-medium hover:underline">
                Pilih peranan lain
            </a>
        </p>

    </div>
</div>

</body>
</html>
