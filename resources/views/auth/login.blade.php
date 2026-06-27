<x-guest-layout>
    <div class="grid grid-cols-1 lg:grid-cols-[3fr_2fr] h-screen">

        {{-- LEFT: IMAGE --}}
        <div class="hidden lg:block relative h-screen overflow-hidden">
            <img
                src="{{ asset('image/branding/ukm.jpg') }}"
                alt="UKM"
                class="absolute inset-0 w-full h-full object-cover object-center"
            />

            {{-- Overlay gradient --}}
            <div class="absolute inset-0 bg-gradient-to-t
                        from-black/40 via-black/20 to-transparent"></div>

            {{-- Branding text --}}
            <div class="absolute bottom-10 left-10 text-white">
                <h2 class="text-2xl font-semibold">
                    eBantuanSiswa UKM
                </h2>
                <p class="text-sm opacity-90 mt-1">
                    Sistem Bantuan Barangan Pelajar
                </p>
            </div>
        </div>

        {{-- RIGHT: LOGIN --}}
        <div class="relative z-10 flex items-center justify-center h-screen bg-white px-8">

            <div class="w-full max-w-lg bg-white p-8 rounded-2xl
                        shadow-xl shadow-slate-200/70">

                {{-- Header --}}
                <div class="mb-10 text-center">
                    <h1 class="text-2xl font-semibold text-slate-800">
                        Login Sistem
                    </h1>
                    <p class="text-sm text-slate-500 mt-1">
                        eBantuanSiswa UKM
                    </p>
                </div>

                {{-- Error --}}
                @if ($errors->any())
                    <div class="mb-4 rounded-lg bg-red-50 border border-red-200
                                px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                {{-- Form --}}
                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    {{-- ID Pengguna --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                             Emel / Nombor Matrik
                        </label>
                        <input
                            type="text"
                            name="identifier"
                            value="{{ old('identifier') }}"
                            placeholder=""
                            required
                            class="w-full rounded-lg border border-slate-300
                                   px-4 py-2.5 text-sm
                                   focus:border-slate-800 focus:ring-1 focus:ring-slate-800
                                   transition"
                        >
                    </div>

                    {{-- Password --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Kata Laluan
                        </label>
                        <div class="relative" data-password-field>
                            <input
                                type="password"
                                name="password"
                                required
                                class="w-full rounded-lg border border-slate-300
                                       px-4 py-2.5 pr-11 text-sm
                                       focus:border-slate-800 focus:ring-1 focus:ring-slate-800
                                       transition"
                            >
                            <x-password-toggle />
                        </div>
                    </div>

                    {{-- Remember + Forgot --}}
                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center gap-2 text-slate-600">
                            <input
                                type="checkbox"
                                name="remember"
                                class="rounded border-slate-300 text-slate-800
                                       focus:ring-slate-800"
                            >
                            Ingat Kata Laluan 
                        </label>

                        <a href="{{ route('password.request') }}"
                           class="text-slate-700 hover:underline">
                            Lupa kata laluan?
                        </a>
                    </div>

                    {{-- Button --}}
                    <button
                        type="submit"
                        class="w-full mt-2 rounded-lg
                               bg-slate-800 text-white
                               py-2.5 text-sm font-semibold
                               hover:bg-slate-900
                               focus:ring-2 focus:ring-slate-400
                               active:scale-[0.98]
                               transition">
                        Log Masuk
                    </button>
                </form>

                {{-- Register --}}
                <div class="mt-8 text-center text-sm text-slate-600">
                    Belum ada akaun?
                    <a href="{{ route('register') }}"
                       class="font-medium text-slate-800 hover:underline">
                        Daftar akaun baru
                    </a>
                </div>

            </div>
        </div>

    </div>
</x-guest-layout>
