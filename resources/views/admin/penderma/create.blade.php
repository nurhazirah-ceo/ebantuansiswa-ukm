@extends('layouts.admin')

@section('page-title', 'Daftar Penderma')
@section('page-subtitle', 'Lengkapkan maklumat penderma di bawah.')

@section('content')

@php
    $countryOptions = ['Malaysia', 'Singapore', 'Indonesia', 'Thailand', 'Brunei', 'Lain-lain'];
    $selectedCountry = old('country', 'Malaysia');
@endphp

<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-6xl mx-auto px-6 space-y-6">
        <x-page-hero
            class="w-full"
            eyebrow="PENDERMA"
            title="Daftar Penderma"
            description="Daftarkan akaun penderma baharu dan hantar maklumat pengesahan akses kepada penderma."
        />

        {{-- ================= FORM ================= --}}
        <main>

            @if ($errors->has('error'))
                <div class="mb-4 rounded bg-red-100 text-red-700 px-4 py-3">
                    {{ $errors->first('error') }}
                </div>
            @endif

            @if ($errors->any() && ! $errors->has('error'))
                <div class="mb-4 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <p class="font-semibold">Sila semak semula maklumat penderma.</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST"
                  action="{{ route('admin.penderma.store') }}"
                  enctype="multipart/form-data"
                  id="donorForm"
                  class="space-y-6"
                  data-confirm
                  data-confirm-title="Daftar penderma baharu?"
                  data-confirm-text="Akaun penderma akan diwujudkan dengan maklumat yang dimasukkan."
                  data-confirm-button="Ya, daftar">
                @csrf

                {{-- ================= SECTION: JENIS ================= --}}
                <section id="section-type" class="bg-white border rounded-lg p-6">
                    <h3 class="text-base font-semibold text-gray-800 mb-4">Jenis Penderma</h3>

                    <select name="donor_type"
                            id="donorType"
                            required
                            class="w-full md:w-1/2 border rounded-md px-3 py-2 text-sm">
                        <option value="">-- Pilih Jenis Penderma --</option>
                        <option value="individu" @selected(old('donor_type') === 'individu')>Individu</option>
                        <option value="syarikat" @selected(old('donor_type') === 'syarikat')>Syarikat</option>
                        <option value="ngo" @selected(old('donor_type') === 'ngo')>NGO</option>
                    </select>
                </section>

                {{-- ================= SECTION: INFO ================= --}}
                <section id="section-info" class="bg-white border rounded-lg p-6">
                    <h3 class="text-base font-semibold text-gray-800 mb-4">Maklumat Penderma</h3>

                    {{-- INDIVIDU --}}
                    <div id="form-individu" class="grid grid-cols-1 md:grid-cols-2 gap-4 hidden">
                        <div>
                            <label class="form-label">Nama Penuh</label>
                            <input name="name" value="{{ old('name') }}" class="input-required" placeholder="Nama Penuh">
                        </div>

                        <div class="space-y-1">
                            <label class="form-label">Emel</label>
                            <div class="email-error hidden text-red-600 text-xs"></div>

                            <input name="email"
                                   data-email-type="individu"
                                   value="{{ old('email') }}"
                                   class="input-required email-check"
                                   placeholder="Emel">
                        </div>

                        <div>
                            <label class="form-label">No Telefon</label>
                            <input name="phone"
                                   value="{{ old('phone') }}"
                                   class="input-required"
                                   placeholder="No Telefon"
                                   maxlength="11"
                                   inputmode="numeric"
                                   pattern="[0-9]*"
                                   autocomplete="tel"
                                   required
                                   data-phone-input>
                        </div>
                    </div>

                    {{-- SYARIKAT --}}
                    <div id="form-syarikat" class="grid grid-cols-1 md:grid-cols-2 gap-4 hidden">
                        <div>
                            <label class="form-label">Nama Syarikat</label>
                            <input name="company_name" value="{{ old('company_name') }}" class="input-required" placeholder="Nama Syarikat">
                        </div>

                        <div>
                            <label class="form-label">Nama Wakil Organisasi</label>
                            <input name="representative_name" value="{{ old('representative_name') }}" class="input-required" placeholder="Nama Wakil Organisasi">
                        </div>

                        <div class="space-y-1">
                            <label class="form-label">Emel Syarikat</label>
                            <div class="email-error hidden text-red-600 text-xs"></div>

                            <input name="company_email"
                                   data-email-type="syarikat"
                                   value="{{ old('company_email') }}"
                                   class="input-required email-check"
                                   placeholder="Emel Syarikat">
                        </div>

                        <div>
                            <label class="form-label">No Telefon Syarikat</label>
                            <input name="company_phone"
                                   value="{{ old('company_phone') }}"
                                   class="input-required"
                                   placeholder="Telefon"
                                   maxlength="11"
                                   inputmode="numeric"
                                   pattern="[0-9]*"
                                   autocomplete="tel"
                                   required
                                   data-phone-input>
                        </div>
                    </div>

                    {{-- NGO --}}
                    <div id="form-ngo" class="grid grid-cols-1 md:grid-cols-2 gap-4 hidden">
                        <div>
                            <label class="form-label">Nama NGO</label>
                            <input name="ngo_name" value="{{ old('ngo_name') }}" class="input-required" placeholder="Nama NGO">
                        </div>

                        <div>
                            <label class="form-label">Nama Wakil Organisasi</label>
                            <input name="representative_name" value="{{ old('representative_name') }}" class="input-required" placeholder="Nama Wakil Organisasi">
                        </div>

                        <div class="space-y-1">
                            <label class="form-label">Emel NGO</label>
                            <div class="email-error hidden text-red-600 text-xs"></div>

                            <input name="ngo_email"
                                   data-email-type="ngo"
                                   value="{{ old('ngo_email') }}"
                                   class="input-required email-check"
                                   placeholder="Emel NGO">
                        </div>

                        <div>
                            <label class="form-label">No Telefon NGO</label>
                            <input name="ngo_phone"
                                   value="{{ old('ngo_phone') }}"
                                   class="input-required"
                                   placeholder="Telefon NGO"
                                   maxlength="11"
                                   inputmode="numeric"
                                   pattern="[0-9]*"
                                   autocomplete="tel"
                                   required
                                   data-phone-input>
                        </div>
                    </div>

                    {{-- CONTACT --}}
                    <div class="pt-6">
                        <label class="block text-sm font-medium mb-2">Saluran Komunikasi Pilihan</label>

                        <div class="flex gap-6">
                            <label class="flex items-center gap-2">
                                <input type="radio" name="preferred_contact" value="email" @checked(old('preferred_contact') === 'email') required>
                                Email
                            </label>

                            <label class="flex items-center gap-2">
                                <input type="radio" name="preferred_contact" value="phone" @checked(old('preferred_contact') === 'phone')>
                                Telefon
                            </label>
                        </div>
                    </div>
                </section>

                {{-- ================= SECTION: ALAMAT ================= --}}
                <section id="section-address" class="bg-white border rounded-lg p-6">
                    <h3 class="text-base font-semibold text-gray-800 mb-4">Alamat</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="form-label">Alamat Baris 1</label>
                            <input name="address_line_1"
                                   value="{{ old('address_line_1') }}"
                                   placeholder="Alamat Baris 1"
                                   class="input-required">
                        </div>

                        <div class="md:col-span-2">
                            <label class="form-label">Alamat Baris 2 (Optional)</label>
                            <input name="address_line_2"
                                   value="{{ old('address_line_2') }}"
                                   placeholder="Alamat Baris 2"
                                   class="w-full border rounded-md px-3 py-2 text-sm">
                        </div>

                        <div>
                            <label class="form-label">Negeri</label>
                            <select name="state" class="input-required">
                                <option value="">Pilih Negeri</option>
                                <option @selected(old('state') === 'Johor')>Johor</option>
                                <option @selected(old('state') === 'Kedah')>Kedah</option>
                                <option @selected(old('state') === 'Kelantan')>Kelantan</option>
                                <option @selected(old('state') === 'Melaka')>Melaka</option>
                                <option @selected(old('state') === 'Negeri Sembilan')>Negeri Sembilan</option>
                                <option @selected(old('state') === 'Pahang')>Pahang</option>
                                <option @selected(old('state') === 'Perak')>Perak</option>
                                <option @selected(old('state') === 'Perlis')>Perlis</option>
                                <option @selected(old('state') === 'Pulau Pinang')>Pulau Pinang</option>
                                <option @selected(old('state') === 'Sabah')>Sabah</option>
                                <option @selected(old('state') === 'Sarawak')>Sarawak</option>
                                <option @selected(old('state') === 'Selangor')>Selangor</option>
                                <option @selected(old('state') === 'Terengganu')>Terengganu</option>
                                <option @selected(old('state') === 'Wilayah Persekutuan Kuala Lumpur')>Wilayah Persekutuan Kuala Lumpur</option>
                                <option @selected(old('state') === 'Wilayah Persekutuan Putrajaya')>Wilayah Persekutuan Putrajaya</option>
                                <option @selected(old('state') === 'Wilayah Persekutuan Labuan')>Wilayah Persekutuan Labuan</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Poskod</label>
                            <input name="postcode"
                                   value="{{ old('postcode') }}"
                                   placeholder="Poskod"
                                   class="input-required"
                                   maxlength="5"
                                   inputmode="numeric"
                                   pattern="[0-9]*"
                                   data-postcode-input>
                        </div>

                        <div>
                            <label class="form-label">Bandar</label>
                            <input name="city" value="{{ old('city') }}" placeholder="Bandar" class="input-required">
                        </div>

                        <div>
                            <label class="form-label">Negara</label>
                            <select name="country"
                                    class="input-required"
                                    data-country-select>
                                @foreach ($countryOptions as $country)
                                    <option value="{{ $country }}" @selected($selectedCountry === $country)>{{ $country }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-2 {{ $selectedCountry === 'Lain-lain' ? '' : 'hidden' }}"
                             data-country-other-wrap>
                            <label class="form-label">Sila nyatakan negara</label>
                            <input name="country_other"
                                   value="{{ old('country_other') }}"
                                   placeholder="Sila nyatakan negara"
                                   class="input-required"
                                   data-country-other
                                   @disabled($selectedCountry !== 'Lain-lain')>
                        </div>
                    </div>
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
                                Logo Penderma
                            </label>

                            <input type="file"
                                   id="logoInput"
                                   name="logo"
                                   accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp"
                                   class="w-full border rounded-md px-3 py-2 text-sm">

                            <p class="text-xs text-gray-500 mt-1">
                                Format dibenarkan: JPG, JPEG, PNG atau WEBP. Logo ini akan digunakan pada kad homepage.
                            </p>

                            @error('logo')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror

                            <div id="logoPreviewWrap" class="mt-4 hidden w-32 rounded-lg border bg-white p-3 shadow-sm">
                                <img
                                    id="logoPreview"
                                    src=""
                                    alt="Preview logo penderma"
                                    class="h-20 w-full rounded object-contain"
                                >
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Dokumen Sokongan
                            </label>

                            <input type="file"
                                   name="support_document"
                                   accept="application/pdf,image/jpeg,image/png,.pdf,.jpg,.jpeg,.png"
                                   class="w-full border rounded-md px-3 py-2 text-sm">

                            <p class="text-xs text-gray-500 mt-1">
                                Format dibenarkan: PDF, JPG, JPEG atau PNG.
                            </p>

                            @error('support_document')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Ranking
                            </label>

                            <input type="number"
                                   name="homepage_order"
                                   value="{{ old('homepage_order') }}"
                                   min="1"
                                   inputmode="numeric"
                                   placeholder="Contoh: 1"
                                   class="w-full border rounded-md px-3 py-2 text-sm">

                            <p class="text-xs text-gray-500 mt-1">
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
                                   @checked(old('show_on_homepage'))
                                   class="w-5 h-5 rounded border-gray-300">

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
                    <h3 class="text-base font-semibold text-gray-800 mb-4">Catatan Pentadbir (Optional)</h3>

                    <textarea name="admin_note"
                              rows="4"
                              class="w-full border rounded-md px-3 py-2 text-sm">{{ old('admin_note') }}</textarea>
                </section>

                {{-- ================= BUTTON ================= --}}
                <div class="flex justify-end gap-3 pt-6">
                    <a href="{{ route('admin.penderma.index') }}"
                       class="px-6 py-2 border rounded-md">
                        Batal
                    </a>

                    <button type="submit"
                            id="submitBtn"
                            disabled
                            class="px-6 py-2 bg-blue-600 text-white rounded-md opacity-50 cursor-not-allowed">
                        Simpan
                    </button>
                </div>

            </form>
        </main>
    </div>
</div>

<script>
/* ================= CORE ELEMENTS ================= */
const donorType = document.getElementById('donorType');
const submitBtn = document.getElementById('submitBtn');
const logoInput = document.getElementById('logoInput');
const logoPreviewWrap = document.getElementById('logoPreviewWrap');
const logoPreview = document.getElementById('logoPreview');

/* ================= FORM MAP ================= */
const forms = {
    individu: document.getElementById('form-individu'),
    syarikat: document.getElementById('form-syarikat'),
    ngo: document.getElementById('form-ngo'),
};

/* ================= STATE ================= */
let emailErrorActive = false;

/* ================= FORM SWITCH ================= */
function setFormEnabled(form, enabled) {
    if (!form) return;

    form.querySelectorAll('input, select, textarea').forEach(input => {
        input.disabled = !enabled;
    });
}

function showForm(type) {
    Object.values(forms).forEach(f => {
        f.classList.add('hidden');
        setFormEnabled(f, false);
    });

    if (forms[type]) {
        forms[type].classList.remove('hidden');
        setFormEnabled(forms[type], true);
    }

    validateForm();
}

donorType.addEventListener('change', () => {
    showForm(donorType.value);
});

/* ================= VALIDATION ================= */
function validateForm() {
    if (emailErrorActive) {
        disableSubmit();
        return;
    }

    if (!donorType.value) {
        disableSubmit();
        return;
    }

    const activeForm = forms[donorType.value];

    if (!activeForm) {
        disableSubmit();
        return;
    }

    const formInputs = activeForm.querySelectorAll('.input-required');
    const addressInputs = document.querySelectorAll('#section-address .input-required');
    const contactChecked = document.querySelector('input[name="preferred_contact"]:checked');

    const allFilled =
        [...formInputs].filter(i => !i.disabled).every(i => i.value.trim() !== '') &&
        [...addressInputs].filter(i => !i.disabled).every(i => i.value.trim() !== '') &&
        contactChecked;

    submitBtn.disabled = !allFilled;
    submitBtn.classList.toggle('opacity-50', !allFilled);
    submitBtn.classList.toggle('cursor-not-allowed', !allFilled);
}

function disableSubmit() {
    submitBtn.disabled = true;
    submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
}

document.addEventListener('input', validateForm);
document.addEventListener('change', validateForm);

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

    validateForm();
}

document.querySelector('[data-country-select]')?.addEventListener('change', syncCountryOther);
syncCountryOther();

/* ================= EMAIL LIVE CHECK ================= */
const emailInputs = document.querySelectorAll('.email-check');

emailInputs.forEach(input => {
    const errorBox = input.previousElementSibling;
    let timer = null;

    input.addEventListener('input', () => {
        clearTimeout(timer);

        timer = setTimeout(() => {
            const email = input.value.trim();
            const type = input.dataset.emailType;

            if (!email) {
                hideError(input, errorBox);
                emailErrorActive = false;
                validateForm();
                return;
            }

            fetch("{{ url('/admin/penderma/check-email') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ email, type })
            })
            .then(res => res.json())
            .then(data => {
                if (data.exists) {
                    emailErrorActive = true;
                    showError(input, errorBox, 'Emel ini telah digunakan oleh akaun lain');
                    disableSubmit();
                } else {
                    emailErrorActive = false;
                    hideError(input, errorBox);
                    validateForm();
                }
            });
        }, 500);
    });
});

function showError(input, box, msg) {
    box.textContent = msg;
    box.classList.remove('hidden');
    input.classList.add('border-red-500');
}

function hideError(input, box) {
    box.classList.add('hidden');
    input.classList.remove('border-red-500');
}

/* ================= LOGO PREVIEW ================= */
if (logoInput && logoPreviewWrap && logoPreview) {
    logoInput.addEventListener('change', () => {
        const file = logoInput.files?.[0];

        if (!file) {
            logoPreview.src = '';
            logoPreviewWrap.classList.add('hidden');
            return;
        }

        logoPreview.src = URL.createObjectURL(file);
        logoPreviewWrap.classList.remove('hidden');
    });
}

showForm(donorType.value);

</script>

<style>
    .form-label {
        display: block;
        margin-bottom: 0.375rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
    }

    .input-required {
        width: 100%;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }
</style>

@endsection
