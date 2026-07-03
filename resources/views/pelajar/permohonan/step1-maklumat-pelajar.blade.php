<div id="step-1">
    @php
        $studentProfile = auth()->user();
        $profileFaculty = $studentProfile?->fakulti;
    @endphp

    <div class="bg-white border border-slate-200 rounded-3xl shadow-lg overflow-hidden">

        <!-- Header -->
        <div class="bg-[#071633] px-8 py-5 text-white">
            <h2 class="text-lg font-semibold tracking-tight">
                Maklumat Pelajar
            </h2>

            <p class="text-sm text-slate-300 mt-1">
                Sila lengkapkan maklumat peribadi pelajar.
            </p>
        </div>

        <!-- Form Body -->
        <div class="p-8">
            @if(blank($profileFaculty))
                <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
                    <p class="font-semibold">Fakulti belum dilengkapkan dalam profil pelajar.</p>
                    <p class="mt-1">
                        Sila kemas kini fakulti di
                        <a href="{{ route('profile.edit') }}#maklumat-profil" class="font-semibold underline">
                            Maklumat Profil
                        </a>
                        sebelum menghantar permohonan.
                    </p>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <!-- Nama -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Nama Penuh
                    </label>

                    <input
                        type="text"
                        name="nama_penuh"
                        value="{{ old('nama_penuh', auth()->user()->name ?? '') }}"
                        required
                        class="w-full h-14 px-5 border border-slate-300 bg-white rounded-2xl
                               focus:outline-none focus:ring-2 focus:ring-blue-400
                               focus:border-blue-400 text-[15px] text-slate-800
                               shadow-sm transition duration-200"
                    >
                </div>

            <!-- No Matrik -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">
                    No Matrik
                </label>

                @php
                    $emailPrefix = explode('@', auth()->user()->email ?? '')[0] ?? '';
                    $autoMatrik = strtoupper($emailPrefix);
                @endphp

                <input
                    type="text"
                    name="no_matrik"
                    value="{{ old('no_matrik', auth()->user()->matrik ?? $autoMatrik) }}"
                    required
                    maxlength="7"
                    pattern="[Aa][0-9]{6}"
                    oninput="
                        this.value = this.value
                            .replace(/[^a-zA-Z0-9]/g, '')
                            .toUpperCase();

                        if(this.value.length > 0 && this.value[0] !== 'A'){
                            this.value = 'A';
                        }

                        this.value = this.value.slice(0,7);
                    "
                    placeholder="Contoh: A208972"
                    class="w-full h-14 px-5 border border-slate-300 bg-white rounded-2xl
                        focus:outline-none focus:ring-2 focus:ring-blue-400
                        focus:border-blue-400 text-[15px] text-slate-800
                        shadow-sm transition duration-200"
                >
            </div>
                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Email UKM
                    </label>

                    <input
                        type="email"
                        name="email_ukm"
                        value="{{ old('email_ukm', auth()->user()->email ?? '') }}"
                        required
                        class="w-full h-14 px-5 border border-slate-300 bg-white rounded-2xl
                               focus:outline-none focus:ring-2 focus:ring-blue-400
                               focus:border-blue-400 text-[15px] text-slate-800
                               shadow-sm transition duration-200"
                    >
                </div>

                <!-- No Telefon -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        No Telefon
                    </label>

                    <input
                        type="text"
                        name="no_telefon"
                        value="{{ old('no_telefon') }}"
                        required
                        inputmode="numeric"
                        minlength="10"
                        maxlength="11"
                        pattern="01[0-9]{8,9}"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11)"
                        placeholder="Contoh: 0123456789"
                        class="w-full h-14 px-5 border border-slate-300 bg-white rounded-2xl
                            focus:outline-none focus:ring-2 focus:ring-blue-400
                            focus:border-blue-400 text-[15px] text-slate-800
                            shadow-sm transition duration-200"
                    >
                </div>

                <!-- Fakulti -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Fakulti
                    </label>

                    <input
                        type="text"
                        name="fakulti"
                        id="fakulti"
                        value="{{ $profileFaculty }}"
                        required
                        readonly
                        data-profile-required-message="Sila kemas kini fakulti dalam profil pelajar sebelum meneruskan permohonan."
                        class="w-full h-14 px-5 border border-slate-300 bg-slate-100 rounded-2xl
                               focus:outline-none focus:ring-2 focus:ring-blue-400
                               focus:border-blue-400 text-[15px] text-slate-800
                               shadow-sm transition duration-200"
                    >

                    @error('fakulti')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Tahun Pengajian -->
                <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">
                    Tahun Pengajian
                </label>

                <select
                    name="tahun_pengajian"
                    required
                    class="w-full h-14 px-5 border border-slate-300 bg-white rounded-2xl
                        focus:outline-none focus:ring-2 focus:ring-blue-400
                        focus:border-blue-400 text-[15px] text-slate-800
                        shadow-sm transition duration-200"
                >
                    <option value="">-- Pilih Tahun --</option>

                    <option value="Tahun 1" {{ old('tahun_pengajian') == 'Tahun 1' ? 'selected' : '' }}>
                        Tahun 1
                    </option>

                    <option value="Tahun 2" {{ old('tahun_pengajian') == 'Tahun 2' ? 'selected' : '' }}>
                        Tahun 2
                    </option>

                    <option value="Tahun 3" {{ old('tahun_pengajian') == 'Tahun 3' ? 'selected' : '' }}>
                        Tahun 3
                    </option>

                    <option value="Tahun 4" {{ old('tahun_pengajian') == 'Tahun 4' ? 'selected' : '' }}>
                        Tahun 4
                    </option>
                </select>

                @error('tahun_pengajian')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>
            </div>

        </div>
    </div>

</div>
