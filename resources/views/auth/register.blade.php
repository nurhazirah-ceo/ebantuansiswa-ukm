<x-guest-layout>
    <div class="grid grid-cols-1 lg:grid-cols-[3fr_2fr] h-screen">

        {{-- LEFT: IMAGE (SAMA DENGAN LOGIN) --}}
        <div class="hidden lg:block relative h-screen overflow-hidden">
            <img
                src="{{ asset('image/branding/ukm.jpg') }}"
                alt="UKM"
                class="absolute inset-0 w-full h-full object-cover object-center"
            />

            {{-- Overlay gradient --}}
            <div class="absolute inset-0 bg-gradient-to-t
                        from-black/40 via-black/20 to-transparent"></div>

            {{-- Branding --}}
            <div class="absolute bottom-10 left-10 text-white">
                <h2 class="text-2xl font-semibold">
                    eBantuanSiswa UKM
                </h2>
                <p class="text-sm opacity-90 mt-1">
                    Sistem Bantuan Barangan Pelajar
                </p>
            </div>
        </div>

        {{-- RIGHT: REGISTER FORM --}}
        <div class="relative z-10 flex items-center justify-center h-screen bg-white px-8">

            <div class="w-full max-w-lg bg-white p-8 rounded-2xl
                        shadow-xl shadow-slate-200/70">

                {{-- Header --}}
                <div class="mb-10 text-center">
                    <h1 class="text-2xl font-semibold text-slate-800">
                        Daftar Akaun
                    </h1>
                    <p class="text-sm text-slate-500 mt-1">
                        eBantuanSiswa UKM
                    </p>
                </div>

                <form method="POST" action="{{ route('register') }}" id="pelajarRegistrationForm" class="space-y-5">
                    @csrf

                    {{-- Nama --}}
                    <div>
                        <x-input-label for="name" value="Nama"
                                       class="text-sm font-medium text-slate-700 mb-1" />
                        <x-text-input
                            id="name"
                            class="w-full rounded-lg border border-slate-300
                                   px-4 py-2.5 text-sm
                                   focus:border-slate-800 focus:ring-1 focus:ring-slate-800
                                   transition"
                            type="text"
                            name="name"
                            :value="old('name')"
                            required
                            autofocus
                        />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    {{-- Nombor Matrik --}}
                    <div>
                        <x-input-label for="matrik" value="Nombor Matrik"
                                       class="text-sm font-medium text-slate-700 mb-1" />
                        <x-text-input
                            id="matrik"
                            class="w-full rounded-lg border border-slate-300
                                   px-4 py-2.5 text-sm
                                   focus:border-slate-800 focus:ring-1 focus:ring-slate-800
                                   transition"
                            type="text"
                            name="matrik"
                            :value="old('matrik')"
                            maxlength="7"
                            autocomplete="off"
                            required
                        />
                        <p id="matrikRealtimeError" class="mt-2 hidden text-sm text-red-600">
                            Mesti bermula dengan huruf A dan diikuti 6 digit
                        </p>
                        <x-input-error :messages="$errors->get('matrik')" class="mt-2" />
                    </div>

                    {{-- Email --}}
                    <div>
                        <x-input-label for="email" value="Emel"
                                       class="text-sm font-medium text-slate-700 mb-1" />
                        <x-text-input
                            id="email"
                            class="w-full rounded-lg border border-slate-300
                                   px-4 py-2.5 text-sm
                                   focus:border-slate-800 focus:ring-1 focus:ring-slate-800
                                   transition"
                            type="email"
                            name="email"
                            :value="old('email')"
                            required
                            autocomplete="username"
                        />
                        <p id="emailRealtimeError" class="mt-2 hidden text-sm text-red-600">
                            Sila gunakan email rasmi siswa (@siswa.ukm.edu.my)
                        </p>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    {{-- Kata Laluan --}}
                    <div>
                        <x-input-label for="password" value="Kata Laluan"
                                       class="text-sm font-medium text-slate-700 mb-1" />
                        <div class="relative" data-password-field>
                            <x-text-input
                                id="password"
                                class="w-full rounded-lg border border-slate-300
                                       px-4 py-2.5 pr-11 text-sm
                                       focus:border-slate-800 focus:ring-1 focus:ring-slate-800
                                       transition"
                                type="password"
                                name="password"
                                required
                                autocomplete="new-password"
                            />
                            <x-password-toggle />
                        </div>
                        <p id="passwordStrengthRealtimeError" class="mt-2 hidden text-sm text-red-600">
                            Kata laluan mesti mempunyai minimum 8 aksara, huruf besar, huruf kecil, nombor dan simbol.
                        </p>
                        <p id="passwordStrengthRealtimeSuccess" class="mt-2 hidden text-sm text-green-600">
                            Kata laluan Diterima
                        </p>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    {{-- Sahkan Kata Laluan --}}
                    <div>
                        <x-input-label for="password_confirmation" value="Sahkan Kata Laluan"
                                       class="text-sm font-medium text-slate-700 mb-1" />
                        <div class="relative" data-password-field>
                            <x-text-input
                                id="password_confirmation"
                                class="w-full rounded-lg border border-slate-300
                                       px-4 py-2.5 pr-11 text-sm
                                       focus:border-slate-800 focus:ring-1 focus:ring-slate-800
                                       transition"
                                type="password"
                                name="password_confirmation"
                                required
                                autocomplete="new-password"
                            />
                            <x-password-toggle />
                        </div>
                        <p id="passwordConfirmationRealtimeError" class="mt-2 hidden text-sm text-red-600">
                            Kata laluan tidak sepadan
                        </p>
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>

                    {{-- Button --}}
                    <x-primary-button
                        id="registerSubmitButton"
                        class="w-full justify-center mt-2
                               rounded-lg bg-slate-800 text-white
                               py-2.5 text-sm font-semibold
                               hover:bg-slate-900
                               focus:ring-2 focus:ring-slate-400
                               active:scale-[0.98]
                               transition">
                        Daftar Akaun
                    </x-primary-button>
                </form>

                {{-- Login link --}}
                <div class="mt-8 text-center text-sm text-slate-600">
                    Sudah ada akaun?
                    <a href="{{ route('login') }}"
                       class="font-medium text-slate-800 hover:underline">
                        Log Masuk
                    </a>
                </div>

            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('pelajarRegistrationForm');
            const submitButton = document.getElementById('registerSubmitButton');
            const nameInput = document.getElementById('name');
            const matrikInput = document.getElementById('matrik');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const passwordConfirmationInput = document.getElementById('password_confirmation');

            const errors = {
                matrik: document.getElementById('matrikRealtimeError'),
                email: document.getElementById('emailRealtimeError'),
                passwordStrength: document.getElementById('passwordStrengthRealtimeError'),
                passwordConfirmation: document.getElementById('passwordConfirmationRealtimeError'),
            };
            const passwordStrengthSuccess = document.getElementById('passwordStrengthRealtimeSuccess');

            const touched = {
                matrik: matrikInput?.value.trim() !== '',
                email: emailInput?.value.trim() !== '',
                password: passwordInput?.value !== '',
                passwordConfirmation: passwordConfirmationInput?.value !== '',
            };

            function setError(input, errorElement, visible) {
                if (!input || !errorElement) {
                    return;
                }

                errorElement.classList.toggle('hidden', !visible);
                input.setAttribute('aria-invalid', visible ? 'true' : 'false');
            }

            function hasValue(input) {
                return input && input.value.trim() !== '';
            }

            function isStrongPassword(value) {
                return value.length >= 8
                    && /[A-Z]/.test(value)
                    && /[a-z]/.test(value)
                    && /[0-9]/.test(value)
                    && /[^A-Za-z0-9\s]/.test(value);
            }

            function validateRegistrationForm() {
                if (!form || !submitButton) {
                    return;
                }

                if (matrikInput) {
                    matrikInput.value = matrikInput.value.toUpperCase();
                }

                const matrikValid = /^A\d{6}$/.test(matrikInput?.value.trim() || '');
                const emailValid = (emailInput?.value.trim() || '').endsWith('@siswa.ukm.edu.my');
                const passwordValid = isStrongPassword(passwordInput?.value || '');
                const confirmationStarted = (passwordConfirmationInput?.value || '') !== '';
                const passwordsMatch = !confirmationStarted || passwordInput?.value === passwordConfirmationInput?.value;

                setError(matrikInput, errors.matrik, touched.matrik && !matrikValid);
                setError(emailInput, errors.email, touched.email && !emailValid);
                setError(passwordInput, errors.passwordStrength, touched.password && !passwordValid);
                passwordStrengthSuccess?.classList.toggle('hidden', !(touched.password && passwordValid));
                setError(
                    passwordConfirmationInput,
                    errors.passwordConfirmation,
                    touched.passwordConfirmation && confirmationStarted && !passwordsMatch
                );

                const requiredFieldsFilled = [
                    nameInput,
                    matrikInput,
                    emailInput,
                    passwordInput,
                    passwordConfirmationInput,
                ].every(hasValue);

                submitButton.disabled = !(requiredFieldsFilled && matrikValid && emailValid && passwordValid && passwordsMatch);
                submitButton.classList.toggle('opacity-50', submitButton.disabled);
                submitButton.classList.toggle('cursor-not-allowed', submitButton.disabled);
            }

            matrikInput?.addEventListener('input', function () {
                touched.matrik = true;
                validateRegistrationForm();
            });

            emailInput?.addEventListener('input', function () {
                touched.email = true;
                validateRegistrationForm();
            });

            passwordInput?.addEventListener('input', function () {
                touched.password = true;
                validateRegistrationForm();
            });

            passwordConfirmationInput?.addEventListener('input', function () {
                touched.passwordConfirmation = true;
                validateRegistrationForm();
            });

            nameInput?.addEventListener('input', validateRegistrationForm);

            validateRegistrationForm();
        });
    </script>
</x-guest-layout>
