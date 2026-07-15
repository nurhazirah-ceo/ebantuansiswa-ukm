@extends('layouts.admin')

@section('page-title', 'Senarai Penderma')
@section('page-subtitle', 'Urus akaun penderma, status jemputan dan paparan homepage.')

@section('content')

@php
    $states = \App\Http\Controllers\Admin\DonorController::stateOptions();
    $countryOptions = ['Malaysia', 'Singapore', 'Indonesia', 'Thailand', 'Brunei', 'Lain-lain'];
    $storageUrl = fn ($path) => $path ? asset('storage/' . ltrim((string) $path, '/')) : '';

    $oldEditingUserId = old('_editing_user_id');
    $oldEditingDonor = $oldEditingUserId
        ? $donors->first(fn ($item) => (string) optional(optional($item)->user)->id === (string) $oldEditingUserId)
        : null;
    $oldEditingUser = optional($oldEditingDonor)->user;
    $oldEditingAddress = optional($oldEditingDonor)->address;
    $oldCountry = old('country', optional($oldEditingAddress)->country ?: 'Malaysia');
    $oldSelectedCountry = in_array($oldCountry, $countryOptions, true) ? $oldCountry : 'Lain-lain';
    $oldCountryOther = old('country_other', $oldSelectedCountry === 'Lain-lain' ? $oldCountry : '');
    $oldHomepage = old('show_on_homepage', optional($oldEditingDonor)->show_on_homepage ? '1' : '0');
    $oldShowOnHomepage = in_array(strtolower((string) $oldHomepage), ['1', 'true', 'on'], true);

    $oldEditPayload = $oldEditingUserId ? [
        'id' => $oldEditingUserId,
        'updateUrl' => route('admin.penderma.update', $oldEditingUserId),
        'nama' => old('name', optional($oldEditingUser)->name),
        'emel' => old('email', optional($oldEditingUser)->email),
        'phone' => old('phone', optional($oldEditingDonor)->phone),
        'representativeName' => old('representative_name', optional($oldEditingDonor)->representative_name),
        'preferredContact' => old('preferred_contact', optional($oldEditingDonor)->preferred_contact),
        'address1' => old('address_line_1', optional($oldEditingAddress)->address_line_1),
        'address2' => old('address_line_2', optional($oldEditingAddress)->address_line_2),
        'city' => old('city', optional($oldEditingAddress)->city),
        'postcode' => old('postcode', optional($oldEditingAddress)->postcode),
        'state' => old('state', optional($oldEditingAddress)->state),
        'country' => $oldSelectedCountry,
        'countryOther' => $oldCountryOther,
        'homepage' => $oldHomepage,
        'ranking' => $oldShowOnHomepage ? old('homepage_order', optional($oldEditingDonor)->homepage_order) : '',
        'status' => old('account_status', optional($oldEditingUser)->account_status),
        'adminNote' => old('admin_note', optional($oldEditingDonor)->admin_note),
        'logoUrl' => $storageUrl(optional($oldEditingDonor)->logo),
        'supportDocumentUrl' => $storageUrl(optional($oldEditingDonor)->support_document),
    ] : null;
@endphp

<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <x-page-hero
    class="mb-6"
    eyebrow="Penderma"
    title="Senarai Penderma"
    description="Pantau maklumat penderma berdaftar dan status akaun penderma dalam sistem."
/>

        @if (session('success'))
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                {{ session('error') }}
            </div>
        @endif

        @if (session('info'))
            <div class="mb-4 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-medium text-blue-700">
                {{ session('info') }}
            </div>
        @endif

{{-- SEARCH + TABLE CARD --}}
<section class="overflow-hidden rounded-[1.5rem] bg-white shadow-lg ring-1 ring-slate-200/70">

    {{-- CARD HEADER --}}
    <div class="bg-[#071633] px-4 py-4 text-white">
<div class="flex justify-end">            
            <form method="GET" action="{{ route('admin.penderma.index') }}" class="w-full lg:max-w-md">
                <div class="flex items-center gap-3 rounded-2xl border border-white/15 bg-white/10 px-4 py-3 shadow-inner">
                    <svg class="h-5 w-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/>
                    </svg>

                    <input type="text"
                           name="search"
                           value="{{ $search ?? '' }}"
                           placeholder="Cari nama atau emel penderma..."
                           class="w-full border-0 bg-transparent text-sm text-white placeholder:text-slate-300 focus:outline-none focus:ring-0">

                    <button type="submit"
                            class="rounded-xl bg-white px-5 py-2 text-sm font-semibold text-[#071633] transition hover:bg-slate-100">
                        Cari
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Logo</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Nama</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Homepage</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Ranking</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Emel</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Tarikh Daftar</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Tindakan</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 bg-white">
                @forelse ($donors as $donor)
                    @php
                        $user = optional($donor)->user;
                        $address = optional($donor)->address;
                        $userId = optional($user)->id;
                        $accountStatus = optional($user)->account_status;
                        $logoUrl = $storageUrl(optional($donor)->logo);
                        $supportDocumentUrl = $storageUrl(optional($donor)->support_document);
                        $rawCountry = optional($address)->country ?: 'Malaysia';
                        $selectedCountry = in_array($rawCountry, $countryOptions, true) ? $rawCountry : 'Lain-lain';
                        $countryOther = $selectedCountry === 'Lain-lain' ? $rawCountry : '';

                        $editPayload = [
                            'id' => $userId,
                            'updateUrl' => $userId ? route('admin.penderma.update', $userId) : '',
                            'nama' => optional($user)->name,
                            'emel' => optional($user)->email,
                            'phone' => optional($donor)->phone,
                            'representativeName' => optional($donor)->representative_name,
                            'preferredContact' => optional($donor)->preferred_contact,
                            'address1' => optional($address)->address_line_1,
                            'address2' => optional($address)->address_line_2,
                            'city' => optional($address)->city,
                            'postcode' => optional($address)->postcode,
                            'state' => optional($address)->state,
                            'country' => $selectedCountry,
                            'countryOther' => $countryOther,
                            'homepage' => optional($donor)->show_on_homepage ? '1' : '0',
                            'ranking' => optional($donor)->show_on_homepage ? optional($donor)->homepage_order : '',
                            'status' => $accountStatus,
                            'adminNote' => optional($donor)->admin_note,
                            'logoUrl' => $logoUrl,
                            'supportDocumentUrl' => $supportDocumentUrl,
                        ];
                    @endphp

                    <tr class="transition hover:bg-slate-50/80">
                        <td class="px-6 py-5">
                            @if ($logoUrl)
                                <img src="{{ $logoUrl }}"
                                     class="h-16 w-16 rounded-2xl border border-slate-200 bg-white object-contain p-2 shadow-sm">
                            @else
                                <div class="flex h-16 w-16 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 text-xs font-medium text-slate-400">
                                    Tiada
                                </div>
                            @endif
                        </td>

                        <td class="px-6 py-5">
                            <p class="text-sm font-semibold text-slate-900">
                                {{ optional($user)->name ?? '-' }}
                            </p>
                        </td>

                        <td class="px-6 py-5">
                            @if (optional($donor)->show_on_homepage)
                                <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                    Dipaparkan
                                </span>
                            @else
                                <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                    Tidak Dipapar
                                </span>
                            @endif
                        </td>

                        <td class="px-6 py-5 text-sm font-semibold text-slate-700">
                            {{ optional($donor)->homepage_order ? '#' . optional($donor)->homepage_order : '-' }}
                        </td>

                        <td class="px-6 py-5 text-sm text-slate-600">
                            {{ optional($user)->email ?? '-' }}
                        </td>

                        <td class="px-6 py-5 text-sm text-slate-600">
                            {{ optional(optional($user)->created_at)->format('d M Y') ?? '-' }}
                        </td>

                        <td class="px-6 py-5">
                            @switch($accountStatus)
                                @case('pending')
                                    <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                                        Menunggu
                                    </span>
                                    @break

                                @case('invited')
                                    <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">
                                        Jemputan
                                    </span>
                                    @break

                                @case('active')
                                    <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                        Aktif
                                    </span>
                                    @break

                                @default
                                    <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                        Tidak Diketahui
                                    </span>
                            @endswitch
                        </td>

                        <td class="px-6 py-5">
                            <div class="flex flex-wrap items-center gap-2">
                                @if ($userId)
                                    <a href="{{ route('admin.penderma.show', $userId) }}"
                                       class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                                        Lihat
                                    </a>

                                    <button type="button"
                                            class="edit-penderma-button rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700 transition hover:bg-amber-100"
                                            data-penderma='@json($editPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)'>
                                        Edit
                                    </button>

                                    @if (in_array($accountStatus, ['pending', 'invited'], true))
                                        <form method="POST"
                                              action="{{ route('admin.penderma.resend', $userId) }}"
                                              data-confirm
                                              data-confirm-title="Hantar email pengesahan?"
                                              data-confirm-text="Email pengesahan akan dihantar kepada penderma ini."
                                              data-confirm-button="Ya, hantar">
                                            @csrf

                                            <button type="submit"
                                                    class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 transition hover:bg-blue-100">
                                                {{ $accountStatus === 'pending' ? 'Hantar Email' : 'Hantar Semula Email' }}
                                            </button>
                                        </form>
                                    @endif

                                    <form method="POST"
                                          action="{{ route('admin.penderma.destroy', $userId) }}"
                                          class="delete-form"
                                          data-confirm
                                          data-confirm-icon="warning"
                                          data-confirm-title="Padam penderma?"
                                          data-confirm-text="Akaun dan maklumat penderma ini akan dipadam."
                                          data-confirm-button="Ya, padam"
                                          data-confirm-color="#dc2626">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">
                                            Delete
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs font-medium text-slate-400">
                                        Tidak tersedia
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="mx-auto max-w-md rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-8">
                                <p class="text-sm font-semibold text-slate-700">Tiada penderma dijumpai.</p>
                                <p class="mt-2 text-sm text-slate-500">
                                    {{ filled($search ?? null) ? 'Cuba gunakan kata kunci carian yang lain.' : 'Penderma yang didaftarkan akan dipaparkan di sini.' }}
                                </p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

{{-- EDIT MODAL --}}
<div id="editPendermaModal"
     class="fixed inset-0 z-50 hidden overflow-hidden"
     aria-labelledby="editPendermaTitle"
     role="dialog"
     aria-modal="true">
    <div class="relative z-10 flex min-h-screen items-start justify-center px-4 py-4 sm:px-6 lg:py-8">
        <div class="fixed inset-0 bg-slate-900/50 transition-opacity"
             data-close-edit-modal></div>

        <div class="relative z-10 flex max-h-[calc(100vh-2rem)] w-full max-w-5xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-slate-900/10 lg:max-h-[calc(100vh-4rem)]">
            <form id="editPendermaForm"
                  method="POST"
                  action=""
                  enctype="multipart/form-data"
                  class="flex min-h-0 flex-1 flex-col"
                  data-confirm
                  data-confirm-title="Simpan perubahan penderma?"
                  data-confirm-text="Maklumat penderma akan dikemaskini."
                  data-confirm-button="Ya, simpan">
                <div class="flex shrink-0 items-start justify-between gap-4 border-b bg-white px-6 py-4">
                    <div>
                        <h2 id="editPendermaTitle" class="text-lg font-semibold text-gray-900">
                            Edit Penderma
                        </h2>
                    </div>

                    <button type="button"
                            class="rounded-md px-2 py-1 text-xl leading-none text-gray-400 hover:bg-gray-100 hover:text-gray-700"
                            data-close-edit-modal
                            aria-label="Tutup modal">
                        &times;
                    </button>
                </div>

                @csrf
                @method('PUT')

                <input type="hidden" name="_editing_user_id" id="editUserId" value="{{ old('_editing_user_id') }}">

                <div class="min-h-0 flex-1 overflow-y-auto px-6 py-5">

                @if ($errors->any())
                    <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <p class="font-semibold">Sila semak semula maklumat yang dimasukkan.</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <section class="rounded-lg border p-5">
                        <h3 class="mb-4 text-base font-semibold text-gray-800">Maklumat Penderma</h3>

                        <div class="space-y-4">
                            <div>
                                <label for="editName" class="mb-2 block text-sm font-medium text-gray-700">Nama</label>
                                <input type="text"
                                       name="name"
                                       id="editName"
                                       class="modal-input"
                                       required>
                                @error('name')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="editEmail" class="mb-2 block text-sm font-medium text-gray-700">Emel</label>
                                <input type="email"
                                       name="email"
                                       id="editEmail"
                                       class="modal-input"
                                       required>
                                @error('email')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="editPhone" class="mb-2 block text-sm font-medium text-gray-700">No Telefon</label>
                                <input type="text"
                                       name="phone"
                                       id="editPhone"
                                       class="modal-input"
                                       maxlength="11"
                                       inputmode="numeric"
                                       pattern="[0-9]*"
                                       autocomplete="tel"
                                       required
                                       data-phone-input>
                                @error('phone')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="editRepresentativeName" class="mb-2 block text-sm font-medium text-gray-700">Nama Wakil Organisasi</label>
                                <input type="text"
                                       name="representative_name"
                                       id="editRepresentativeName"
                                       class="modal-input">
                                @error('representative_name')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">
                                    Saluran Komunikasi Pilihan
                                </label>

                                <div class="flex min-h-[42px] items-center gap-6">
                                    <label class="flex items-center gap-2 text-sm text-gray-700">
                                        <input type="radio"
                                               name="preferred_contact"
                                               value="email"
                                               class="text-blue-600 border-gray-300">
                                        Email
                                    </label>

                                    <label class="flex items-center gap-2 text-sm text-gray-700">
                                        <input type="radio"
                                               name="preferred_contact"
                                               value="phone"
                                               class="text-blue-600 border-gray-300">
                                        Telefon
                                    </label>
                                </div>

                                @error('preferred_contact')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="editStatus" class="mb-2 block text-sm font-medium text-gray-700">Status</label>
                                <select name="account_status"
                                        id="editStatus"
                                        class="modal-input">
                                    <option value="pending">Menunggu Kelulusan</option>
                                    <option value="invited">Menunggu Pengesahan Emel</option>
                                    <option value="active">Aktif</option>
                                </select>
                                @error('account_status')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </section>

                    <section class="rounded-lg border p-5">
                        <h3 class="mb-4 text-base font-semibold text-gray-800">Alamat</h3>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label for="editAddress1" class="mb-2 block text-sm font-medium text-gray-700">Alamat Baris 1</label>
                                <input type="text"
                                       name="address_line_1"
                                       id="editAddress1"
                                       class="modal-input">
                                @error('address_line_1')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label for="editAddress2" class="mb-2 block text-sm font-medium text-gray-700">Alamat Baris 2</label>
                                <input type="text"
                                       name="address_line_2"
                                       id="editAddress2"
                                       class="modal-input">
                                @error('address_line_2')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="editState" class="mb-2 block text-sm font-medium text-gray-700">Negeri</label>
                                <select name="state" id="editState" class="modal-input">
                                    <option value="">Pilih Negeri</option>
                                    @foreach ($states as $state)
                                        <option value="{{ $state }}">{{ $state }}</option>
                                    @endforeach
                                </select>
                                @error('state')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="editPostcode" class="mb-2 block text-sm font-medium text-gray-700">Poskod</label>
                                <input type="text"
                                       name="postcode"
                                       id="editPostcode"
                                       class="modal-input"
                                       maxlength="5"
                                       inputmode="numeric"
                                       pattern="[0-9]*"
                                       data-postcode-input>
                                @error('postcode')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="editCity" class="mb-2 block text-sm font-medium text-gray-700">Bandar</label>
                                <input type="text"
                                       name="city"
                                       id="editCity"
                                       class="modal-input">
                                @error('city')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="editCountry" class="mb-2 block text-sm font-medium text-gray-700">Negara</label>
                                <select name="country"
                                        id="editCountry"
                                        class="modal-input">
                                    @foreach ($countryOptions as $country)
                                        <option value="{{ $country }}">{{ $country }}</option>
                                    @endforeach
                                </select>
                                @error('country')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div id="editCountryOtherWrap" class="hidden sm:col-span-2">
                                <label for="editCountryOther" class="mb-2 block text-sm font-medium text-gray-700">Sila nyatakan negara</label>
                                <input type="text"
                                       name="country_other"
                                       id="editCountryOther"
                                       placeholder="Sila nyatakan negara"
                                       class="modal-input">
                                @error('country_other')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </section>

                    <section class="rounded-lg border p-5">
                        <h3 class="mb-4 text-base font-semibold text-gray-800">Logo Penderma</h3>

                        <div class="mb-4">
                            <img id="editLogoPreview"
                                 src=""
                                 alt="Logo penderma"
                                 class="hidden h-28 w-28 rounded-lg border bg-gray-50 object-contain p-3">

                            <div id="editLogoEmpty"
                                 class="flex h-28 w-28 items-center justify-center rounded-lg border bg-gray-50 text-xs text-gray-400">
                                Tiada Logo
                            </div>
                        </div>

                        <input type="file"
                               name="logo"
                               id="editLogo"
                               accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp"
                               class="modal-input">

                        @error('logo')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </section>

                    <section class="rounded-lg border p-5">
                        <h3 class="mb-4 text-base font-semibold text-gray-800">Dokumen Sokongan</h3>

                        <div id="editSupportDocumentWrap"
                             class="mb-4 hidden rounded-lg border bg-gray-50 px-4 py-3">
                            <a id="editSupportDocumentLink"
                               href="#"
                               target="_blank"
                               rel="noopener"
                               class="text-sm font-semibold text-blue-600 hover:underline">
                                Lihat Dokumen Sedia Ada
                            </a>
                        </div>

                        <input type="file"
                               name="support_document"
                               id="editSupportDocument"
                               accept="application/pdf,image/jpeg,image/png,.pdf,.jpg,.jpeg,.png"
                               class="modal-input">

                        <p class="mt-2 text-xs text-gray-500">
                            Format dibenarkan: PDF, JPG, JPEG atau PNG.
                        </p>

                        @error('support_document')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </section>

                    <section class="rounded-lg border p-5 lg:col-span-2">
                        <h3 class="mb-4 text-base font-semibold text-gray-800">Tetapan Homepage</h3>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label for="editRanking" class="mb-2 block text-sm font-medium text-gray-700">
                                    Ranking
                                </label>
                                <input type="number"
                                        name="homepage_order"
                                        id="editRanking"
                                        min="1"
                                        inputmode="numeric"
                                        placeholder="Contoh: 1"
                                        class="modal-input">
                                <p class="mt-2 text-xs text-gray-500">
                                    Nombor lebih kecil akan dipaparkan dahulu di homepage.
                                </p>
                                @error('homepage_order')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2 flex items-center gap-3 rounded-lg border bg-gray-50 px-4 py-3">
                                <input type="hidden" name="show_on_homepage" value="0">
                                <input type="checkbox"
                                       name="show_on_homepage"
                                       id="editShowOnHomepage"
                                       value="1"
                                       class="h-5 w-5 rounded border-gray-300 text-blue-600">

                                <div>
                                    <label for="editShowOnHomepage" class="text-sm font-medium text-gray-800">
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

                    <section class="rounded-lg border p-5 lg:col-span-2">
                        <h3 class="mb-4 text-base font-semibold text-gray-800">Catatan Admin</h3>

                        <textarea name="admin_note"
                                  id="editAdminNote"
                                  rows="4"
                                  class="modal-input"
                                  placeholder="Catatan dalaman admin"></textarea>

                        @error('admin_note')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </section>
                </div>

                </div>

                <div class="flex shrink-0 justify-end gap-3 border-t bg-white px-6 py-4">
                    <button type="button"
                            class="rounded-md border bg-white px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            data-close-edit-modal>
                        Batal
                    </button>

                    <button type="submit"
                            class="rounded-md bg-blue-600 px-5 py-2 text-sm font-medium text-white transition hover:bg-blue-700">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('editPendermaModal');
    const form = document.getElementById('editPendermaForm');

    if (!modal || !form) {
        return;
    }

    const editButtons = document.querySelectorAll('.edit-penderma-button');
    const closeButtons = document.querySelectorAll('[data-close-edit-modal]');

    const fields = {
        userId: document.getElementById('editUserId'),
        name: document.getElementById('editName'),
        email: document.getElementById('editEmail'),
        phone: document.getElementById('editPhone'),
        representativeName: document.getElementById('editRepresentativeName'),
        status: document.getElementById('editStatus'),
        state: document.getElementById('editState'),
        postcode: document.getElementById('editPostcode'),
        city: document.getElementById('editCity'),
        country: document.getElementById('editCountry'),
        countryOtherWrap: document.getElementById('editCountryOtherWrap'),
        countryOther: document.getElementById('editCountryOther'),
        address1: document.getElementById('editAddress1'),
        address2: document.getElementById('editAddress2'),
        ranking: document.getElementById('editRanking'),
        showOnHomepage: document.getElementById('editShowOnHomepage'),
        adminNote: document.getElementById('editAdminNote'),
        logo: document.getElementById('editLogo'),
        logoPreview: document.getElementById('editLogoPreview'),
        logoEmpty: document.getElementById('editLogoEmpty'),
        supportDocument: document.getElementById('editSupportDocument'),
        supportDocumentWrap: document.getElementById('editSupportDocumentWrap'),
        supportDocumentLink: document.getElementById('editSupportDocumentLink'),
    };

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

    function valueOf(data, key) {
        if (!data || typeof data !== 'object' || data[key] === undefined || data[key] === null) {
            return '';
        }

        return data[key];
    }

    function setFieldValue(field, value) {
        if (field) {
            field.value = value === undefined || value === null ? '' : value;
        }
    }

    function setSelectValue(field, value, fallback = '') {
        if (!field) {
            return;
        }

        const selectedValue = String(value ?? '');
        const hasOption = Array.from(field.options).some(option => option.value === selectedValue);

        field.value = hasOption ? selectedValue : fallback;
    }

    function syncCountryOther() {
        if (!fields.country || !fields.countryOtherWrap || !fields.countryOther) {
            return;
        }

        const isOther = fields.country.value === 'Lain-lain';
        fields.countryOtherWrap.classList.toggle('hidden', !isOther);
        fields.countryOther.disabled = !isOther;

        if (!isOther) {
            fields.countryOther.value = '';
        }
    }

    function syncHomepageRanking(clearWhenDisabled = true) {
        if (!fields.showOnHomepage || !fields.ranking) {
            return;
        }

        const enabled = fields.showOnHomepage.checked;
        fields.ranking.disabled = !enabled;
        fields.ranking.required = enabled;

        if (!enabled && clearWhenDisabled) {
            fields.ranking.value = '';
        }
    }

    function safeUrl(url) {
        const value = String(url || '').trim();

        if (!value || value === '#') {
            return '';
        }

        try {
            const parsedUrl = new URL(value, window.location.origin);

            if (!['http:', 'https:'].includes(parsedUrl.protocol)) {
                return '';
            }

            return parsedUrl.href;
        } catch (error) {
            return '';
        }
    }

    function payloadFromButton(button) {
        const payload = button.dataset.penderma || '{}';

        try {
            const parsedPayload = JSON.parse(payload);

            return parsedPayload && typeof parsedPayload === 'object' ? parsedPayload : {};
        } catch (error) {
            return {};
        }
    }

    function setPreferredContact(value) {
        const selectedValue = ['email', 'phone'].includes(String(value)) ? String(value) : 'email';

        form.querySelectorAll('input[name="preferred_contact"]').forEach(input => {
            input.checked = input.value === selectedValue;
        });
    }

    function setExistingLogo(url) {
        const logoUrl = safeUrl(url);

        if (logoUrl && fields.logoPreview && fields.logoEmpty) {
            fields.logoPreview.src = logoUrl;
            fields.logoPreview.classList.remove('hidden');
            fields.logoEmpty.classList.add('hidden');
            return;
        }

        if (fields.logoPreview) {
            fields.logoPreview.removeAttribute('src');
            fields.logoPreview.classList.add('hidden');
        }

        if (fields.logoEmpty) {
            fields.logoEmpty.classList.remove('hidden');
        }
    }

    function setExistingSupportDocument(url) {
        const documentUrl = safeUrl(url);

        if (documentUrl && fields.supportDocumentLink && fields.supportDocumentWrap) {
            fields.supportDocumentLink.href = documentUrl;
            fields.supportDocumentWrap.classList.remove('hidden');
            return;
        }

        if (fields.supportDocumentLink) {
            fields.supportDocumentLink.href = '#';
        }

        if (fields.supportDocumentWrap) {
            fields.supportDocumentWrap.classList.add('hidden');
        }
    }

    function fillModal(data) {
        const updateUrl = safeUrl(valueOf(data, 'updateUrl'));

        if (!updateUrl) {
            return false;
        }

        form.action = updateUrl;
        setFieldValue(fields.userId, valueOf(data, 'id'));
        setFieldValue(fields.name, valueOf(data, 'nama'));
        setFieldValue(fields.email, valueOf(data, 'emel'));
        setFieldValue(fields.phone, valueOf(data, 'phone'));
        setFieldValue(fields.representativeName, valueOf(data, 'representativeName'));
        setSelectValue(fields.status, valueOf(data, 'status'), 'pending');
        setSelectValue(fields.state, valueOf(data, 'state'));
        setFieldValue(fields.postcode, valueOf(data, 'postcode'));
        setFieldValue(fields.city, valueOf(data, 'city'));
        setSelectValue(fields.country, valueOf(data, 'country'), 'Malaysia');
        setFieldValue(fields.countryOther, valueOf(data, 'countryOther'));
        syncCountryOther();
        setFieldValue(fields.address1, valueOf(data, 'address1'));
        setFieldValue(fields.address2, valueOf(data, 'address2'));
        setFieldValue(fields.ranking, valueOf(data, 'ranking'));

        if (fields.showOnHomepage) {
            fields.showOnHomepage.checked = ['1', 'true', 'on'].includes(String(valueOf(data, 'homepage')).toLowerCase());
        }
        syncHomepageRanking();
        setFieldValue(fields.adminNote, valueOf(data, 'adminNote'));
        setFieldValue(fields.logo, '');
        setFieldValue(fields.supportDocument, '');

        setPreferredContact(valueOf(data, 'preferredContact'));
        setExistingLogo(valueOf(data, 'logoUrl'));
        setExistingSupportDocument(valueOf(data, 'supportDocumentUrl'));

        return true;
    }

    function openModal(data) {
        if (!fillModal(data)) {
            return;
        }

        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');

        if (fields.name) {
            fields.name.focus();
        }
    }

    function closeModal() {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    if (fields.country) {
        fields.country.addEventListener('change', syncCountryOther);
    }

    if (fields.showOnHomepage) {
        fields.showOnHomepage.addEventListener('change', () => syncHomepageRanking());
    }

    editButtons.forEach(button => {
        button.addEventListener('click', () => openModal(payloadFromButton(button)));
    });

    closeButtons.forEach(button => {
        button.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });

    const oldEditPayload = {!! json_encode($oldEditPayload) !!};
    const validationErrorFields = new Set(@json(array_keys($errors->getMessages())));

    form.querySelectorAll('input[name], select[name], textarea[name]').forEach(field => {
        if (validationErrorFields.has(field.name)) {
            field.classList.add('is-invalid');
            field.setAttribute('aria-invalid', 'true');
        }
    });

    if (oldEditPayload) {
        openModal(oldEditPayload);
    }
});
</script>

<style>
    .modal-input {
        width: 100%;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        background-color: #ffffff;
    }

    .modal-input:focus {
        outline: 2px solid transparent;
        outline-offset: 2px;
        border-color: #2563eb;
        box-shadow: 0 0 0 1px #2563eb;
    }

    .modal-input.is-invalid {
        border-color: #dc2626;
        box-shadow: 0 0 0 1px #dc2626;
    }

    .modal-input.is-invalid:focus {
        border-color: #dc2626;
        box-shadow: 0 0 0 1px #dc2626;
    }
</style>

@endsection
