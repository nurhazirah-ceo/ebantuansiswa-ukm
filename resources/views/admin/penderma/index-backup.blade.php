@extends('layouts.admin')

@section('page-title', 'Senarai Penderma')
@section('page-subtitle', 'Urus akaun penderma, status jemputan dan paparan homepage.')

@section('content')

@php
    $states = [
        'Johor',
        'Kedah',
        'Kelantan',
        'Melaka',
        'Negeri Sembilan',
        'Pahang',
        'Perak',
        'Perlis',
        'Pulau Pinang',
        'Sabah',
        'Sarawak',
        'Selangor',
        'Terengganu',
        'Wilayah Persekutuan Kuala Lumpur',
        'Wilayah Persekutuan Putrajaya',
        'Wilayah Persekutuan Labuan',
    ];

    $storageUrl = fn ($path) => $path ? asset('storage/' . ltrim((string) $path, '/')) : '';

    $oldEditingUserId = old('_editing_user_id');
    $oldEditingDonor = $oldEditingUserId
        ? $donors->first(fn ($item) => (string) optional(optional($item)->user)->id === (string) $oldEditingUserId)
        : null;
    $oldEditingUser = optional($oldEditingDonor)->user;
    $oldEditingAddress = optional($oldEditingDonor)->address;

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
        'homepage' => old('show_on_homepage', optional($oldEditingDonor)->show_on_homepage ? '1' : '0'),
        'homepageLabel' => old('homepage_label', optional($oldEditingDonor)->homepage_label),
        'ranking' => old('homepage_order', optional($oldEditingDonor)->homepage_order),
        'status' => old('account_status', optional($oldEditingUser)->account_status),
        'adminNote' => old('admin_note', optional($oldEditingDonor)->admin_note),
        'logoUrl' => $storageUrl(optional($oldEditingDonor)->logo),
        'supportDocumentUrl' => $storageUrl(optional($oldEditingDonor)->support_document),
    ] : null;
@endphp

<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        @if (session('success'))
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if (session('info'))
            <div class="mb-4 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-medium text-blue-700">
                {{ session('info') }}
            </div>
        @endif

        {{-- SEARCH --}}
        <div class="mb-4">
            <form method="GET" action="{{ route('admin.penderma.index') }}">
                <input type="text"
                       name="search"
                       value="{{ $search ?? '' }}"
                       placeholder="Cari nama atau emel penderma..."
                       class="w-full md:w-1/3 border rounded-lg px-5 py-2.5 text-sm
                              focus:outline-none focus:ring-2 focus:ring-black focus:border-black">
            </form>
        </div>

        {{-- TABLE --}}
        <div class="bg-white shadow rounded-lg overflow-x-auto">
            <table class="min-w-full border border-gray-200 divide-y divide-gray-200">

                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">
                            Logo
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">
                            Nama
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">
                            Homepage
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">
                            Ranking
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">
                            Emel
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">
                            Tarikh Daftar
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">
                            Status
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">
                            Tindakan
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    @forelse ($donors as $donor)
                        @php
                            $user = optional($donor)->user;
                            $address = optional($donor)->address;
                            $userId = optional($user)->id;
                            $accountStatus = optional($user)->account_status;
                            $logoUrl = $storageUrl(optional($donor)->logo);
                            $supportDocumentUrl = $storageUrl(optional($donor)->support_document);

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
                                'homepage' => optional($donor)->show_on_homepage ? '1' : '0',
                                'homepageLabel' => optional($donor)->homepage_label,
                                'ranking' => optional($donor)->homepage_order ?: '',
                                'status' => $accountStatus,
                                'adminNote' => optional($donor)->admin_note,
                                'logoUrl' => $logoUrl,
                                'supportDocumentUrl' => $supportDocumentUrl,
                            ];
                        @endphp

                        <tr class="hover:bg-gray-50 transition">

                            {{-- LOGO --}}
                            <td class="px-6 py-4">
                                @if ($logoUrl)
                                    <img src="{{ $logoUrl }}"
                                         class="w-14 h-14 object-cover rounded-xl border">
                                @else
                                    <div class="w-14 h-14 rounded-xl bg-gray-100
                                                flex items-center justify-center
                                                text-xs text-gray-400 border">
                                        Tiada
                                    </div>
                                @endif
                            </td>

                            {{-- NAMA --}}
                            <td class="px-6 py-4 text-sm text-gray-900 font-medium">
                                {{ optional($user)->name ?? '-' }}
                            </td>

                            {{-- HOMEPAGE --}}
                            <td class="px-6 py-4">
                                @if (optional($donor)->show_on_homepage)
                                    <span class="inline-flex items-center px-3 py-1 text-xs
                                                 font-medium text-green-700 bg-green-100 rounded-full">
                                        Dipaparkan
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 text-xs
                                                 font-medium text-gray-700 bg-gray-100 rounded-full">
                                        Tidak Dipapar
                                    </span>
                                @endif
                            </td>

                            {{-- RANKING --}}
                            <td class="px-6 py-4 text-sm font-semibold text-gray-700">
                                @if (optional($donor)->homepage_order)
                                    #{{ optional($donor)->homepage_order }}
                                @else
                                    -
                                @endif
                            </td>

                            {{-- EMAIL --}}
                            <td class="px-6 py-4 text-sm text-gray-700">
                                {{ optional($user)->email ?? '-' }}
                            </td>

                            {{-- TARIKH --}}
                            <td class="px-6 py-4 text-sm text-gray-700">
                                {{ optional(optional($user)->created_at)->format('d M Y') ?? '-' }}
                            </td>

                            {{-- STATUS --}}
                            <td class="px-6 py-4">
                                @switch($accountStatus)

                                    @case('pending')
                                        <span class="inline-flex items-center px-3 py-1 text-xs
                                                     font-medium text-yellow-700 bg-yellow-100 rounded-full">
                                            Menunggu Kelulusan
                                        </span>
                                        @break

                                    @case('invited')
                                        <span class="inline-flex items-center px-3 py-1 text-xs
                                                     font-medium text-blue-700 bg-blue-100 rounded-full">
                                            Menunggu Pengesahan Emel
                                        </span>
                                        @break

                                    @case('active')
                                        <span class="inline-flex items-center px-3 py-1 text-xs
                                                     font-medium text-green-700 bg-green-100 rounded-full">
                                            Aktif
                                        </span>
                                        @break

                                    @default
                                        <span class="inline-flex items-center px-3 py-1 text-xs
                                                     font-medium text-gray-700 bg-gray-100 rounded-full">
                                            Tidak Diketahui
                                        </span>

                                @endswitch
                            </td>

                            {{-- TINDAKAN --}}
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap items-center gap-2">

                                    @if ($userId)

                                {{-- LIHAT --}}
                                <a href="{{ route('admin.penderma.show', $userId) }}"
                                class="px-3 py-1.5 text-xs font-medium
                                        text-slate-700 bg-slate-50 border border-slate-200
                                        rounded hover:bg-slate-100 transition">

                                    Lihat

                                </a>

                                {{-- EDIT --}}
                                <button type="button"
                                        class="edit-penderma-button px-3 py-1.5 text-xs font-medium
                                               text-amber-700 bg-amber-50 border border-amber-200
                                               rounded hover:bg-amber-100 transition"
                                        data-penderma='@json($editPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)'>

                                    Edit

                                </button>

                                    {{-- PENDING → HANTAR JEMPUTAN --}}
                                    @if ($accountStatus === 'pending')
                                        <form method="POST"
                                              action="{{ route('admin.penderma.activate', $userId) }}"
                                              class="activate-form">
                                            @csrf

                                            <button type="submit"
                                                    class="px-3 py-1.5 text-xs font-medium
                                                           text-blue-700 bg-blue-50 border border-blue-200
                                                           rounded hover:bg-blue-100 transition">
                                                Hantar Jemputan
                                            </button>
                                        </form>
                                    @endif

                                    {{-- INVITED → RESEND --}}
                                    @if ($accountStatus === 'invited')
                                        <form method="POST"
                                              action="{{ route('admin.penderma.resend', $userId) }}"
                                              class="resend-form">
                                            @csrf

                                            <button type="submit"
                                                    class="px-3 py-1.5 text-xs font-medium
                                                           text-indigo-700 bg-indigo-50 border border-indigo-200
                                                           rounded hover:bg-indigo-100 transition">
                                                Hantar Semula
                                            </button>
                                        </form>
                                    @endif

                                    

                                    {{-- DELETE --}}
                                    <form method="POST"
                                          action="{{ route('admin.penderma.destroy', $userId) }}"
                                          class="delete-form">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="px-3 py-1.5 text-xs font-medium
                                                       text-red-700 bg-red-50 border border-red-200
                                                       rounded hover:bg-red-100 transition">
                                            Delete
                                        </button>
                                    </form>
                                    @else
                                        <span class="px-3 py-1.5 text-xs font-medium text-gray-500">
                                            Tidak tersedia
                                        </span>
                                    @endif

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="8"
                                class="px-6 py-8 text-center text-gray-500">
                                Tiada penderma dijumpai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

    </div>
</div>

{{-- EDIT MODAL --}}
<div id="editPendermaModal"
     class="fixed inset-0 z-50 hidden overflow-y-auto"
     aria-labelledby="editPendermaTitle"
     role="dialog"
     aria-modal="true">
    <div class="flex min-h-full items-start justify-center px-4 py-8 sm:px-6">
        <div class="fixed inset-0 bg-slate-900/50 transition-opacity"
             data-close-edit-modal></div>

        <div class="relative w-full max-w-4xl rounded-lg bg-white shadow-xl">
            <div class="flex items-start justify-between gap-4 border-b px-6 py-4">
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

            <form id="editPendermaForm"
                  method="POST"
                  action=""
                  enctype="multipart/form-data"
                  class="max-h-[78vh] overflow-y-auto px-6 py-5">
                @csrf
                @method('PUT')

                <input type="hidden" name="_editing_user_id" id="editUserId" value="{{ old('_editing_user_id') }}">

                @if ($errors->any())
                    <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        Sila semak semula maklumat yang dimasukkan.
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
                                       class="modal-input">
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
                                       class="modal-input">
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
                                <label class="mb-2 block text-sm font-medium text-gray-700">Negara</label>
                                <input type="text"
                                       value="Malaysia"
                                       readonly
                                       class="modal-input bg-gray-100">
                            </div>

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
                               accept="image/*"
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
                               accept="application/pdf,.pdf"
                               class="modal-input">

                        @error('support_document')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </section>

                    <section class="rounded-lg border p-5 lg:col-span-2">
                        <h3 class="mb-4 text-base font-semibold text-gray-800">Tetapan Homepage</h3>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label for="editHomepageLabel" class="mb-2 block text-sm font-medium text-gray-700">
                                    Label Homepage
                                </label>
                                <input type="text"
                                       name="homepage_label"
                                       id="editHomepageLabel"
                                       class="modal-input"
                                       placeholder="Contoh: Penderma Tertinggi">
                                @error('homepage_label')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="editRanking" class="mb-2 block text-sm font-medium text-gray-700">
                                    Ranking
                                </label>
                                <input type="number"
                                       name="homepage_order"
                                       id="editRanking"
                                       min="1"
                                       class="modal-input">
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

                <div class="sticky bottom-0 -mx-6 mt-6 flex justify-end gap-3 border-t bg-white px-6 py-4">
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
        address1: document.getElementById('editAddress1'),
        address2: document.getElementById('editAddress2'),
        homepageLabel: document.getElementById('editHomepageLabel'),
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
        setFieldValue(fields.address1, valueOf(data, 'address1'));
        setFieldValue(fields.address2, valueOf(data, 'address2'));
        setFieldValue(fields.homepageLabel, valueOf(data, 'homepageLabel'));
        setFieldValue(fields.ranking, valueOf(data, 'ranking'));
        if (fields.showOnHomepage) {
            fields.showOnHomepage.checked = ['1', 'true', 'on'].includes(String(valueOf(data, 'homepage')).toLowerCase());
        }
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

const oldEditPayload = {!! json_encode($oldEditPayload) !!};    if (oldEditPayload) {
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
</style>

@endsection
