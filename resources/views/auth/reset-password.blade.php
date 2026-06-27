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

        {{-- RIGHT: RESET PASSWORD --}}
        <div class="relative z-10 flex items-center justify-center h-screen bg-white px-8">

            <div class="w-full max-w-lg bg-white p-8 rounded-2xl
                        shadow-xl shadow-slate-200/70">

                {{-- Header --}}
                <div class="mb-10 text-center">
                    <h1 class="text-2xl font-semibold text-slate-800">
                        Tetapkan Kata Laluan
                    </h1>
                    <p class="text-sm text-slate-500 mt-1">
                        Sila masukkan kata laluan baharu
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
                <form method="POST" action="{{ route('password.store') }}" class="space-y-6">
                    @csrf

                    <input type="hidden" name="token" value="{{ request()->route('token') }}">

                    {{-- Email --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Emel
                        </label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email', request('email')) }}"
                            required
                            readonly
                            class="w-full rounded-lg border border-slate-300
                                   px-4 py-2.5 text-sm bg-slate-100
                                   focus:border-slate-800 focus:ring-1 focus:ring-slate-800
                                   transition"
                        >
                    </div>

                    {{-- Password --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Kata Laluan Baharu
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

                    {{-- Confirm Password --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Sahkan Kata Laluan
                        </label>
                        <div class="relative" data-password-field>
                            <input
                                type="password"
                                name="password_confirmation"
                                required
                                class="w-full rounded-lg border border-slate-300
                                       px-4 py-2.5 pr-11 text-sm
                                       focus:border-slate-800 focus:ring-1 focus:ring-slate-800
                                       transition"
                            >
                            <x-password-toggle />
                        </div>
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
                        Reset Kata Laluan
                    </button>
                </form>

                {{-- Back to login --}}
                <div class="mt-8 text-center text-sm text-slate-600">
                    <a href="{{ route('login') }}"
                       class="font-medium text-slate-800 hover:underline">
                        Kembali ke login
                    </a>
                </div>

            </div>
        </div>

    </div>
</x-guest-layout>
