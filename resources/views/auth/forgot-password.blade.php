<x-guest-layout>
    <div class="grid grid-cols-1 lg:grid-cols-[3fr_2fr] h-screen">

        {{-- LEFT: IMAGE --}}
        <div class="hidden lg:block relative h-screen overflow-hidden">
            <img
                src="{{ asset('image/branding/ukm.jpg') }}"
                alt="UKM"
                class="absolute inset-0 w-full h-full object-cover object-center"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-black/20 to-transparent"></div>

            <div class="absolute bottom-10 left-10 text-white">
                <h2 class="text-2xl font-semibold">
                    eBantuanSiswa UKM
                </h2>
                <p class="text-sm opacity-90 mt-1">
                    Sistem Bantuan Barangan Pelajar
                </p>
            </div>
        </div>

        {{-- RIGHT: FORGOT PASSWORD --}}
        <div class="flex items-center justify-center h-screen bg-white px-8">
            <div class="w-full max-w-lg bg-white p-8 rounded-2xl shadow-xl">

                <div class="mb-6 text-center">
                    <h1 class="text-2xl font-semibold text-slate-800">
                        Lupa Kata Laluan
                    </h1>
                    <p class="text-sm text-slate-500 mt-1">
                        Masukkan emel berdaftar untuk menetapkan semula kata laluan
                    </p>
                </div>

                @if (session('status'))
                    <div class="mb-4 text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg px-4 py-3">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg px-4 py-3">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Email
                        </label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm
                                   focus:border-slate-800 focus:ring-1 focus:ring-slate-800 transition"
                        >
                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-lg bg-slate-800 text-white py-2.5 text-sm font-semibold
                               hover:bg-slate-900 transition">
                        Hantar Pautan Tetapan Semula
                    </button>
                </form>

                <div class="mt-6 text-center text-sm text-slate-600">
                    <a href="{{ route('login') }}" class="font-medium text-slate-800 hover:underline">
                        ← Kembali ke Log Masuk
                    </a>
                </div>

            </div>
        </div>

    </div>
</x-guest-layout>
