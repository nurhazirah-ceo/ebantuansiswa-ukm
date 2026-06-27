{{-- resources/views/pelajar/bantuan/pembelajaran.blade.php --}}

<div class="bg-white border border-slate-200 rounded-3xl shadow-lg overflow-hidden mt-6">

    <!-- Header -->
    <div class="bg-[#071633] px-8 py-6 text-white">
        <h2 class="text-xl font-semibold tracking-tight">
            Permohonan Pembelajaran
        </h2>
        <p class="text-sm text-slate-300 mt-1">
            Lengkapkan maklumat bantuan pembelajaran mengikut jenis permohonan.
        </p>
    </div>

    <div class="p-8 space-y-8 bg-slate-50/30">

        {{-- ================= JENIS PERMOHONAN ================= --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-3">
                Jenis Permohonan
            </label>

            <select
                id="learning_type"
                name="bantuan_data[learning_type]"
                required
                class="w-full h-14 px-5 border border-slate-300 bg-white rounded-2xl text-[15px] shadow-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition"
                onchange="toggleLearningType()"
            >
                <option value="">-- Pilih Jenis Permohonan --</option>
                <option value="individu">Individu</option>
                <option value="group" @selected(old('bantuan_data.learning_type') === 'group')>Kelab / Persatuan / Kelas</option>
            </select>
        </div>

        @php
            $items = $learningItems ?? collect();
        @endphp

        {{-- ===================================================== --}}
        {{-- INDIVIDU SECTION --}}
        {{-- ===================================================== --}}
        <div id="learning-individu-section" class="hidden space-y-6">

            {{-- Maklumat Pemohon --}}
            <div class="border border-slate-200 rounded-3xl bg-white p-6 shadow-sm">
                <h3 class="font-semibold text-base text-slate-900 mb-5">
                    Maklumat Pemohon
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <input
                        type="text"
                        name="bantuan_data[individu][nama]"
                        value="{{ old('nama_penuh', auth()->user()->name ?? '') }}"
                        readonly
                        class="w-full h-14 px-5 border border-slate-300 bg-slate-100 rounded-2xl text-[15px] text-slate-700 shadow-sm"
                    >

                    <input
                        type="text"
                        name="bantuan_data[individu][no_matrik]"
                        value="{{ old('no_matrik', auth()->user()->no_matrik ?? '') }}"
                        readonly
                        class="w-full h-14 px-5 border border-slate-300 bg-slate-100 rounded-2xl text-[15px] text-slate-700 shadow-sm"
                    >

                    <input
                        type="text"
                        name="bantuan_data[individu][fakulti]"
                        value="{{ old('fakulti') }}"
                        readonly
                        class="w-full h-14 px-5 border border-slate-300 bg-slate-100 rounded-2xl text-[15px] text-slate-700 shadow-sm"
                        placeholder="Fakulti"
                    >

                    <input
                        type="text"
                        name="bantuan_data[individu][tahun_pengajian]"
                        value="{{ old('tahun_pengajian') }}"
                        readonly
                        class="w-full h-14 px-5 border border-slate-300 bg-slate-100 rounded-2xl text-[15px] text-slate-700 shadow-sm"
                        placeholder="Tahun Pengajian"
                    >

                </div>
            </div>

            {{-- Senarai Item --}}
            <div class="border border-slate-200 rounded-3xl bg-white p-6 shadow-sm">
                <h3 class="font-semibold text-base text-slate-900 mb-5">
                    Senarai Item Pembelajaran
                </h3>

                <div class="overflow-x-auto rounded-2xl border border-slate-200">
                    <table class="w-full text-sm">
                        <thead class="bg-[#071633] text-white">
                            <tr>
                                <th class="p-4 text-center">Pilih</th>
                                <th class="p-4 text-left">Item</th>
                                <th class="p-4 text-center">Kuantiti</th>
                            </tr>
                        </thead>

                        <tbody class="bg-white">
                            @foreach($items as $index => $item)
                                <tr class="border-b border-slate-100">
                                    <td class="p-4 text-center">
                                        <input
                                            type="hidden"
                                            name="bantuan_data[individu][items][{{ $index }}][item_id]"
                                            value="{{ $item->id }}"
                                        >
                                        <input
                                            type="checkbox"
                                            name="bantuan_data[individu][items][{{ $index }}][selected]"
                                            value="{{ $item->nama_item }}"
                                            data-item-name="{{ $item->nama_item }}"
                                            onchange="toggleItemQty(this,'qty_ind_{{ $index }}'); updateSummary('individu')"
                                        >
                                    </td>

                                    <td class="p-4 text-slate-700">
                                        {{ $item->nama_item }}
                                    </td>

                                    <td class="p-4 text-center">
                                        <input
                                            type="number"
                                            name="bantuan_data[individu][items][{{ $index }}][qty]"
                                            id="qty_ind_{{ $index }}"
                                            min="1"
                                            value="1"
                                            disabled
                                            onchange="updateSummary('individu')"
                                            class="w-24 h-10 border border-slate-300 rounded-xl text-center text-sm shadow-sm"
                                        >
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>
            </div>

            {{-- Ringkasan Permohonan --}}
            <div class="border border-blue-100 rounded-3xl p-6 bg-blue-50 shadow-sm">
                <h3 class="font-semibold text-base text-blue-700 mb-4">
                    Ringkasan Permohonan
                </h3>

                <div id="summary-individu" class="text-sm text-slate-700">
                    Tiada item dipilih.
                </div>
            </div>

            {{-- Justifikasi --}}
            <div class="border border-slate-200 rounded-3xl p-6 bg-white shadow-sm">
                <h3 class="font-semibold text-base text-slate-900 mb-4">
                    Justifikasi Ringkas
                </h3>

                <textarea
                    rows="4"
                    name="bantuan_data[individu][justifikasi]"
                    class="w-full border border-slate-300 bg-white p-4 rounded-2xl text-sm shadow-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400"
                    placeholder="Nyatakan sebab bantuan pembelajaran diperlukan"
                >{{ old('bantuan_data.individu.justifikasi') }}</textarea>
            </div>

        </div>

        {{-- ===================================================== --}}
        {{-- KELAB / PERSATUAN / KELAS SECTION --}}
        {{-- ===================================================== --}}
        <div id="learning-group-section" class="hidden space-y-6">

            {{-- Maklumat Kelab / Persatuan / Kelas --}}
            <div class="border border-slate-200 rounded-3xl bg-white p-6 shadow-sm">
                <h3 class="font-semibold text-base text-slate-900 mb-5">
                    Maklumat Kelab / Persatuan / Kelas
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <input
                        type="text"
                        name="bantuan_data[group][nama_group]"
                        value="{{ old('bantuan_data.group.nama_group') }}"
                        required
                        class="w-full h-14 px-5 border border-slate-300 bg-white rounded-2xl text-[15px] shadow-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400"
                        placeholder="Nama Kelab / Persatuan / Kelas"
                    >

                    <input
                        type="number"
                        name="bantuan_data[group][bil_ahli]"
                        value="{{ old('bantuan_data.group.bil_ahli') }}"
                        min="1"
                        required
                        class="w-full h-14 px-5 border border-slate-300 bg-white rounded-2xl text-[15px] shadow-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400"
                        placeholder="Bilangan Ahli"
                    >

                </div>
            </div>

            {{-- Senarai Item Kelab / Persatuan / Kelas --}}
            <div class="border border-slate-200 rounded-3xl bg-white p-6 shadow-sm">
                <h3 class="font-semibold text-base text-slate-900 mb-5">
                    Senarai Item Pembelajaran
                </h3>

                <div class="overflow-x-auto rounded-2xl border border-slate-200">
                    <table class="w-full text-sm">
                        <thead class="bg-[#071633] text-white">
                            <tr>
                                <th class="p-4 text-center">Pilih</th>
                                <th class="p-4 text-left">Item</th>
                                <th class="p-4 text-center">Kuantiti</th>
                            </tr>
                        </thead>

                        <tbody class="bg-white">
                            @foreach($items as $index => $item)
                                <tr class="border-b border-slate-100">
                                    <td class="p-4 text-center">
                                        <input
                                            type="hidden"
                                            name="bantuan_data[group][items][{{ $index }}][item_id]"
                                            value="{{ $item->id }}"
                                        >
                                        <input
                                            type="checkbox"
                                            name="bantuan_data[group][items][{{ $index }}][selected]"
                                            value="{{ $item->nama_item }}"
                                            data-item-name="{{ $item->nama_item }}"
                                            onchange="toggleItemQty(this,'qty_grp_{{ $index }}'); updateSummary('group')"
                                        >
                                    </td>

                                    <td class="p-4 text-slate-700">
                                        {{ $item->nama_item }}
                                    </td>

                                    <td class="p-4 text-center">
                                        <input
                                            type="number"
                                            name="bantuan_data[group][items][{{ $index }}][qty]"
                                            id="qty_grp_{{ $index }}"
                                            min="1"
                                            value="1"
                                            disabled
                                            onchange="updateSummary('group')"
                                            class="w-24 h-10 border border-slate-300 rounded-xl text-center text-sm shadow-sm"
                                        >
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>
            </div>

            {{-- Ringkasan Kelab / Persatuan / Kelas --}}
            <div class="border border-blue-100 rounded-3xl p-6 bg-blue-50 shadow-sm">
                <h3 class="font-semibold text-base text-blue-700 mb-4">
                    Ringkasan Permohonan
                </h3>

                <div id="summary-group" class="text-sm text-slate-700">
                    Tiada item dipilih.
                </div>
            </div>

        </div>

    </div>

</div>
