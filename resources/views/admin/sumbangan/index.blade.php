@extends('layouts.admin')

@section('page-title', 'Sumbangan')

@section('content')
<div class="min-h-screen bg-slate-50 py-8">
    <div class="mx-auto max-w-7xl space-y-7 px-6">
    <x-page-hero
        class="relative"
        eyebrow="PENGURUSAN SUMBANGAN"
        title="Stok Sumbangan Bantuan"
        description="Pantau dan kemas kini jumlah diperlukan, jumlah telah disumbang dan baki stok bantuan."
    >
        <div class="mt-5 flex justify-start sm:absolute sm:right-6 sm:top-6 sm:mt-0">
            <button type="button"
                    data-open-add-item-modal
                    class="inline-flex items-center justify-center rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-[#071633] shadow-sm transition hover:bg-slate-100">
                + Tambah Item
            </button>
        </div>
    </x-page-hero>

        @if(session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800">
                <p class="font-semibold">Sila semak semula maklumat stok.</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="grid gap-5 md:grid-cols-3">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Jumlah Diperlukan</p>
                <h2 id="summaryNeed" class="mt-3 text-4xl font-extrabold text-slate-950">{{ $totalNeed }}</h2>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Telah Disumbang</p>
                <h2 id="summaryDonated" class="mt-3 text-4xl font-extrabold text-emerald-600">{{ $totalDonated }}</h2>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Baki</p>
                <h2 id="summaryBalance" class="mt-3 text-4xl font-extrabold text-rose-600">{{ $totalBalance }}</h2>
            </div>
        </section>

        <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-3" aria-label="Kategori stok sumbangan">
            @foreach($categories as $category)
                @php
                    $categoryItems = collect($category['items']);
                    $previewItems = $categoryItems->take(3);
                    $categoryNeed = $categoryItems->sum('jumlah_diperlukan');
                    $categoryBalance = $categoryItems->sum('baki');
                @endphp

                <button type="button"
                        data-category-tab
                        data-category-target="{{ $category['key'] }}"
                        aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                        class="category-tab rounded-3xl border border-slate-200 bg-white p-5 text-left shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Kategori</p>
                            <h2 class="mt-2 text-xl font-bold text-slate-950">{{ $category['title'] }}</h2>
                            <p class="mt-2 line-clamp-2 text-sm text-slate-500">{{ $category['description'] }}</p>
                        </div>

                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                            {{ $categoryItems->count() }} item
                        </span>
                    </div>

                    <div class="mt-5 flex items-center justify-between gap-4">
                        <div class="flex -space-x-3">
                            @forelse($previewItems as $previewItem)
                                <span class="grid h-14 w-14 place-items-center rounded-2xl border-2 border-white bg-slate-50 shadow-sm">
                                    <img src="{{ asset('image/' . $previewItem->image_asset_path) }}"
                                         alt="{{ $previewItem->nama_item }}"
                                         class="max-h-11 max-w-11 object-contain">
                                </span>
                            @empty
                                <span class="grid h-14 w-14 place-items-center rounded-2xl border border-dashed border-slate-300 bg-slate-50 text-xs font-semibold text-slate-400">
                                    Tiada
                                </span>
                            @endforelse
                        </div>

                        <div class="text-right">
                            <p class="text-xs font-semibold text-slate-500">Diperlukan</p>
                            <p class="text-lg font-extrabold text-slate-950">{{ $categoryNeed }}</p>
                            <p class="mt-1 text-xs font-semibold text-rose-600">Baki {{ $categoryBalance }}</p>
                        </div>
                    </div>
                </button>
            @endforeach
        </section>

        @foreach($categories as $category)
            <section @class(['hidden' => ! $loop->first]) id="{{ $category['key'] }}" data-category-panel="{{ $category['key'] }}">
                <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($category['items'] as $item)
                        @php
                            $balance = $item->baki;
                            $percent = $item->progress_percentage;
                        @endphp

                        <div class="relative">
                            <form
                                id="remove-item-{{ $item->id }}"
                                method="POST"
                                action="{{ route('admin.sumbangan.remove', $item) }}"
                                class="absolute right-4 top-4 z-10"
                                data-confirm
                                data-confirm-icon="warning"
                                data-confirm-title="Adakah anda pasti mahu membuang item ini?"
                                data-confirm-text="Item ini akan disembunyikan daripada senarai aktif."
                                data-confirm-button="Ya, buang"
                                data-confirm-color="#dc2626"
                            >
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-red-200 bg-red-50 text-red-600 shadow-sm transition hover:bg-red-100"
                                        aria-label="Buang {{ $item->nama_item }} daripada senarai aktif"
                                        title="Buang item">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         viewBox="0 0 24 24"
                                         fill="none"
                                         stroke="currentColor"
                                         stroke-width="2"
                                         stroke-linecap="round"
                                         stroke-linejoin="round"
                                         class="h-4 w-4"
                                         aria-hidden="true">
                                        <path d="M3 6h18" />
                                        <path d="M8 6V4h8v2" />
                                        <path d="M19 6l-1 14H6L5 6" />
                                        <path d="M10 11v5" />
                                        <path d="M14 11v5" />
                                    </svg>
                                </button>
                            </form>

                            <form
                                method="POST"
                                action="{{ route('admin.sumbangan.update', $item) }}"
                                class="donation-card rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
                                data-category="{{ $category['key'] }}"
                                data-confirm
                                data-confirm-title="Kemaskini stok sumbangan?"
                                data-confirm-text="Perubahan jumlah stok bantuan akan disimpan."
                                data-confirm-button="Ya, kemaskini"
                            >
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ $item->status }}">
                                <input type="hidden" name="susunan" value="{{ $item->susunan }}">

                                <div class="flex items-start gap-5">
                                    <div class="grid h-32 w-32 shrink-0 place-items-center rounded-2xl bg-slate-50">
                                        <img src="{{ asset('image/' . $item->image_asset_path) }}"
                                             alt="{{ $item->nama_item }}"
                                             class="max-h-28 max-w-28 object-contain">
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">
                                            {{ $category['title'] }}
                                        </p>
                                        <label class="mt-2 block">
                                            <span class="sr-only">Nama item</span>
                                            <input type="text"
                                                   name="nama_item"
                                                   value="{{ $item->nama_item }}"
                                                   class="w-full rounded-xl border-slate-200 text-lg font-bold leading-tight text-slate-950 focus:border-[#071633] focus:ring-[#071633]">
                                        </label>
                                        <label class="mt-3 block">
                                            <span class="text-xs font-semibold text-slate-500">Harga / unit</span>
                                            <input type="number"
                                                   name="harga"
                                                   min="0"
                                                   step="0.01"
                                                   value="{{ $item->harga }}"
                                                   class="mt-1 w-full rounded-xl border-slate-200 text-sm font-semibold text-[#071633] focus:border-[#071633] focus:ring-[#071633]">
                                        </label>
                                    </div>
                                </div>

                                <div class="mt-5 grid gap-3">
                                    <div class="block">
                                        <span class="text-xs font-semibold text-slate-500">Kategori</span>
                                        <p class="mt-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700">
                                            {{ $item->kategori_bantuan_label }}
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-5 grid gap-3 sm:grid-cols-3">
                                    <label class="block">
                                        <span class="text-xs font-semibold text-slate-500">Jumlah diperlukan</span>
                                        <input type="number"
                                               name="jumlah_diperlukan"
                                               min="0"
                                               value="{{ $item->jumlah_diperlukan }}"
                                               class="stock-need mt-1 w-full rounded-xl border-slate-200 text-sm font-semibold focus:border-[#071633] focus:ring-[#071633]">
                                    </label>

                                    <label class="block">
                                        <span class="text-xs font-semibold text-slate-500">Telah disumbang</span>
                                        <input type="number"
                                               name="telah_disumbang"
                                               min="0"
                                               value="{{ $item->telah_disumbang }}"
                                               class="stock-donated mt-1 w-full rounded-xl border-slate-200 text-sm font-semibold focus:border-[#071633] focus:ring-[#071633]">
                                    </label>

                                    <label class="block">
                                        <span class="text-xs font-semibold text-slate-500">Baki</span>
                                        <input type="number"
                                               value="{{ $balance }}"
                                               readonly
                                               class="stock-balance mt-1 w-full rounded-xl border-slate-200 bg-slate-50 text-sm font-semibold text-rose-600">
                                    </label>
                                </div>

                                <div class="mt-5">
                                    <div class="mb-1 flex justify-between text-xs font-semibold text-slate-500">
                                        <span>Progress sumbangan</span>
                                        <span class="stock-percent">{{ $percent }}%</span>
                                    </div>
                                    <div class="h-2 rounded-full bg-slate-200">
                                        <div class="stock-progress h-2 rounded-full bg-blue-600 transition-all"
                                             style="width: {{ $percent }}%;"></div>
                                    </div>
                                </div>

                                <button type="button"
                                        data-update-stock-button
                                        class="mt-5 w-full rounded-xl bg-[#071633] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#102544]">
                                    Kemaskini Stok
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach

    </div>
</div>

<div id="addItemModal"
     data-add-item-modal
     data-open-on-load="{{ $errors->storeItem->any() ? 'true' : 'false' }}"
     class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-950/60 px-4 py-6">
    <button type="button"
            data-close-add-item-modal
            class="absolute inset-0 h-full w-full cursor-default"
            aria-label="Tutup modal"></button>

    <div class="relative flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-[2rem] bg-white shadow-2xl">
        <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-6 py-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-600">Item Bantuan</p>
                <h2 class="mt-2 text-2xl font-bold text-slate-950">Tambah Item Bantuan</h2>
            </div>

            <button type="button"
                    data-close-add-item-modal
                    class="rounded-full bg-slate-100 px-3 py-1.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-200">
                X
            </button>
        </div>

        <form method="POST"
              action="{{ route('admin.sumbangan.store') }}"
              enctype="multipart/form-data"
              class="overflow-y-auto px-6 py-6">
            @csrf

            @if($errors->storeItem->any())
                <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800">
                    <p class="font-semibold">Sila semak semula maklumat item.</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach($errors->storeItem->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block sm:col-span-2">
                    <span class="text-sm font-semibold text-slate-700">Nama Item</span>
                    <input type="text"
                           name="nama_item"
                           value="{{ old('nama_item') }}"
                           required
                           class="mt-1 w-full rounded-xl border-slate-200 text-sm font-semibold focus:border-[#071633] focus:ring-[#071633]">
                </label>

                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Kategori Bantuan</span>
                    <select name="kategori_bantuan"
                            required
                            class="mt-1 w-full rounded-xl border-slate-200 text-sm font-semibold focus:border-[#071633] focus:ring-[#071633]">
                        @foreach(\App\Models\Item::DONATION_CATEGORIES as $key => $meta)
                            <option value="{{ $key }}" @selected(old('kategori_bantuan', \App\Models\Item::CATEGORY_KEPERLUAN_ASAS) === $key)>
                                {{ $meta['title'] }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Harga / unit</span>
                    <input type="number"
                           name="harga"
                           value="{{ old('harga', 0) }}"
                           min="0"
                           step="0.01"
                           required
                           class="mt-1 w-full rounded-xl border-slate-200 text-sm font-semibold focus:border-[#071633] focus:ring-[#071633]">
                </label>

                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Jumlah Diperlukan</span>
                    <input type="number"
                           name="jumlah_diperlukan"
                           value="{{ old('jumlah_diperlukan', 0) }}"
                           min="0"
                           required
                           class="mt-1 w-full rounded-xl border-slate-200 text-sm font-semibold focus:border-[#071633] focus:ring-[#071633]">
                </label>

                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Telah Disumbang</span>
                    <input type="number"
                           name="telah_disumbang"
                           value="{{ old('telah_disumbang', 0) }}"
                           min="0"
                           required
                           class="mt-1 w-full rounded-xl border-slate-200 text-sm font-semibold focus:border-[#071633] focus:ring-[#071633]">
                </label>

                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Status</span>
                    <select name="status"
                            required
                            class="mt-1 w-full rounded-xl border-slate-200 text-sm font-semibold focus:border-[#071633] focus:ring-[#071633]">
                        <option value="aktif" @selected(old('status', 'aktif') === 'aktif')>aktif</option>
                        <option value="tidak_aktif" @selected(old('status') === 'tidak_aktif')>tidak_aktif</option>
                    </select>
                </label>

                <label class="block sm:col-span-2">
                    <span class="text-sm font-semibold text-slate-700">Muat Naik Imej</span>
                    <input type="file"
                           name="imej"
                           accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                           required
                           class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 file:mr-4 file:rounded-xl file:border-0 file:bg-[#071633] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-[#102544]">
                </label>
            </div>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
                <button type="button"
                        data-close-add-item-modal
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Cancel
                </button>
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-xl bg-[#071633] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#102544]">
                    Tambah Item
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function normalizeNumber(value) {
        const number = parseInt(value, 10);
        return Number.isNaN(number) || number < 0 ? 0 : number;
    }

    function updateCard(card) {
        const needInput = card.querySelector('.stock-need');
        const donatedInput = card.querySelector('.stock-donated');
        const balanceInput = card.querySelector('.stock-balance');
        const percentText = card.querySelector('.stock-percent');
        const progressBar = card.querySelector('.stock-progress');

        const need = normalizeNumber(needInput.value);
        const donated = normalizeNumber(donatedInput.value);
        const balance = need - donated;
        const percent = need > 0 ? Math.min(Math.round((donated / need) * 100), 100) : 0;

        needInput.value = need;
        donatedInput.value = donated;
        balanceInput.value = balance;
        percentText.textContent = percent + '%';
        progressBar.style.width = percent + '%';
    }

    function updateSummary() {
        let totalNeed = 0;
        let totalDonated = 0;

        document.querySelectorAll('.donation-card').forEach((card) => {
            totalNeed += normalizeNumber(card.querySelector('.stock-need').value);
            totalDonated += normalizeNumber(card.querySelector('.stock-donated').value);
        });

        document.getElementById('summaryNeed').textContent = totalNeed;
        document.getElementById('summaryDonated').textContent = totalDonated;
        document.getElementById('summaryBalance').textContent = totalNeed - totalDonated;
    }

    document.querySelectorAll('.donation-card').forEach((card) => {
        card.querySelectorAll('.stock-need, .stock-donated').forEach((input) => {
            input.addEventListener('input', () => {
                updateCard(card);
                updateSummary();
            });
        });

        card.querySelector('[data-update-stock-button]').addEventListener('click', () => {
            updateCard(card);
            updateSummary();
            card.requestSubmit();
        });
    });

    const categoryTabs = document.querySelectorAll('[data-category-tab]');
    const categoryPanels = document.querySelectorAll('[data-category-panel]');

    function activateCategory(categoryKey) {
        categoryTabs.forEach((tab) => {
            const isActive = tab.dataset.categoryTarget === categoryKey;

            tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
            tab.classList.toggle('border-slate-300', isActive);
            tab.classList.toggle('bg-slate-50', isActive);
            tab.classList.toggle('shadow-lg', isActive);
            tab.classList.toggle('border-slate-200', !isActive);
        });

        categoryPanels.forEach((panel) => {
            panel.classList.toggle('hidden', panel.dataset.categoryPanel !== categoryKey);
        });
    }

    categoryTabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            activateCategory(tab.dataset.categoryTarget);
        });
    });

    if (categoryTabs.length > 0) {
        activateCategory(categoryTabs[0].dataset.categoryTarget);
    }

    (() => {
        const modal = document.querySelector('[data-add-item-modal]');
        const openButtons = document.querySelectorAll('[data-open-add-item-modal]');
        const closeButtons = document.querySelectorAll('[data-close-add-item-modal]');

        if (!modal || openButtons.length === 0) {
            return;
        }

        const openModal = () => {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        };

        const closeModal = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        };

        openButtons.forEach((button) => {
            button.addEventListener('click', openModal);
        });

        closeButtons.forEach((button) => {
            button.addEventListener('click', closeModal);
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });

        if (modal.dataset.openOnLoad === 'true') {
            openModal();
        }
    })();
</script>
@endsection
