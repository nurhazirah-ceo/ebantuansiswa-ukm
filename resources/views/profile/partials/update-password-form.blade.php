<section id="tukar-kata-laluan" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-lg">
    <header>
        <h2 class="text-xl font-semibold text-slate-900">
            Kemaskini Kata Laluan
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Pastikan akaun menggunakan kata laluan yang selamat.
        </p>
    </header>

    <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('PUT')

        <div class="rounded-2xl bg-slate-50 p-5">
            <div class="flex items-start gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#071633] text-white">
                    &#128274;
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Tip keselamatan</h3>
                    <p class="mt-1 text-sm leading-relaxed text-slate-500">
                        Gunakan gabungan huruf besar, huruf kecil, nombor dan simbol.
                    </p>
                </div>
            </div>
        </div>

        <div>
            <x-input-label for="current_password" value="Kata Laluan Semasa" />
            <div class="relative mt-1" data-password-field>
                <x-text-input id="current_password" type="password"
                    name="current_password"
                    class="block w-full rounded-lg pr-11 text-sm"
                    required />
                <x-password-toggle />
            </div>
            <x-input-error :messages="$errors->updatePassword->get('current_password')" />
        </div>

        <div>
            <x-input-label for="password" value="Kata Laluan Baharu" />
            <div class="relative mt-1" data-password-field>
                <x-text-input id="password" type="password"
                    name="password"
                    class="block w-full rounded-lg pr-11 text-sm"
                    required />
                <x-password-toggle />
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password')" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Sahkan Kata Laluan" />
            <div class="relative mt-1" data-password-field>
                <x-text-input id="password_confirmation" type="password"
                    name="password_confirmation"
                    class="block w-full rounded-lg pr-11 text-sm"
                    required />
                <x-password-toggle />
            </div>
        </div>

        <div class="pt-2">
            <x-primary-button class="px-7 py-3">
                Simpan Kata Laluan
            </x-primary-button>
        </div>
    </form>

    @if (session('status') === 'password-updated')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'success',
                    title: 'Berjaya!',
                    text: 'Kata laluan berjaya dikemas kini.',
                    confirmButtonColor: '#2563eb',
                    confirmButtonText: 'OK'
                });
            });
        </script>
    @endif
</section>
