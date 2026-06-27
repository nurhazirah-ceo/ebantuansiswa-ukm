<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>eBantuanSiswa UKM</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

<!-- BACKGROUND -->
<div class="relative min-h-screen flex items-center justify-center"
     style="background-image: url('{{ asset('image/branding/ukm.jpg') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;">

    <!-- OVERLAY -->
    <div class="absolute inset-0 bg-black/20"></div>

    <!-- WRAPPER UNTUK CENTER -->
    <div class="relative z-10 flex justify-center w-full px-4">

        <!-- ✅ KAD PUTIH (BETUL) -->
         <div class="bg-white w-full max-w-md
                rounded-2xl shadow-xl
                px-15 py-18
                text-center">

            <h1 class="text-lg font-semibold text-black-800">
                eBantuanSiswa UKM
            </h1>

            <p class="text-sm text-black-500 mt-1">
                Sila pilih peranan anda untuk log masuk
            </p>

            <!-- BUTTON -->
            <div class="mt-8 space-y-4">

                <a href="{{ route('login') }}"
                   class="block w-full bg-slate-800 text-black
                          py-3 rounded-lg font-medium
                          hover:bg-slate-900 transition">
                    Pelajar
                </a>

                <a href="{{ route('login') }}"
                   class="block w-full bg-slate-800 text-black
                          py-3 rounded-lg font-medium
                          hover:bg-slate-900 transition">
                    Penderma
                </a>

                <a href="{{ route('login') }}"
                   class="block w-full bg-slate-800 text-black
                          py-3 rounded-lg font-medium
                          hover:bg-slate-900 transition">
                    Pentadbir
                </a>

            </div>

            <p class="mt-6 text-sm text-gray-500">
                Belum mempunyai akaun?
                <a href="{{ route('register') }}"
                   class="text-indigo-600 font-medium hover:underline">
                    Daftar di sini
                </a>
            </p>

        </div>
    </div>
</div>

</body>
</html>
