<section id="maklumat-profil" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-lg">
    <header>
        <h2 class="text-xl font-semibold text-slate-900">
            Maklumat Profil
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Kemas kini gambar profil, maklumat akaun dan alamat e-mel.
        </p>
    </header>

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('PATCH')

        @php
            $photoUrl = $user->profile_photo_path
                ? asset('storage/' . $user->profile_photo_path)
                : null;
        @endphp

        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center">

                <div class="relative mx-auto sm:mx-0">
                    <div id="profile_preview_wrapper">
                        @if($photoUrl)
                            <img
                                id="profile_photo_preview"
                                src="{{ $photoUrl }}"
                                alt="Gambar profil {{ $user->name }}"
                                class="h-28 w-28 rounded-full border-4 border-white object-cover shadow-lg"
                            >
                        @else
                            <div
                                id="profile_initial_preview"
                                class="flex h-28 w-28 items-center justify-center rounded-full border-4 border-white bg-gradient-to-br from-blue-500 to-cyan-400 text-3xl font-bold text-white shadow-lg"
                            >
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>

                            <img
                                id="profile_photo_preview"
                                src=""
                                alt="Preview gambar profil"
                                class="hidden h-28 w-28 rounded-full border-4 border-white object-cover shadow-lg"
                            >
                        @endif
                    </div>

                    <span class="absolute bottom-1 right-1 flex h-8 w-8 items-center justify-center rounded-full bg-[#071633] text-xs font-bold text-white shadow">
                        +
                    </span>
                </div>

                <div class="flex-1 text-center sm:text-left">
                    <h3 class="text-base font-semibold text-slate-900">
                        Gambar Profil
                    </h3>

                    <p class="mt-1 text-sm leading-relaxed text-slate-500">
                        Muat naik gambar JPG, PNG atau WEBP. Saiz maksimum 2MB.
                    </p>

                    <label
                        for="profile_photo"
                        class="mt-4 inline-flex cursor-pointer items-center justify-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-blue-700 transition"
                    >
                        Pilih Gambar
                    </label>

                    <input
                        id="profile_photo"
                        name="profile_photo"
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        class="sr-only"
                    >

                    <p id="selected_file_name" class="mt-2 text-sm text-slate-500">
                        Tiada gambar dipilih.
                    </p>

                    <x-input-error class="mt-2" :messages="$errors->get('profile_photo')" />
                </div>
            </div>
        </div>

        <div>
            <x-input-label for="name" value="Nama" />
            <x-text-input
                id="name"
                name="name"
                type="text"
                class="mt-1 block w-full rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500"
                :value="old('name', $user->name)"
                required
                autofocus
            />
            <x-input-error class="mt-1" :messages="$errors->get('name')" />
        </div>

        @if($user->role === 'pelajar')
            <div>
                <x-input-label for="matrik" value="No Matrik" />
                <x-text-input
                    id="matrik"
                    name="matrik"
                    type="text"
                    class="mt-1 block w-full rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500"
                    :value="old('matrik', $user->matrik)"
                    required
                />
                <x-input-error class="mt-1" :messages="$errors->get('matrik')" />
            </div>

            <div>
                <x-input-label for="fakulti" value="Fakulti" />
                <select
                    id="fakulti"
                    name="fakulti"
                    required
                    class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
                    <option value="">-- Pilih Fakulti --</option>
                    @foreach(\App\Support\StudentAcademicProfile::faculties() as $fakulti)
                        <option value="{{ $fakulti }}" @selected(old('fakulti', $user->fakulti) === $fakulti)>
                            {{ $fakulti }}
                        </option>
                    @endforeach
                </select>
                <x-input-error class="mt-1" :messages="$errors->get('fakulti')" />
            </div>
        @endif

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input
                id="email"
                name="email"
                type="email"
                class="mt-1 block w-full rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500"
                :value="old('email', $user->email)"
                required
            />
            <x-input-error class="mt-1" :messages="$errors->get('email')" />
        </div>


        <div class="pt-2">
            <x-primary-button class="px-7 py-3">
                Simpan Profil
            </x-primary-button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('profile_photo');
            const imagePreview = document.getElementById('profile_photo_preview');
            const initialPreview = document.getElementById('profile_initial_preview');
            const fileName = document.getElementById('selected_file_name');

            if (!input || !imagePreview || !fileName) return;

            input.addEventListener('change', function () {
                const file = this.files[0];

                if (!file) {
                    fileName.textContent = 'Tiada gambar dipilih.';
                    return;
                }

                fileName.textContent = file.name;

                const reader = new FileReader();

                reader.onload = function (e) {
                    imagePreview.src = e.target.result;
                    imagePreview.classList.remove('hidden');

                    if (initialPreview) {
                        initialPreview.classList.add('hidden');
                    }
                };

                reader.readAsDataURL(file);
            });
        });
    </script>

@if (session('status') === 'profile-updated')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            icon: 'success',
            title: 'Berjaya!',
            text: 'Profil berjaya dikemas kini.',
            confirmButtonColor: '#2563eb',
            confirmButtonText: 'OK'
        });
    });
</script>
@endif
</section>
