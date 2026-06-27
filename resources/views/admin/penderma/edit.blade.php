@extends('layouts.admin')

@section('page-title', 'Edit Penderma')
@section('page-subtitle', 'Kemas kini maklumat penderma dan tetapan paparan homepage.')

@section('content')

@php
    $address = $donor->address;
    $selectedContact = old('preferred_contact', $donor->preferred_contact);
    $selectedState = old('state', optional($address)->state);
    $countryOptions = ['Malaysia', 'Singapore', 'Indonesia', 'Thailand', 'Brunei', 'Lain-lain'];
    $savedCountry = old('country', optional($address)->country ?: 'Malaysia');
    $selectedCountry = in_array($savedCountry, $countryOptions, true) ? $savedCountry : 'Lain-lain';
    $countryOtherValue = old('country_other', $selectedCountry === 'Lain-lain' ? $savedCountry : '');
    $showOnHomepage = $errors->any() ? old('show_on_homepage') : $donor->show_on_homepage;
    $states = \App\Http\Controllers\Admin\DonorController::stateOptions();
@endphp

<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-7xl mx-auto px-6 grid grid-cols-12 gap-6">

        <x-page-hero
            class="col-span-12"
            eyebrow="Penderma"
            :title="'Edit Penderma: ' . $user->name"
            description="Kemas kini maklumat penderma dan tetapan paparan homepage."
        />

        {{-- ================= SIDEBAR ================= --}}
        <aside class="col-span-12 md:col-span-3 sticky top-24 h-fit">
            <div class="bg-white border-l-4 border-blue-600 rounded-lg p-4 space-y-4">
                <h3 class="font-semibold text-gray-800">Navigasi Borang</h3>

                <nav class="space-y-1 text-sm">
                    <a href="#section-info" class="nav-item block px-3 py-2 rounded border-l-4 border-transparent">
                        Maklumat Penderma
                    </a>
                    <a href="#section-address" class="nav-item block px-3 py-2 rounded border-l-4 border-transparent">
                        Alamat
                    </a>
                    <a href="#section-document" class="nav-item block px-3 py-2 rounded border-l-4 border-transparent">
                        Dokumen Sokongan
                    </a>
                    <a href="#section-logo" class="nav-item block px-3 py-2 rounded border-l-4 border-transparent">
                        Logo Penderma
                    </a>
                    <a href="#section-homepage" class="nav-item block px-3 py-2 rounded border-l-4 border-transparent">
                        Tetapan Homepage
                    </a>
                    <a href="#section-note" class="nav-item block px-3 py-2 rounded border-l-4 border-transparent">
                        Catatan Admin
                    </a>
                </nav>
            </div>
        </aside>

        {{-- ================= FORM ================= --}}
        <main class="col-span-12 md:col-span-9">

            @if ($errors->has('error'))
                <div class="mb-4 rounded bg-red-100 text-red-700 px-4 py-3">
                    {{ $errors->first('error') }}
                </div>
            @endif

            @if ($errors->any() && ! $errors->has('error'))
                <div class="mb-4 rounded bg-red-100 text-red-700 px-4 py-3 text-sm">
                    <p class="font-semibold">Sila semak semula maklumat yang dimasukkan.</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST"
                  action="{{ route('admin.penderma.update', $user->id) }}"
                  enctype="multipart/form-data"
                  class="space-y-6"
                  data-confirm
                  data-confirm-title="Simpan perubahan penderma?"
                  data-confirm-text="Maklumat penderma akan dikemaskini."
                  data-confirm-button="Ya, simpan">
                @csrf
                @method('PUT')

                {{-- ================= SECTION: INFO ================= --}}
                <section id="section-info" class="bg-white border rounded-lg p-6">
                    <h3 class="text-base font-semibold text-gray-800 mb-4">Maklumat Penderma</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama</label>
                            <input type="text"
                                   name="name"
                                   value="{{ old('name', $user->name) }}"
                                   class="form-input"
                                   required>
                            @error('name')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Emel</label>
                            <input type="email"
                                   name="email"
                                   value="{{ old('email', $user->email) }}"
                                   class="form-input"
                                   required>
                            @error('email')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">No Telefon</label>
                            <input type="text"
                                   name="phone"
                                   value="{{ old('phone', $donor->phone) }}"
                                   class="form-input"
                                   maxlength="11"
                                   inputmode="numeric"
                                   pattern="[0-9]*"
                                   autocomplete="tel"
                                   required
                                   data-phone-input>
                            @error('phone')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Wakil Organisasi</label>
                            <input type="text"
                                   name="representative_name"
                                   value="{{ old('representative_name', $donor->representative_name) }}"
                                   class="form-input">
                            @error('representative_name')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Saluran Komunikasi Pilihan
                            </label>

                            <div class="flex items-center gap-6 min-h-[42px]">
                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                    <input type="radio"
                                           name="preferred_contact"
                                           value="email"
                                           @checked($selectedContact === 'email')
                                           class="text-blue-600 border-gray-300">
                                    Email
                                </label>

                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                    <input type="radio"
                                           name="preferred_contact"
                                           value="phone"
                                           @checked($selectedContact === 'phone')
                                           class="text-blue-600 border-gray-300">
                                    Telefon
                                </label>
                            </div>

                            @error('preferred_contact')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>

                {{-- ================= SECTION: ALAMAT ================= --}}
                <section id="section-address" class="bg-white border rounded-lg p-6">
                    <h3 class="text-base font-semibold text-gray-800 mb-4">Alamat</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Alamat Baris 1</label>
                            <input type="text"
                                   name="address_line_1"
                                   value="{{ old('address_line_1', optional($address)->address_line_1) }}"
                                   class="form-input">
                            @error('address_line_1')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Alamat Baris 2</label>
                            <input type="text"
                                   name="address_line_2"
                                   value="{{ old('address_line_2', optional($address)->address_line_2) }}"
                                   class="form-input">
                            @error('address_line_2')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Negeri</label>
                            <select name="state" class="form-input">
                                <option value="">Pilih Negeri</option>
                                @foreach ($states as $state)
                                    <option value="{{ $state }}" @selected($selectedState === $state)>
                                        {{ $state }}
                                    </option>
                                @endforeach
                            </select>
                            @error('state')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Poskod</label>
                            <input type="text"
                                   name="postcode"
                                   value="{{ old('postcode', optional($address)->postcode) }}"
                                   class="form-input"
                                   maxlength="5"
                                   inputmode="numeric"
                                   pattern="[0-9]*"
                                   data-postcode-input>
                            @error('postcode')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Bandar</label>
                            <input type="text"
                                   name="city"
                                   value="{{ old('city', optional($address)->city) }}"
                                   class="form-input">
                            @error('city')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Negara</label>
                            <select name="country"
                                    class="form-input"
                                    data-country-select>
                                @foreach ($countryOptions as $country)
                                    <option value="{{ $country }}" @selected($selectedCountry === $country)>
                                        {{ $country }}
                                    </option>
                                @endforeach
                            </select>
                            @error('country')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2 {{ $selectedCountry === 'Lain-lain' ? '' : 'hidden' }}"
                             data-country-other-wrap>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Sila nyatakan negara</label>
                            <input type="text"
                                   name="country_other"
                                   value="{{ $countryOtherValue }}"
                                   placeholder="Sila nyatakan negara"
                                   class="form-input"
                                   data-country-other
                                   @disabled($selectedCountry !== 'Lain-lain')>
                            @error('country_other')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>

                {{-- ================= SECTION: DOKUMEN ================= --}}
                <section id="section-document" class="bg-white border rounded-lg p-6">
                    <div class="flex items-start justify-between gap-4 mb-5">
                        <div>
                            <h3 class="text-base font-semibold text-gray-800">Dokumen Sokongan</h3>
                            <p class="text-sm text-gray-500 mt-1">
                                Muat naik dokumen baharu jika mahu menggantikan dokumen sedia ada.
                            </p>
                        </div>

                        <span class="px-3 py-1 rounded-full bg-blue-50 text-blue-600 text-xs font-semibold">
                            PDF/JPG/PNG
                        </span>
                    </div>

                    @if ($donor->support_document)
                        <div class="mb-4 rounded-lg border bg-gray-50 px-4 py-3">
                            <a href="{{ asset('storage/' . $donor->support_document) }}"
                               target="_blank"
                               rel="noopener"
                               class="text-sm font-semibold text-blue-600 hover:underline">
                                Lihat Dokumen Sedia Ada
                            </a>
                        </div>
                    @endif

                    <input type="file"
                           name="support_document"
                           accept="application/pdf,image/jpeg,image/png,.pdf,.jpg,.jpeg,.png"
                           class="form-input">

                    <p class="text-xs text-gray-500 mt-2">
                        Format dibenarkan: PDF, JPG, JPEG atau PNG.
                    </p>

                    @error('support_document')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </section>

                {{-- ================= SECTION: LOGO ================= --}}
                <section id="section-logo" class="bg-white border rounded-lg p-6">
                    <div class="flex items-start justify-between gap-4 mb-5">
                        <div>
                            <h3 class="text-base font-semibold text-gray-800">Logo Penderma</h3>
                            <p class="text-sm text-gray-500 mt-1">
                                Logo ini akan digunakan pada kad homepage jika penderma dipaparkan.
                            </p>
                        </div>

                        <span class="px-3 py-1 rounded-full bg-blue-50 text-blue-600 text-xs font-semibold">
                            Image
                        </span>
                    </div>

                    @if ($donor->logo)
                        <div class="mb-4">
                            <img src="{{ asset('storage/' . $donor->logo) }}"
                                 alt="Logo {{ $user->name }}"
                                 class="w-32 h-32 object-contain border rounded-lg bg-white p-3 shadow-sm">
                        </div>
                    @endif

                    <input type="file"
                           name="logo"
                           accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp"
                           class="form-input">

                    <p class="text-xs text-gray-500 mt-2">
                        Format dibenarkan: JPG, PNG, WEBP.
                    </p>

                    @error('logo')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </section>

                {{-- ================= SECTION: HOMEPAGE ================= --}}
                <section id="section-homepage" class="bg-white border rounded-lg p-6">
                    <div class="flex items-start justify-between gap-4 mb-5">
                        <div>
                            <h3 class="text-base font-semibold text-gray-800">
                                Tetapan Homepage
                            </h3>

                            <p class="text-sm text-gray-500 mt-1">
                                Tetapkan penderma ini untuk dipaparkan di halaman utama.
                            </p>
                        </div>

                        <span class="px-3 py-1 rounded-full bg-blue-50 text-blue-600 text-xs font-semibold">
                            Optional
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Ranking
                            </label>

                            <input type="number"
                                   name="homepage_order"
                                   value="{{ old('homepage_order', $donor->homepage_order ?: 1) }}"
                                   min="1"
                                   class="form-input">

                            <p class="text-xs text-gray-500 mt-2">
                                Nombor lebih kecil akan dipaparkan dahulu di homepage.
                            </p>

                            @error('homepage_order')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2 flex items-center gap-3 bg-gray-50 border rounded-lg px-4 py-3">
                            <input type="checkbox"
                                   name="show_on_homepage"
                                   value="1"
                                   @checked((bool) $showOnHomepage)
                                   class="w-5 h-5 rounded border-gray-300 text-blue-600">

                            <div>
                                <label class="text-sm font-medium text-gray-800">
                                    Paparkan di Homepage
                                </label>

                                <p class="text-xs text-gray-500">
                                    Jika ditanda, penderma ini akan muncul di bahagian sokongan komuniti homepage.
                                </p>
                            </div>
                        </div>

                        @error('show_on_homepage')
                            <p class="md:col-span-2 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </section>

                {{-- ================= SECTION: CATATAN ================= --}}
                <section id="section-note" class="bg-white border rounded-lg p-6">
                    <h3 class="text-base font-semibold text-gray-800 mb-4">Catatan Admin</h3>

                    <textarea name="admin_note"
                              rows="4"
                              class="form-input"
                              placeholder="Catatan dalaman admin">{{ old('admin_note', $donor->admin_note) }}</textarea>

                    @error('admin_note')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </section>

                {{-- ================= BUTTON ================= --}}
                <div class="flex justify-end gap-3 pt-6">
                    <a href="{{ route('admin.penderma.index') }}"
                       class="px-6 py-2 border rounded-md bg-white text-gray-700 hover:bg-gray-50">
                        Batal
                    </a>

                    <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </main>
    </div>
</div>

<script>
const sections = document.querySelectorAll('section');
const navItems = document.querySelectorAll('.nav-item');

navItems.forEach(link => {
    link.addEventListener('click', e => {
        e.preventDefault();

        document.querySelector(link.getAttribute('href'))
            .scrollIntoView({ behavior: 'smooth' });
    });
});

window.addEventListener('scroll', () => {
    let current = '';

    sections.forEach(section => {
        const rect = section.getBoundingClientRect();

        if (rect.top <= 150 && rect.bottom >= 150) {
            current = section.id;
        }
    });

    navItems.forEach(link => {
        link.classList.remove('bg-blue-50', 'text-blue-600', 'font-semibold', 'border-blue-600');

        if (link.getAttribute('href') === '#' + current) {
            link.classList.add('bg-blue-50', 'text-blue-600', 'font-semibold', 'border-blue-600');
        }
    });
});

document.querySelectorAll('[data-phone-input]').forEach(input => {
    input.addEventListener('input', () => {
        input.value = input.value.replace(/\D/g, '').slice(0, 11);
    });
});

document.querySelectorAll('[data-postcode-input]').forEach(input => {
    input.addEventListener('input', () => {
        input.value = input.value.replace(/\D/g, '').slice(0, 5);
    });
});

function syncCountryOther() {
    const countrySelect = document.querySelector('[data-country-select]');
    const otherWrap = document.querySelector('[data-country-other-wrap]');
    const otherInput = document.querySelector('[data-country-other]');

    if (!countrySelect || !otherWrap || !otherInput) {
        return;
    }

    const isOther = countrySelect.value === 'Lain-lain';
    otherWrap.classList.toggle('hidden', !isOther);
    otherInput.disabled = !isOther;

    if (!isOther) {
        otherInput.value = '';
    }
}

document.querySelector('[data-country-select]')?.addEventListener('change', syncCountryOther);
syncCountryOther();

const validationErrorFields = new Set(@json(array_keys($errors->getMessages())));

document.querySelectorAll('input[name], select[name], textarea[name]').forEach(field => {
    if (validationErrorFields.has(field.name)) {
        field.classList.add('is-invalid');
        field.setAttribute('aria-invalid', 'true');
    }
});
</script>

<style>
    .form-input {
        width: 100%;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        background-color: #ffffff;
    }

    .form-input:focus {
        outline: 2px solid transparent;
        outline-offset: 2px;
        border-color: #2563eb;
        box-shadow: 0 0 0 1px #2563eb;
    }

    .form-input.is-invalid {
        border-color: #dc2626;
        box-shadow: 0 0 0 1px #dc2626;
    }

    .form-input.is-invalid:focus {
        border-color: #dc2626;
        box-shadow: 0 0 0 1px #dc2626;
    }
</style>

@endsection
