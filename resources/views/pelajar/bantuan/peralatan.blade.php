{{-- resources/views/pelajar/bantuan/peralatan.blade.php --}}

@php
    $learningEquipmentItems = $learningEquipmentItems ?? collect();
@endphp

<div class="bg-white border border-slate-200 rounded-3xl shadow-lg overflow-hidden mt-6">

    <div class="bg-[#071633] px-8 py-6 text-white">
        <h2 class="text-xl font-semibold tracking-tight">
            Permohonan Peralatan Pembelajaran
        </h2>

        <p class="text-sm text-slate-300 mt-1">
            Pilih peralatan pembelajaran yang diperlukan dan lengkapkan justifikasi permohonan.
        </p>
    </div>

    <div class="p-8 space-y-8 bg-slate-50/30">

        <div class="border border-slate-200 rounded-3xl p-6 bg-white shadow-sm">
            <h3 class="font-semibold text-base text-slate-900 mb-5">
                Pilih Peralatan
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                @forelse($learningEquipmentItems as $item)
                    <label class="equipment-card border border-slate-300 rounded-2xl p-6 cursor-pointer hover:border-blue-500 hover:shadow-md transition bg-slate-50 hover:bg-white">
                        <input
                            type="radio"
                            name="bantuan_data[peralatan]"
                            value="{{ $item->nama_item }}"
                            class="hidden"
                            onchange="selectEquipment(this)"
                        >

                        <div class="text-center">
                            <div class="font-semibold text-base text-slate-800">
                                {{ $item->nama_item }}
                            </div>
                        </div>
                    </label>
                @empty
                    <div class="md:col-span-3 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center">
                        <p class="text-sm font-semibold text-slate-700">Tiada peralatan pembelajaran aktif buat masa ini.</p>
                    </div>
                @endforelse

            </div>
        </div>

        <div class="border border-slate-200 rounded-3xl p-6 bg-white shadow-sm">
            <h3 class="font-semibold text-base text-slate-900 mb-5">
                Sebab Permohonan
            </h3>

            <select
                id="equipment_reason"
                name="bantuan_data[sebab]"
                required
                class="w-full h-14 px-5 border border-slate-300 bg-white rounded-2xl text-[15px] shadow-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition"
                onchange="updateEquipmentSummary()"
            >
                <option value="">-- Pilih Sebab --</option>
                <option value="Rosak">Rosak</option>
                <option value="Tiada kemampuan membeli">Tiada kemampuan membeli</option>
                <option value="Lain-lain">Lain-lain</option>
            </select>
        </div>

        <div class="border border-slate-200 rounded-3xl p-6 bg-white shadow-sm">
            <h3 class="font-semibold text-base text-slate-900 mb-5">
                Justifikasi Detail
            </h3>

            <textarea
                id="equipment_justification"
                name="bantuan_data[justifikasi]"
                rows="5"
                class="w-full border border-slate-300 bg-white p-4 rounded-2xl text-sm shadow-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition"
                placeholder="Nyatakan sebab secara terperinci..."
                oninput="updateEquipmentSummary()"
            >{{ old('bantuan_data.justifikasi') }}</textarea>
        </div>

    </div>

</div>
