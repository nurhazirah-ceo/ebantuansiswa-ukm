<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($items as $item)
        @php
            $balance = $item->baki;
            $percent = $item->progress_percentage;
        @endphp

        <div class="bg-white rounded-3xl border border-slate-200 shadow-md p-6 hover:shadow-lg transition group">

            <!-- Image -->
            <div class="flex justify-center mb-4 overflow-hidden">
                <img src="{{ asset('image/' . $item->image_asset_path) }}"
                     alt="{{ $item->nama_item }}"
                     class="w-36 h-36 object-contain transition group-hover:scale-105 item-image">
            </div>

            <!-- Title -->
            <h2 class="text-lg font-semibold text-slate-800">
                {{ $item->nama_item }}
            </h2>

            <!-- Price -->
            <p class="text-sm font-semibold text-[#0B1F3A] mt-1">
                RM {{ number_format((float) $item->harga, 2) }} / unit
            </p>

            <!-- Info -->
            <div class="text-sm text-slate-600 mt-3 space-y-1">
                <p>Jumlah diperlukan: <strong>{{ $item->jumlah_diperlukan }}</strong></p>
                <p>Telah disumbang: <strong>{{ $item->telah_disumbang }}</strong></p>
                <p>Baki: <strong class="text-red-500">{{ $balance }}</strong></p>
            </div>

            <!-- Progress -->
            <div class="mt-3">
                <div class="text-xs text-blue-600 text-right mb-1">
                    {{ $percent }}%
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full"
                         style="width: {{ $percent }}%;">
                    </div>
                </div>
            </div>

            <!-- BUTTON -->
            <button
                data-id="{{ $item->id }}"
                data-name="{{ $item->nama_item }}"
                data-price="{{ $item->harga }}"
                data-category="{{ $item->kategori_bantuan }}"
                data-img="{{ asset('image/' . $item->image_asset_path) }}"
                class="add-to-cart mt-4 w-full bg-[#0B1F3A] text-white py-2.5 rounded-xl text-sm font-semibold hover:scale-[1.03] transition">
                Tambah ke Senarai
            </button>

        </div>
    @empty
        <div class="col-span-full rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-center">
            <p class="text-sm font-semibold text-slate-700">Tiada item sumbangan aktif untuk kategori ini.</p>
            <p class="mt-2 text-sm text-slate-500">Item yang tersedia untuk disumbangkan akan dipaparkan di sini.</p>
        </div>
    @endforelse
</div>
