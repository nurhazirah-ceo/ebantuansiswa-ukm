@extends('layouts.admin')

@section('page-title', 'Semak Permohonan')
@section('page-subtitle', 'Panel semakan dan keputusan permohonan bantuan pelajar.')

@section('content')
@php
    $formatLabel = fn ($value) => filled($value)
        ? \Illuminate\Support\Str::of($value)->replace(['_', '-'], ' ')->squish()->title()
        : '-';

    $pelajar = $permohonan->pelajar;
    $bantuan = $permohonan->bantuan;
    $statusLabel = $permohonan->lewat_diproses ? 'Lewat Diproses' : $permohonan->status_label;
    $statusClass = $permohonan->lewat_diproses
        ? 'bg-indigo-100 text-indigo-700'
        : $permohonan->status_badge_class;
    $reviewDate = $permohonan->admin_review_date
        ? $permohonan->admin_review_date->format('d/m/Y h:i A')
        : '-';
    $approvalToAgihan = session('approval_to_agihan');
    $approvedForAgihan = $permohonan->status_key === \App\Models\Permohonan::STATUS_DILULUSKAN;
@endphp

<div class="min-h-screen bg-slate-50 py-8">
    <div class="mx-auto max-w-7xl px-6">
        <div class="mb-4">
            <a href="{{ route('admin.permohonan.index') }}"
               class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100">
                Kembali
            </a>
        </div>

        <x-page-hero
            class="mb-6"
            eyebrow="Panel Semakan"
            title="Semak Permohonan Pelajar"
            description="Semak maklumat lengkap, dokumen sokongan dan rekodkan keputusan rasmi permohonan."
        />

        @foreach(['success' => 'bg-emerald-50 text-emerald-800 border-emerald-200', 'warning' => 'bg-amber-50 text-amber-800 border-amber-200', 'info' => 'bg-blue-50 text-blue-800 border-blue-200'] as $flashKey => $flashClass)
            @if(session($flashKey) && !($approvalToAgihan && $flashKey === 'success'))
                <div class="mb-5 rounded-2xl border px-5 py-4 text-sm font-medium {{ $flashClass }}">
                    {{ session($flashKey) }}
                </div>
            @endif
        @endforeach

        @if($errors->any())
            <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800">
                <p class="font-semibold">Sila semak semula maklumat keputusan.</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-[1.3fr_0.9fr]">
            <div class="space-y-6">
                <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="bg-[#071633] px-6 py-5 text-white">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <p class="text-sm text-slate-300">No Kelompok</p>
                                <h2 class="mt-1 text-2xl font-bold">{{ $permohonan->no_kelompok ?? '-' }}</h2>
                            </div>

                            <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-bold {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                        </div>
                    </div>

                    <div class="grid gap-5 p-6 md:grid-cols-2">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">No Matrik</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $pelajar?->no_matrik ?? $permohonan->user?->matrik ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Nama Pelajar</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $pelajar?->nama_penuh ?? $permohonan->user?->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Fakulti</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $pelajar?->fakulti ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Sesi Akademik</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $pelajar?->tahun_pengajian ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Jenis Bantuan</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $formatLabel($bantuan?->jenis_bantuan ?? $permohonan->jenis_bantuan) }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Kategori Bantuan</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $formatLabel($bantuan?->kategori_bantuan) }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Tarikh Mohon</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $permohonan->tarikh_mohon ? $permohonan->tarikh_mohon->format('d/m/Y') : '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Tarikh Semakan Admin</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $reviewDate }}</p>
                        </div>
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-950">Maklumat Pelajar</h2>
                    <div class="mt-5 grid gap-4 md:grid-cols-2">
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Email UKM</p>
                            <p class="mt-1 text-sm font-medium text-slate-800">{{ $pelajar?->email_ukm ?? $permohonan->user?->email ?? '-' }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">No Telefon</p>
                            <p class="mt-1 text-sm font-medium text-slate-800">{{ $pelajar?->no_telefon ?? '-' }}</p>
                        </div>
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-950">Maklumat Keluarga</h2>
                    <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-100 text-slate-700">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold">Jenis</th>
                                    <th class="px-4 py-3 text-left font-semibold">Nama</th>
                                    <th class="px-4 py-3 text-left font-semibold">Hubungan</th>
                                    <th class="px-4 py-3 text-left font-semibold">Telefon</th>
                                    <th class="px-4 py-3 text-left font-semibold">Pekerjaan</th>
                                    <th class="px-4 py-3 text-right font-semibold">Pendapatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse($permohonan->keluarga as $keluarga)
                                    <tr>
                                        <td class="px-4 py-3 text-slate-700">{{ $formatLabel($keluarga->jenis) }}</td>
                                        <td class="px-4 py-3 font-medium text-slate-900">{{ $keluarga->nama ?? '-' }}</td>
                                        <td class="px-4 py-3 text-slate-700">{{ $keluarga->hubungan ?? '-' }}</td>
                                        <td class="px-4 py-3 text-slate-700">{{ $keluarga->telefon ?? '-' }}</td>
                                        <td class="px-4 py-3 text-slate-700">{{ $keluarga->pekerjaan ?? '-' }}</td>
                                        <td class="px-4 py-3 text-right text-slate-700">RM {{ number_format((float) $keluarga->pendapatan, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-10 text-center">
                                            <div class="mx-auto max-w-md rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-7">
                                                <p class="text-sm font-semibold text-slate-700">Tiada maklumat keluarga direkodkan.</p>
                                                <p class="mt-2 text-sm text-slate-500">Maklumat keluarga akan dipaparkan jika pelajar mengisi bahagian ini.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-950">Justifikasi Permohonan Pelajar</h2>
                    <div class="mt-4 rounded-2xl border border-blue-100 bg-blue-50 p-5 text-sm leading-6 text-slate-700 whitespace-pre-line">
                        {{ filled($studentJustifikasi) ? $studentJustifikasi : 'Tiada justifikasi khusus direkodkan dalam borang permohonan.' }}
                    </div>
                </section>
            </div>

            <aside class="space-y-6">
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-950">Senarai Dokumen Sokongan</h2>
                    <div class="mt-5 space-y-3">
                        @forelse($permohonan->dokumens as $dokumen)
                            <x-permohonan-document-card :dokumen="$dokumen" />
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center">
                                <p class="text-sm font-semibold text-slate-700">Tiada dokumen sokongan direkodkan.</p>
                                <p class="mt-2 text-sm text-slate-500">Dokumen sokongan pelajar akan dipaparkan di sini jika tersedia.</p>
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-950">Keputusan Admin</h2>

                    @if($canReview)
                        <form method="POST"
                              action="{{ route('admin.permohonan.keputusan', $permohonan) }}"
                              id="adminDecisionForm"
                              class="mt-5 space-y-5"
                              novalidate
                              data-confirm
                              data-confirm-title="Hantar keputusan permohonan?"
                              data-confirm-text="Keputusan lulus atau tolak akan direkodkan dan dimaklumkan kepada pelajar."
                              data-confirm-button="Ya, hantar keputusan">
                            @csrf
                            @method('PATCH')

                            <div>
                                <p class="mb-3 text-sm font-semibold text-slate-700">Keputusan</p>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <label class="cursor-pointer rounded-2xl border border-emerald-200 bg-emerald-50 p-4 transition hover:border-emerald-400">
                                        <input type="radio" name="keputusan" value="Diluluskan" class="text-emerald-600 focus:ring-emerald-500" @checked(old('keputusan') === 'Diluluskan')>
                                        <span class="ml-2 text-sm font-bold text-emerald-700">Luluskan Permohonan</span>
                                    </label>

                                    <label class="cursor-pointer rounded-2xl border border-rose-200 bg-rose-50 p-4 transition hover:border-rose-400">
                                        <input type="radio" name="keputusan" value="Ditolak" class="text-rose-600 focus:ring-rose-500" @checked(old('keputusan') === 'Ditolak')>
                                        <span class="ml-2 text-sm font-bold text-rose-700">Tolak Permohonan</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label for="decision_reason" class="block text-sm font-semibold text-slate-700">
                                    Sebab Keputusan
                                </label>
                                <select id="decision_reason"
                                        name="decision_reason"
                                        class="mt-3 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Pilih keputusan terlebih dahulu</option>
                                </select>
                                <p class="mt-2 text-xs text-slate-500">
                                    Pilih sebab untuk auto-isi justifikasi. Catatan masih boleh diedit selepas itu.
                                </p>
                            </div>

                            <div>
                                <label for="admin_catatan" class="block text-sm font-semibold text-slate-700">
                                    Catatan / Justifikasi Admin
                                    <span class="text-rose-500">*</span>
                                </label>
                                <textarea id="admin_catatan"
                                          name="admin_catatan"
                                          rows="6"
                                          class="mt-3 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                          placeholder="Nyatakan justifikasi keputusan dengan jelas.">{{ old('admin_catatan') }}</textarea>
                            </div>

                            <button type="submit"
                                    class="inline-flex w-full items-center justify-center rounded-2xl bg-[#071633] px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#102544]">
                                Hantar Keputusan
                            </button>
                        </form>
                    @else
                        <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <p class="text-sm text-slate-500">Keputusan telah direkodkan.</p>
                            <p class="mt-2 text-xl font-bold text-slate-950">{{ $permohonan->status_label }}</p>
                            <p class="mt-4 text-sm font-semibold text-slate-700">Catatan Admin</p>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $permohonan->admin_catatan ?? $permohonan->catatan ?? '-' }}</p>
                            <p class="mt-4 text-xs text-slate-400">Tarikh semakan: {{ $reviewDate }}</p>
                        </div>

                        @if($approvedForAgihan)
                            <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700">Aliran Seterusnya</p>
                                <h3 class="mt-2 text-base font-bold text-slate-950">Permohonan masuk ke proses agihan bantuan</h3>
                                <div class="mt-4 flex items-center gap-3 text-xs font-semibold text-slate-600">
                                    <span class="rounded-full bg-emerald-600 px-3 py-1 text-white">Diluluskan</span>
                                    <span class="h-px flex-1 bg-emerald-200"></span>
                                    <span class="rounded-full bg-blue-100 px-3 py-1 text-blue-700">Menunggu Agihan</span>
                                </div>
                                <a href="{{ route('admin.agihan.index') }}"
                                   class="mt-5 inline-flex w-full items-center justify-center rounded-2xl bg-[#071633] px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#102544]">
                                    Ke Agihan Bantuan
                                </a>
                            </div>
                        @endif
                    @endif
                </section>
            </aside>
        </div>
    </div>
</div>

@if($approvalToAgihan)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'success',
                title: 'Permohonan Diluluskan',
                text: 'Permohonan berjaya diluluskan dan dimasukkan ke proses agihan bantuan.',
                confirmButtonText: 'Ke Agihan',
                cancelButtonText: 'Tutup',
                showCancelButton: true,
                confirmButtonColor: '#071633',
                cancelButtonColor: '#64748b'
            }).then(function (result) {
                if (result.isConfirmed) {
                    window.location.href = @json(route('admin.agihan.index'));
                }
            });
        });
    </script>
@endif

@if($canReview)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('adminDecisionForm');
            const reasonSelect = document.getElementById('decision_reason');
            const catatanField = document.getElementById('admin_catatan');
            const oldReason = @json(old('decision_reason'));

            if (!form || !reasonSelect || !catatanField) {
                return;
            }

            const decisionReasons = {
                Diluluskan: [
                    {
                        value: 'Memenuhi syarat kelayakan bantuan',
                        text: 'Memenuhi syarat kelayakan bantuan',
                        catatan: 'Tahniah! Permohonan anda telah diluluskan kerana memenuhi syarat kelayakan bantuan dan dokumen yang dikemukakan adalah lengkap.'
                    },
                    {
                        value: 'Pendapatan keluarga berada dalam kategori layak',
                        text: 'Pendapatan keluarga berada dalam kategori layak',
                        catatan: 'Tahniah! Permohonan anda telah diluluskan kerana pendapatan keluarga berada dalam kategori layak untuk menerima bantuan.'
                    },
                    {
                        value: 'Dokumen lengkap dan disahkan',
                        text: 'Dokumen lengkap dan disahkan',
                        catatan: 'Tahniah! Permohonan anda telah diluluskan kerana dokumen sokongan yang dikemukakan adalah lengkap dan telah disahkan.'
                    },
                    {
                        value: 'Lain-lain',
                        text: 'Lain-lain',
                        catatan: 'Tahniah! Permohonan anda telah diluluskan setelah semakan pentadbir mendapati permohonan ini wajar dipertimbangkan berdasarkan maklumat yang dikemukakan.'
                    }
                ],
                Ditolak: [
                    {
                        value: 'Dokumen tidak lengkap',
                        text: 'Dokumen tidak lengkap',
                        catatan: 'Dukacita dimaklumkan bahawa permohonan anda tidak dapat diluluskan kerana dokumen yang dikemukakan tidak lengkap.'
                    },
                    {
                        value: 'Maklumat permohonan tidak mencukupi',
                        text: 'Maklumat permohonan tidak mencukupi',
                        catatan: 'Dukacita dimaklumkan bahawa permohonan anda tidak dapat diluluskan kerana maklumat permohonan yang dikemukakan tidak mencukupi untuk semakan kelayakan.'
                    },
                    {
                        value: 'Tidak memenuhi syarat kelayakan',
                        text: 'Tidak memenuhi syarat kelayakan',
                        catatan: 'Dukacita dimaklumkan bahawa permohonan anda tidak dapat diluluskan kerana tidak memenuhi syarat kelayakan bantuan yang ditetapkan.'
                    },
                    {
                        value: 'Permohonan bertindih dengan bantuan lain',
                        text: 'Permohonan bertindih dengan bantuan lain',
                        catatan: 'Dukacita dimaklumkan bahawa permohonan anda tidak dapat diluluskan kerana permohonan ini bertindih dengan bantuan lain yang telah direkodkan.'
                    },
                    {
                        value: 'Lain-lain',
                        text: 'Lain-lain',
                        catatan: 'Dukacita dimaklumkan bahawa permohonan anda tidak dapat diluluskan setelah semakan pentadbir dibuat berdasarkan maklumat yang dikemukakan.'
                    }
                ]
            };

            const autoFillTexts = Object.values(decisionReasons)
                .flat()
                .map(reason => reason.catatan);

            function selectedDecision() {
                return form.querySelector('input[name="keputusan"]:checked')?.value || '';
            }

            function isAutoFilledText(value) {
                return autoFillTexts.includes((value || '').trim());
            }

            function populateReasons(decision, selectedReason = '') {
                const currentCatatan = catatanField.value.trim();
                const hadAutoFill = isAutoFilledText(currentCatatan);

                reasonSelect.innerHTML = '';

                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = decision ? 'Pilih Sebab Keputusan' : 'Pilih keputusan terlebih dahulu';
                reasonSelect.appendChild(placeholder);
                reasonSelect.disabled = !decision;

                (decisionReasons[decision] || []).forEach(function (reason) {
                    const option = document.createElement('option');
                    option.value = reason.value;
                    option.textContent = reason.text;
                    option.dataset.catatan = reason.catatan;
                    reasonSelect.appendChild(option);
                });

                reasonSelect.value = selectedReason || '';

                if (hadAutoFill && !selectedReason) {
                    catatanField.value = '';
                }
            }

            form.querySelectorAll('input[name="keputusan"]').forEach(function (radio) {
                radio.addEventListener('change', function () {
                    populateReasons(radio.value);
                });
            });

            reasonSelect.addEventListener('change', function () {
                const selectedOption = reasonSelect.options[reasonSelect.selectedIndex];
                const catatan = selectedOption?.dataset.catatan || '';

                if (catatan) {
                    catatanField.value = catatan;
                    catatanField.focus();
                }
            });

            form.addEventListener('submit', function (event) {
                const decision = selectedDecision();
                const catatan = catatanField.value.trim();

                if (!decision || !catatan) {
                    event.preventDefault();
                    event.stopPropagation();

                    Swal.fire({
                        icon: 'warning',
                        title: 'Maklumat keputusan belum lengkap',
                        text: !decision
                            ? 'Sila pilih keputusan permohonan terlebih dahulu.'
                            : 'Sila isi Catatan / Justifikasi Admin sebelum menghantar keputusan.',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#071633'
                    }).then(function () {
                        if (!decision) {
                            form.querySelector('input[name="keputusan"]')?.focus();
                        } else {
                            catatanField.focus();
                        }
                    });
                }
            });

            populateReasons(selectedDecision(), oldReason);
        });
    </script>
@endif
@endsection
