{{-- resources/views/pelajar/bantuan/keperluan-asas.blade.php --}}

@php
    $basicPackages = $basicPackages ?? collect();
    $selectedBasicPackage = old('bantuan_data.pakej_item_id');
@endphp

<div class="bg-white border border-slate-200 rounded-3xl shadow-lg overflow-hidden mt-6">

    <div class="bg-[#071633] px-8 py-6 text-white">
        <h2 class="text-xl font-semibold tracking-tight">
            Permohonan Keperluan Asas
        </h2>
        <p class="text-sm text-slate-300 mt-1">
            Lengkapkan maklumat permohonan bantuan keperluan asas anda.
        </p>
    </div>

    <div class="p-8 space-y-8 bg-slate-50/30">

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-3">
                Pilih Pakej Bantuan
            </label>

            <select
                id="basic_package"
                name="bantuan_data[pakej_item_id]"
                required
                class="w-full h-14 px-5 border border-slate-300 bg-white rounded-2xl text-[15px] shadow-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition"
                onchange="updatePackageLimit()"
            >
                <option value="">-- Pilih Pakej --</option>
                @foreach($basicPackages as $package)
                    @php
                        $packageLimit = \App\Support\AssistanceCatalog::basicPackageLimit($package);
                    @endphp
                    <option
                        value="{{ $package->id }}"
                        data-limit="{{ $packageLimit }}"
                        data-name="{{ $package->nama_item }}"
                        @selected((string) $selectedBasicPackage === (string) $package->id)
                    >
                        {{ $package->nama_item }}
                    </option>
                @endforeach
            </select>

            <input
                type="hidden"
                id="basic_package_name"
                name="bantuan_data[pakej]"
                value="{{ old('bantuan_data.pakej') }}"
            >
        </div>

        <div class="bg-blue-50 border border-blue-100 rounded-2xl px-5 py-4 text-sm text-slate-700">
            Jumlah Ahli Dibenarkan:
            <span id="packageLimit" class="font-semibold text-blue-700">0</span>
        </div>

        <input type="hidden" name="bantuan_data[jumlah_ahli]" id="basic_total_members" value="0">

        <div class="border border-slate-200 rounded-3xl bg-white p-6 shadow-sm">
            <h3 class="font-semibold text-base text-slate-900 mb-5">
                Maklumat Ketua Pemohon
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Nama Ketua
                    </label>
                    <input
                        type="text"
                        name="bantuan_data[nama_ketua]"
                        value="{{ old('nama_penuh', auth()->user()->name ?? '') }}"
                        readonly
                        class="w-full h-14 px-5 border border-slate-300 bg-slate-100 rounded-2xl text-[15px] text-slate-700 shadow-sm"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        No Matrik
                    </label>
                    <input
                        type="text"
                        name="bantuan_data[no_matrik_ketua]"
                        value="{{ old('no_matrik', auth()->user()->no_matrik ?? '') }}"
                        readonly
                        class="w-full h-14 px-5 border border-slate-300 bg-slate-100 rounded-2xl text-[15px] text-slate-700 shadow-sm"
                    >
                </div>

            </div>
        </div>

        <div class="border border-slate-200 rounded-3xl bg-white p-6 shadow-sm">
            <h3 class="font-semibold text-base text-slate-900 mb-5">
                Maklumat Kediaman Sewa
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Alamat Rumah Sewa
                    </label>
                    <textarea
                        rows="3"
                        name="bantuan_data[alamat_rumah]"
                        required
                        class="w-full px-5 py-3 border border-slate-300 bg-white rounded-2xl text-[15px] shadow-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400"
                        placeholder="Masukkan alamat penuh rumah sewa"
                    >{{ old('bantuan_data.alamat_rumah') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Bandar
                    </label>
                    <input
                        type="text"
                        name="bantuan_data[bandar]"
                        value="{{ old('bantuan_data.bandar') }}"
                        required
                        class="w-full h-14 px-5 border border-slate-300 bg-white rounded-2xl text-[15px] shadow-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Poskod
                    </label>
                    <input
                        type="text"
                        name="bantuan_data[poskod]"
                        value="{{ old('bantuan_data.poskod') }}"
                        inputmode="numeric"
                        maxlength="5"
                        pattern="[0-9]{5}"
                        required
                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 5)"
                        class="w-full h-14 px-5 border border-slate-300 bg-white rounded-2xl text-[15px] shadow-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Negeri
                    </label>
                    <select
                        name="bantuan_data[negeri]"
                        required
                        class="w-full h-14 px-5 border border-slate-300 bg-white rounded-2xl text-[15px] shadow-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400"
                    >
                        <option value="">-- Pilih Negeri --</option>
                        <option value="Selangor">Selangor</option>
                        <option value="Kuala Lumpur">Kuala Lumpur</option>
                        <option value="Johor">Johor</option>
                        <option value="Perak">Perak</option>
                        <option value="Negeri Sembilan">Negeri Sembilan</option>
                        <option value="Melaka">Melaka</option>
                        <option value="Pahang">Pahang</option>
                        <option value="Terengganu">Terengganu</option>
                        <option value="Kelantan">Kelantan</option>
                        <option value="Pulau Pinang">Pulau Pinang</option>
                        <option value="Kedah">Kedah</option>
                        <option value="Perlis">Perlis</option>
                        <option value="Sabah">Sabah</option>
                        <option value="Sarawak">Sarawak</option>
                        <option value="Labuan">Labuan</option>
                        <option value="Putrajaya">Putrajaya</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Jenis Kediaman
                    </label>
                    <select
                        name="bantuan_data[jenis_kediaman]"
                        required
                        class="w-full h-14 px-5 border border-slate-300 bg-white rounded-2xl text-[15px] shadow-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400"
                    >
                        <option value="">-- Pilih Jenis Kediaman --</option>
                        <option value="Rumah Sewa">Rumah Sewa</option>
                        <option value="Kolej Kediaman">Kolej Kediaman</option>
                        <option value="Rumah Keluarga">Rumah Keluarga</option>
                    </select>
                </div>

            </div>
        </div>

        <div id="basicMemberSection" class="border border-slate-200 rounded-3xl bg-white p-6 shadow-sm">

            <div class="flex justify-between items-center mb-5">
                <h3 class="font-semibold text-base text-slate-900">
                    Senarai Ahli Rumah
                </h3>

                <button
                    type="button"
                    onclick="showBasicMemberForm()"
                    id="addBasicMemberBtn"
                    class="bg-blue-600 hover:bg-blue-700 disabled:bg-slate-300 disabled:hover:bg-slate-300 disabled:cursor-not-allowed text-white px-5 py-3 rounded-2xl text-sm font-medium shadow transition"
                    disabled
                >
                    + Tambah Ahli Rumah
                </button>
            </div>

            <div id="basicMemberForm" class="hidden mb-6 border border-slate-200 rounded-2xl p-5 bg-slate-50">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">

                    <input
                        id="basic_name"
                        class="h-12 px-4 border border-slate-300 bg-white rounded-xl text-sm shadow-sm"
                        placeholder="Nama"
                    >

                    <input
                        id="basic_matric"
                        maxlength="7"
                        pattern="A[0-9]{6}"
                        oninput="formatMatricInput(this)"
                        class="h-12 px-4 border border-slate-300 bg-white rounded-xl text-sm shadow-sm"
                        placeholder="No Matrik"
                    >

                    <input
                        id="basic_faculty"
                        class="h-12 px-4 border border-slate-300 bg-white rounded-xl text-sm shadow-sm"
                        placeholder="Fakulti"
                    >

                </div>

                <div class="flex justify-end gap-3">
                    <button
                        type="button"
                        onclick="cancelBasicMemberForm()"
                        class="px-4 py-2 bg-slate-200 hover:bg-slate-300 rounded-xl text-sm"
                    >
                        Batal
                    </button>

                    <button
                        type="button"
                        onclick="addBasicMember()"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl text-sm"
                    >
                        Tambah
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-slate-200">
                <table class="w-full text-sm">
                    <thead class="bg-[#071633] text-white">
                        <tr>
                            <th class="p-4 text-left">Bil</th>
                            <th class="p-4 text-left">Nama</th>
                            <th class="p-4 text-left">No Matrik</th>
                            <th class="p-4 text-left">Fakulti</th>
                            <th class="p-4 text-center">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody id="basicMemberTableBody" class="bg-white"></tbody>
                </table>
            </div>

        </div>

    </div>

</div>
