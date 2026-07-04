<div id="step-2" class="hidden space-y-8">

    <!-- ================= MAKLUMAT PENJAGA ================= -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-lg overflow-hidden">

        <!-- Header -->
        <div class="bg-[#071633] px-8 py-6 text-white">
            <h2 class="text-xl font-semibold tracking-tight">
                Maklumat Penjaga Keluarga
            </h2>

            <p class="text-sm text-slate-300 mt-1">
                Lengkapkan maklumat penjaga utama pemohon.
            </p>
        </div>

        <!-- BODY -->
        <div class="p-8 space-y-8">

<!-- ================= PENJAGA UTAMA ================= -->
<div class="border border-slate-200 rounded-3xl overflow-hidden">

    <div class="bg-slate-100 px-6 py-4 border-b border-slate-200">
        <h3 class="font-semibold text-slate-800 text-base">
            Bapa / Ibu / Penjaga Pemohon
            <span class="text-red-500">*</span>
        </h3>
    </div>

    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">

        <!-- Nama -->
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">
                Nama
            </label>

            <input
                type="text"
                name="penjaga_nama"
                value="{{ old('penjaga_nama') }}"
                required
                class="w-full h-14 px-5 border border-slate-300 bg-white rounded-2xl
                       focus:outline-none focus:ring-2 focus:ring-blue-400
                       focus:border-blue-400 text-[15px] text-slate-800
                       shadow-sm transition duration-200"
            >
        </div>

        <!-- No KP -->
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">
                No KP
            </label>

            <input
                type="text"
                name="penjaga_no_kp"
                value="{{ old('penjaga_no_kp') }}"
                required
                inputmode="numeric"
                maxlength="12"
                pattern="[0-9]{12}"
                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 12)"
                placeholder="Contoh: 800101145678"
                class="w-full h-14 px-5 border border-slate-300 bg-white rounded-2xl
                       focus:outline-none focus:ring-2 focus:ring-blue-400
                       focus:border-blue-400 text-[15px] text-slate-800
                       shadow-sm transition duration-200"
            >
        </div>

        <!-- Hubungan -->
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">
                Hubungan Dengan Pemohon
            </label>

            <div class="relative">
                <select
                    name="penjaga_hubungan"
                    required
                    class="w-full h-14 px-5 pr-12 border border-slate-300 bg-white rounded-2xl
                           focus:outline-none focus:ring-2 focus:ring-blue-400
                           focus:border-blue-400 text-[15px] text-slate-800
                           shadow-sm transition duration-200 appearance-none"
                >
                    <option value="">-- Pilih Hubungan --</option>

                    <option value="Bapa" {{ old('penjaga_hubungan') == 'Bapa' ? 'selected' : '' }}>
                        Bapa
                    </option>

                    <option value="Ibu" {{ old('penjaga_hubungan') == 'Ibu' ? 'selected' : '' }}>
                        Ibu
                    </option>

                    <option value="Penjaga" {{ old('penjaga_hubungan') == 'Penjaga' ? 'selected' : '' }}>
                        Penjaga
                    </option>

                    <option value="Datuk" {{ old('penjaga_hubungan') == 'Datuk' ? 'selected' : '' }}>
                        Datuk
                    </option>

                    <option value="Nenek" {{ old('penjaga_hubungan') == 'Nenek' ? 'selected' : '' }}>
                        Nenek
                    </option>

                    <option value="Abang" {{ old('penjaga_hubungan') == 'Abang' ? 'selected' : '' }}>
                        Abang
                    </option>

                    <option value="Kakak" {{ old('penjaga_hubungan') == 'Kakak' ? 'selected' : '' }}>
                        Kakak
                    </option>

                    <option value="Lain-lain" {{ old('penjaga_hubungan') == 'Lain-lain' ? 'selected' : '' }}>
                        Lain-lain
                    </option>
                </select>

                <div class="pointer-events-none absolute inset-y-0 right-5 flex items-center text-slate-500">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="w-5 h-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Telefon -->
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">
                Telefon
            </label>

            <input
                type="text"
                name="penjaga_telefon"
                value="{{ old('penjaga_telefon') }}"
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

        <!-- Pekerjaan -->
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">
                Pekerjaan
            </label>

            <input
                type="text"
                name="penjaga_pekerjaan"
                value="{{ old('penjaga_pekerjaan') }}"
                required
                class="w-full h-14 px-5 border border-slate-300 bg-white rounded-2xl
                       focus:outline-none focus:ring-2 focus:ring-blue-400
                       focus:border-blue-400 text-[15px] text-slate-800
                       shadow-sm transition duration-200"
            >
        </div>

        <!-- Pendapatan -->
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">
                Pendapatan Sebulan (RM)
            </label>

            <input
                type="number"
                name="penjaga_pendapatan"
                id="primary_income"
                value="{{ old('penjaga_pendapatan') }}"
                required
                min="0"
                max="99999999.99"
                step="0.01"
                inputmode="decimal"
                oninput="this.value = this.value.replace(/[^0-9.]/g, ''); calculateIncome();"
                placeholder="Contoh: 1500.00"
                class="w-full h-14 px-5 border border-slate-300 bg-white rounded-2xl
                       focus:outline-none focus:ring-2 focus:ring-blue-400
                       focus:border-blue-400 text-[15px] text-slate-800
                       shadow-sm transition duration-200"
            >
        </div>

    </div>
</div>

        </div>
    </div>

    <!-- ================= ISI RUMAH ================= -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-lg overflow-hidden">

        <!-- Header -->
        <div class="px-8 py-6 border-b border-slate-200 flex flex-col md:flex-row md:justify-between md:items-center gap-4">

            <div>
                <h2 class="text-xl font-semibold text-slate-900">
                    Maklumat Isi Rumah / Tanggungan
                </h2>

                <p class="text-sm text-slate-500 mt-1">
                    Tambah ahli keluarga yang tinggal bersama pemohon.
                </p>
            </div>

            <button type="button"
                    onclick="showFamilyForm()"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-2xl text-sm font-medium shadow">
                + Tambah Ahli Keluarga
            </button>

        </div>

        <div class="p-8">

            <!-- ================= INPUT FORM ================= -->
            <div id="familyInputSection"
                 class="hidden mb-8 border border-blue-100 rounded-3xl p-8 bg-blue-50/40 shadow-inner">

                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-slate-800">
                        Tambah Ahli Keluarga
                    </h3>

                    <p class="text-sm text-slate-500 mt-1">
                        Masukkan maklumat ahli keluarga / tanggungan.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-6">

                    <!-- Nama -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Nama Penuh
                        </label>

                        <input id="fam_name"
                               class="w-full h-12 px-4 border border-slate-300 bg-white rounded-xl text-sm shadow-sm"
                               placeholder="Contoh: Nur Aisyah">
                    </div>

                    <!-- Hubungan -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Hubungan
                        </label>

                        <select id="fam_relation"
                                onchange="toggleRelationOther()"
                                class="w-full h-12 px-4 border border-slate-300 bg-white rounded-xl text-sm shadow-sm">

                            <option value="">Pilih Hubungan</option>
                            <option>Abang</option>
                            <option>Kakak</option>
                            <option>Adik</option>
                            <option>Lain-lain</option>

                        </select>
                    </div>

                    <!-- Hubungan lain -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Nyatakan Hubungan
                        </label>

                        <input id="fam_relation_other"
                               class="w-full h-12 px-4 border border-slate-300 bg-white rounded-xl hidden text-sm shadow-sm"
                               placeholder="Nyatakan hubungan">
                    </div>

                    <!-- Umur -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Umur
                        </label>

                        <input type="number"
                               id="fam_age"
                               class="w-full h-12 px-4 border border-slate-300 bg-white rounded-xl text-sm shadow-sm"
                               placeholder="12">
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Status
                        </label>

                        <select id="fam_status"
                                class="w-full h-12 px-4 border border-slate-300 bg-white rounded-xl text-sm shadow-sm">

                            <option value="">Pilih Status</option>
                            <option value="Dewasa Bekerja">Dewasa Bekerja</option>
                            <option value="Dewasa Belajar">Dewasa Belajar</option>
                            <option value="Dewasa Tidak Bekerja">Dewasa Tidak Bekerja</option>
                            <option value="Tanggungan (0–6 Tahun)">Tanggungan (0–6 Tahun)</option>
                            <option value="Tanggungan (7–17 Tahun)">Tanggungan (7–17 Tahun)</option>

                        </select>
                    </div>

                    <!-- Kesihatan -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Kesihatan
                        </label>

                        <select id="fam_health"
                                onchange="toggleHealthRemark()"
                                class="w-full h-12 px-4 border border-slate-300 bg-white rounded-xl text-sm shadow-sm">

                            <option value="">Pilih Kesihatan</option>
                            <option value="SIHAT">SIHAT</option>
                            <option value="SAKIT KRONIK">SAKIT KRONIK</option>
                            <option value="OKU">OKU</option>
                            <option value="LAIN-LAIN">Lain-lain</option>

                        </select>
                    </div>

                    <!-- Penyakit -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Nyatakan Penyakit
                        </label>

                        <input id="health_remark"
                               class="w-full h-12 px-4 border border-slate-300 bg-white rounded-xl hidden text-sm shadow-sm"
                               placeholder="Nyatakan penyakit">
                    </div>

                    <!-- Pendapatan -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Pendapatan (RM)
                        </label>

                        <input type="number"
                               id="fam_income"
                               min="0"
                               max="99999999.99"
                               step="0.01"
                               inputmode="decimal"
                               oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                               class="w-full h-12 px-4 border border-slate-300 bg-white rounded-xl text-sm shadow-sm"
                               placeholder="0.00">
                    </div>

                </div>

                <!-- Buttons -->
                <div class="flex justify-end gap-3">

                    <button type="button"
                            onclick="cancelFamilyForm()"
                            class="px-5 py-3 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-2xl text-sm font-medium transition">
                        Batal
                    </button>

                    <button type="button"
                            onclick="addFamilyMember()"
                            class="px-5 py-3 bg-green-600 hover:bg-green-700 text-white rounded-2xl text-sm font-medium shadow transition">
                        Tambah
                    </button>

                </div>

            </div>

            <!-- ================= TABLE ================= -->
            <div class="overflow-x-auto rounded-2xl border border-slate-200">

                <table class="w-full text-sm">

                    <thead class="bg-[#071633] text-white">

                        <tr>
                            <th class="p-4 text-left">Bil</th>
                            <th class="p-4 text-left">Nama</th>
                            <th class="p-4 text-left">Hubungan</th>
                            <th class="p-4 text-left">Umur</th>
                            <th class="p-4 text-left">Status</th>
                            <th class="p-4 text-left">Kesihatan</th>
                            <th class="p-4 text-left">Pendapatan</th>
                            <th class="p-4 text-center">Tindakan</th>
                        </tr>

                    </thead>

                    <tbody id="familyTableBody" class="bg-white"></tbody>

                </table>

            </div>

            <!-- ================= TOTAL ================= -->
            <div class="mt-6 flex justify-end items-center gap-3">

                <span class="text-base font-medium text-slate-700">
                    Jumlah Pendapatan Isi Rumah:
                </span>

                <span id="totalIncome"
                      class="text-xl font-bold text-blue-700">
                    RM0.00
                </span>

            </div>

        </div>
    </div>

</div>
