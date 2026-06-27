@php
    $lockedBantuanTypes = $lockedBantuanTypes ?? [];
@endphp

<div id="step-3" class="hidden space-y-8">

    <div class="bg-white border border-slate-200 rounded-3xl shadow-lg overflow-hidden">

        <div class="bg-[#071633] px-8 py-6 text-white">
            <h2 class="text-xl font-semibold tracking-tight">
                Jenis Bantuan
            </h2>

            <p class="text-sm text-slate-300 mt-1">
                Pilih jenis bantuan dan kategori bantuan yang ingin dipohon.
            </p>
        </div>

        <div class="p-8 space-y-6">

            <div class="bg-blue-50 border border-blue-100 rounded-2xl px-5 py-4">
                <p class="text-sm text-blue-700 leading-relaxed">
                    Setiap pelajar hanya dibenarkan memohon bantuan tertakluk kepada syarat dan had permohonan yang ditetapkan.
                </p>
            </div>

            @if(count($lockedBantuanTypes) > 0)
                <div class="bg-emerald-50 border border-emerald-100 rounded-2xl px-5 py-4">
                    @foreach($lockedBantuanTypes as $lockedBantuan)
                        <p class="text-sm font-semibold text-emerald-800 {{ $loop->first ? '' : 'mt-2' }}">
                            {{ $lockedBantuan['message'] }}
                        </p>
                    @endforeach
                </div>
            @endif

            <!-- JENIS BANTUAN -->
            <div class="max-w-xl">
                <label class="block text-sm font-medium text-slate-700 mb-3">
                    Pilih Jenis Bantuan
                </label>

                <div class="relative">
                    <select
                        id="jenis_bantuan"
                        name="jenis_bantuan"
                        required
                        onchange="loadKategoriBantuan()"
                        class="w-full h-14 px-5 pr-12 border border-slate-300 bg-white rounded-2xl
                               focus:outline-none focus:ring-2 focus:ring-blue-400
                               focus:border-blue-400 text-[15px] text-slate-800
                               shadow-sm transition duration-200 appearance-none"
                    >
                        <option value="">-- Pilih Jenis Bantuan --</option>
                        <option value="bantuan_asas_hidup" @disabled(isset($lockedBantuanTypes['bantuan_asas_hidup']))>Bantuan Asas Hidup</option>
                        <option value="bantuan_pembelajaran" @disabled(isset($lockedBantuanTypes['bantuan_pembelajaran']))>Bantuan Pembelajaran</option>
                        <option value="bantuan_sukan" @disabled(isset($lockedBantuanTypes['bantuan_sukan']))>Bantuan Sukan</option>
                        <option value="bantuan_musibah">Bantuan Musibah</option>
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

            <!-- KATEGORI BANTUAN -->
            <div id="kategori-bantuan-wrapper" class="hidden max-w-xl">
                <label class="block text-sm font-medium text-slate-700 mb-3">
                    Pilih Kategori Bantuan
                </label>

                <div class="relative">
                    <select
                        id="kategori_bantuan"
                        name="kategori_bantuan"
                        required
                        disabled
                        onchange="loadBantuanForm()"
                        class="w-full h-14 px-5 pr-12 border border-slate-300 bg-white rounded-2xl
                               focus:outline-none focus:ring-2 focus:ring-blue-400
                               focus:border-blue-400 text-[15px] text-slate-800
                               shadow-sm transition duration-200 appearance-none"
                    >
                        <option value="">-- Pilih Kategori Bantuan --</option>
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

        </div>
    </div>

    <div id="form-keperluan-asas" class="hidden transition-all duration-300">
        @include('pelajar.bantuan.keperluan-asas')
    </div>

    <div id="form-pembelajaran" class="hidden transition-all duration-300">
        @include('pelajar.bantuan.pembelajaran')
    </div>

    <div id="form-peralatan" class="hidden transition-all duration-300">
        @include('pelajar.bantuan.peralatan')
    </div>

    <div id="form-sukan" class="hidden transition-all duration-300">
        @include('pelajar.bantuan.sukan')
    </div>

</div>
