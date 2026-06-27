{{-- resources/views/pelajar/bantuan/sukan.blade.php --}}

@php
    $sportsItems = $sportsItems ?? collect();
@endphp

<div class="bg-white border border-slate-200 rounded-3xl shadow-lg overflow-hidden mt-6">

    <div class="bg-[#071633] px-8 py-6 text-white">
        <h2 class="text-xl font-semibold tracking-tight">
            Permohonan Peralatan Sukan
        </h2>

        <p class="text-sm text-slate-300 mt-1">
            Lengkapkan maklumat bantuan peralatan sukan mengikut kategori permohonan.
        </p>
    </div>

    <div class="p-8 space-y-8 bg-slate-50/30">

        {{-- ===================================================== --}}
        {{-- PERINGKAT AKTIVITI / PERTANDINGAN --}}
        {{-- ===================================================== --}}
        <div class="border border-slate-200 rounded-3xl p-6 bg-white shadow-sm">

            <h3 class="font-semibold text-base text-slate-900 mb-5">
                Peringkat Aktiviti / Pertandingan
            </h3>

            <select
                id="sports_level"
                name="bantuan_data[peringkat]"
                required
                class="w-full h-14 px-5 border border-slate-300 bg-white rounded-2xl text-[15px] shadow-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition"
                onchange="toggleSportsLevel()"
            >
                <option value="">-- Pilih Peringkat Aktiviti / Pertandingan --</option>
                <option value="fakulti" @selected(old('bantuan_data.peringkat') === 'fakulti')>Fakulti</option>
                <option value="universiti" @selected(old('bantuan_data.peringkat') === 'universiti')>Universiti</option>
                <option value="kebangsaan" @selected(old('bantuan_data.peringkat') === 'kebangsaan')>Kebangsaan</option>
                <option value="antarabangsa" @selected(old('bantuan_data.peringkat') === 'antarabangsa')>Antarabangsa</option>
            </select>

        </div>

        {{-- ===================================================== --}}
        {{-- MAKLUMAT --}}
        {{-- ===================================================== --}}
        <div
            id="sports-info-section"
            class="hidden border border-slate-200 rounded-3xl p-6 bg-white shadow-sm"
        >

            <h3 class="font-semibold text-base text-slate-900 mb-5">
                Maklumat Permohonan
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Nama Kelab / Pasukan
                    </label>

                    <input
                        type="text"
                        id="sports_org"
                        name="bantuan_data[nama_kelab_pasukan]"
                        value="{{ old('bantuan_data.nama_kelab_pasukan') }}"
                        required
                        class="w-full h-14 px-5 border border-slate-300 bg-white rounded-2xl text-[15px] shadow-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400"
                        placeholder="Masukkan nama kelab / pasukan"
                        oninput="updateSportsSummary()"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Bilangan Peserta
                    </label>

                    <input
                        type="number"
                        id="sports_participants"
                        name="bantuan_data[bilangan_peserta]"
                        value="{{ old('bantuan_data.bilangan_peserta') }}"
                        min="1"
                        required
                        class="w-full h-14 px-5 border border-slate-300 bg-white rounded-2xl text-[15px] shadow-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400"
                        placeholder="Masukkan bilangan peserta"
                        oninput="updateSportsSummary()"
                    >
                </div>

            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- SENARAI ITEM --}}
        {{-- ===================================================== --}}
        <div
            id="sports-items-section"
            class="hidden border border-slate-200 rounded-3xl p-6 bg-white shadow-sm"
        >

            <h3 class="font-semibold text-base text-slate-900 mb-5">
                Senarai Item / Barang
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

                        @forelse($sportsItems as $index => $item)

                        <tr class="border-b border-slate-100">

                            <td class="p-4 text-center">

                                <input
                                    type="hidden"
                                    name="bantuan_data[items][{{$index}}][item_id]"
                                    value="{{ $item->id }}"
                                >

                                <input
                                    type="checkbox"
                                    name="bantuan_data[items][{{$index}}][selected]"
                                    value="{{ $item->nama_item }}"
                                    data-item-name="{{ $item->nama_item }}"
                                    onchange="toggleSportsQty(this,'sports_qty_{{$index}}'); updateSportsSummary()"
                                >

                            </td>

                            <td class="p-4 text-slate-700">
                                {{ $item->nama_item }}
                            </td>

                            <td class="p-4 text-center">

                                <input
                                    type="number"
                                    id="sports_qty_{{$index}}"
                                    name="bantuan_data[items][{{$index}}][qty]"
                                    min="1"
                                    value="1"
                                    disabled
                                    onchange="updateSportsSummary()"
                                    class="w-24 h-10 border border-slate-300 rounded-xl text-center text-sm shadow-sm"
                                >

                            </td>

                        </tr>

                        @empty
                            <tr>
                                <td colspan="3" class="p-6 text-center text-sm text-slate-500">
                                    Tiada peralatan sukan aktif buat masa ini.
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

        {{-- ===================================================== --}}
        {{-- RINGKASAN --}}
        {{-- ===================================================== --}}
        <div
            id="sports-summary-section"
            class="hidden border border-blue-100 rounded-3xl p-6 bg-blue-50 shadow-sm"
        >

            <h3 class="font-semibold text-base text-blue-700 mb-4">
                Ringkasan Permohonan
            </h3>

            <div id="sports-summary" class="text-sm text-slate-700">
                Tiada item dipilih.
            </div>

        </div>

        {{-- ===================================================== --}}
        {{-- JUSTIFIKASI --}}
        {{-- ===================================================== --}}
        <div
            id="sports-justification-section"
            class="hidden border border-slate-200 rounded-3xl p-6 bg-white shadow-sm"
        >

            <h3 class="font-semibold text-base text-slate-900 mb-4">
                Justifikasi Ringkas
            </h3>

            <textarea
                id="sports_justification"
                name="bantuan_data[justifikasi]"
                rows="4"
                class="w-full border border-slate-300 bg-white p-4 rounded-2xl text-sm shadow-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition"
                placeholder="Nyatakan justifikasi ringkas permohonan peralatan sukan..."
                oninput="updateSportsSummary()"
            >{{ old('bantuan_data.justifikasi') }}</textarea>

        </div>

    </div>

</div>
