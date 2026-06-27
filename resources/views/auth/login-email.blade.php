<x-guest-layout>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center">
        <div class="w-full max-w-3xl bg-white rounded-xl shadow-lg p-10">

            <h1 class="text-3xl font-bold text-gray-800 text-center">
                Login Sistem
            </h1>

            <p class="text-center text-gray-500 mt-2">
                Sila log masuk menggunakan email
            </p>

            @if ($errors->any())
                <div class="mt-6 bg-red-100 text-red-700 p-4 rounded">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="mt-8">
                @csrf

                <!-- Email -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">
                        Email
                    </label>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="w-full border rounded px-4 py-2 focus:ring focus:ring-green-200"
                        required
                    >
                </div>

                <!-- Password -->
                <div class="mt-6">
                    <label class="block text-gray-700 font-semibold mb-1">
                        Kata Laluan
                    </label>
                    <div class="relative" data-password-field>
                        <input
                            type="password"
                            name="password"
                            class="w-full border rounded px-4 py-2 pr-11 focus:ring focus:ring-green-200"
                            required
                        >
                        <x-password-toggle />
                    </div>
                </div>

                <button
                    type="submit"
                    class="w-full mt-8 py-3 bg-green-600 text-black rounded-lg font-semibold hover:bg-green-700 transition"
                >
                    Log Masuk
                </button>
            </form>

            <p class="text-center text-gray-500 mt-6">
                Bukan penderma / pentadbir?
                <a href="{{ route('login') }}" class="text-indigo-600 underline">
                    Pilih peranan lain
                </a>
            </p>

        </div>
    </div>
</x-guest-layout>
