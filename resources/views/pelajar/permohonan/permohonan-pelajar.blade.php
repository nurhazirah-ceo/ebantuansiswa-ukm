@extends('layouts.app')

@section('content')

<div class="max-w-6xl mx-auto px-6 py-8">

    <x-page-hero
        class="mb-8"
        eyebrow="Pelajar"
        title="Permohonan Bantuan"
        description="Sila lengkapkan permohonan anda."
    />

    @if ($errors->any())
        <div class="mb-8 rounded-2xl border border-red-200 bg-red-50 px-6 py-5 text-red-800" role="alert">
            <p class="font-semibold">Permohonan belum dapat dihantar. Sila semak maklumat berikut:</p>
            <ul class="mt-3 list-disc space-y-1 pl-5 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ================= STEPPER ================= --}}
    <div class="flex items-center justify-between mb-10 text-center">
        @for($i=1;$i<=4;$i++)
            <div class="flex flex-col items-center flex-1">
                <div class="step {{ $i==1 ? 'active' : '' }}" id="indicator-{{$i}}">
                    {{$i}}
                </div>
                <span class="mt-2 text-sm text-gray-600">
                    @if($i==1)
                        Maklumat Pelajar
                    @elseif($i==2)
                        Maklumat Keluarga
                    @elseif($i==3)
                        Jenis Bantuan
                    @else
                        Dokumen
                    @endif
                </span>
            </div>

            @if($i<4)
                <div class="line"></div>
            @endif
        @endfor
    </div>

    {{-- ================= FORM ================= --}}
    <form id="permohonanForm"
      method="POST"
      action="{{ route('permohonan.store') }}"
      enctype="multipart/form-data">
    @csrf
        <div id="familyHiddenInputs" class="hidden"></div>

        {{-- STEP 1 --}}
        @include('pelajar.permohonan.step1-maklumat-pelajar')

        {{-- STEP 2 --}}
        @include('pelajar.permohonan.step2-maklumat-keluarga')

        {{-- STEP 3 --}}
        @include('pelajar.permohonan.step3-jenis-bantuan')

        {{-- STEP 4 --}}
        @include('pelajar.permohonan.step4-dokumen')

        {{-- ================= PREMIUM STEP NAVIGATION ================= --}}

<div class="mt-10">
    <div class="bg-white border border-slate-200 rounded-3xl shadow-xl px-8 py-5">

        <!-- Top Progress Info -->
        <div class="flex items-center justify-between mb-5">

            <div>
                <p class="text-sm text-slate-500 font-medium">
                    Langkah <span id="currentStepLabel">1</span> daripada 4
                </p>
                <p class="text-xs text-slate-400 mt-1">
                    Lengkapkan semua maklumat sebelum menghantar permohonan
                </p>
            </div>

            <div class="text-sm font-semibold text-blue-700">
                <span id="progressPercent">25%</span>
            </div>

        </div>

        <!-- Progress Bar -->
        <div class="w-full bg-slate-200 rounded-full h-2 mb-6 overflow-hidden">
            <div id="progressBar"
                 class="bg-[#071633] h-2 rounded-full transition-all duration-500"
                 style="width:25%">
            </div>
        </div>

        <!-- Buttons -->
        <div class="flex items-center justify-between">

            <button type="button"
                id="backBtn"
                onclick="prevStep()"
                class="px-6 py-3 rounded-2xl border border-slate-300 text-slate-700 font-medium hover:bg-slate-100 transition hidden">

                ← Kembali
            </button>

            <button type="button"
                id="nextBtn"
                onclick="nextStep()"
                class="ml-auto px-8 py-3 rounded-2xl bg-[#071633] text-white font-semibold hover:bg-[#102544] hover:shadow-lg transition flex items-center gap-2">

                Seterusnya
                <span>→</span>
            </button>

            <button type="submit"
                id="submitBtn"
                class="hidden ml-auto px-8 py-3 rounded-2xl bg-green-600 text-white font-semibold hover:bg-green-700 transition flex items-center gap-2">

                Hantar Permohonan
                <span>✓</span>
            </button>

        </div>
    </div>
</div>

    </form>

{{-- ================= STYLE ================= --}}
<style>
.step{
    width:40px;
    height:40px;
    border-radius:50%;
    border:2px solid #3b82f6;
    display:flex;
    align-items:center;
    justify-content:center;
    color:#3b82f6;
    font-weight:600;
}

.step.active{
    background:#3b82f6;
    color:white;
}

.line{
    flex:1;
    height:3px;
    background:#93c5fd;
    margin:0 10px;
}
</style>

{{-- ================= SCRIPT ================= --}}
<script>
let currentStep = 1;
let totalSteps = 4;
let tanggunganIndex = 0;
let familyMembers = [];
const initialBantuanSelection = {
    jenis: @json(old('jenis_bantuan', $selectedJenis ?? null)),
    kategori: @json(old('kategori_bantuan', $selectedKategori ?? null)),
    item: @json($selectedItem ?? null)
};
const JUSTIFIKASI_RINGKAS_REQUIRED_MESSAGE = 'Sila isi Justifikasi Ringkas sebelum meneruskan permohonan.';

function showStepWarning(title, text) {
    Swal.fire({
        icon: 'warning',
        title: title,
        text: text,
        confirmButtonColor: '#071633'
    });
}

function normalizeMatricValue(value) {
    value = (value || '')
        .toUpperCase()
        .replace(/[^A-Z0-9]/g, '');

    if (value === '') {
        return '';
    }

    let aIndex = value.indexOf('A');

    if (aIndex === -1) {
        return '';
    }

    value = value.slice(aIndex);

    return 'A' + value.slice(1).replace(/[^0-9]/g, '').slice(0, 6);
}

function formatMatricInput(input) {
    if (!input) return;

    input.value = normalizeMatricValue(input.value);
}

function isValidMatric(value) {
    return /^A[0-9]{6}$/.test((value || '').trim().toUpperCase());
}

function setContainerDisabled(container, disabled) {
    if (!container) return;

    container.querySelectorAll('input, select, textarea, button').forEach(el => {
        el.disabled = disabled;
    });
}

function syncItemQuantityState(container) {
    if (!container) return;

    container.querySelectorAll('input[type="number"]').forEach(input => {
        let checkbox = input.closest('tr')?.querySelector('input[type="checkbox"]');

        if (checkbox) {
            input.disabled = checkbox.disabled || !checkbox.checked;
        }
    });
}

function toggleSectionEnabled(section, enabled) {
    if (!section) return;

    section.classList.toggle('hidden', !enabled);
    setContainerDisabled(section, !enabled);

    if (enabled) {
        syncItemQuantityState(section);
    }
}

function getActiveBantuanForm() {
    let kategoriSelect = document.getElementById('kategori_bantuan');
    let selectedOption = kategoriSelect?.options[kategoriSelect.selectedIndex];
    let kategori = getSelectedKategoriBantuan();
    let formId = selectedOption?.dataset.form || '';

    if (!formId) {
        let formMap = {
            keperluan_asas: 'form-keperluan-asas',
            alat_tulis_pembelajaran: 'form-pembelajaran',
            peralatan_pembelajaran: 'form-peralatan',
            sukan: 'form-sukan'
        };

        formId = formMap[kategori] || '';
    }

    let selectedForm = formId ? document.getElementById(formId) : null;

    if (selectedForm && !selectedForm.classList.contains('hidden')) {
        return selectedForm;
    }

    return getBantuanFormIds()
        .map(id => document.getElementById(id))
        .find(form => form && !form.classList.contains('hidden')) || null;
}

function getVisibleJustifikasiRingkasFields(container) {
    if (!container) return [];

    return Array.from(container.querySelectorAll('textarea[name*="justifikasi"], input[name*="justifikasi"]'))
        .filter(field => {
            let type = (field.getAttribute('type') || '').toLowerCase();

            if (type === 'hidden' || field.disabled || hasHiddenAncestorWithin(field, container)) {
                return false;
            }

            return true;
        });
}

function hasHiddenAncestorWithin(field, container) {
    let current = field.parentElement;

    while (current && current !== container) {
        if (current.classList?.contains('hidden')) {
            return true;
        }

        current = current.parentElement;
    }

    return false;
}

function showJustifikasiRingkasError(field) {
    Swal.fire({
        icon: 'warning',
        title: 'Justifikasi diperlukan',
        text: JUSTIFIKASI_RINGKAS_REQUIRED_MESSAGE,
        confirmButtonColor: '#071633'
    });

    if (!field) return;

    field.classList.add('border-red-500', 'ring-2', 'ring-red-300');
    field.focus();

    field.addEventListener('input', function () {
        field.classList.remove('border-red-500', 'ring-2', 'ring-red-300');
    }, { once: true });
}

function validateActiveJustifikasiRingkas() {
    let activeForm = getActiveBantuanForm();
    let justifikasiFields = getVisibleJustifikasiRingkasFields(activeForm);

    for (let field of justifikasiFields) {
        if ((field.value || '').trim() === '') {
            showJustifikasiRingkasError(field);
            return false;
        }
    }

    return true;
}

function nextStep() {
    if (!validateCurrentStep()) {
        return;
    }

    if (currentStep < totalSteps) {
        document.getElementById('step-' + currentStep)
            .classList.add('hidden', 'pointer-events-none');

        currentStep++;

        document.getElementById('step-' + currentStep)
            .classList.remove('hidden', 'pointer-events-none');

        if (currentStep === 3) {
            syncStep1ToStep3();
        }

        if (currentStep === 4) {
            loadDocumentFields();
        }

        updateStepUI();

        
    }
}

function prevStep() {
    if (currentStep > 1) {
        document.getElementById('step-' + currentStep)
            .classList.add('hidden', 'pointer-events-none');

        currentStep--;

        document.getElementById('step-' + currentStep)
            .classList.remove('hidden', 'pointer-events-none');

        updateStepUI();
    }
}

function validateCurrentStep() {
    let stepContainer = document.getElementById('step-' + currentStep);

    if (!stepContainer) {
        return true;
    }

    let fields = stepContainer.querySelectorAll(
        'input[required], select[required], textarea[required]'
    );

    for (let field of fields) {
        if (field.disabled || field.closest('.hidden')) {
            continue;
        }

        if (field.type === 'file') {
            if (field.files.length === 0) {
                showFieldError(field, 'Sila lengkapkan semua maklumat.');
                return false;
            }
        } else {
            if (field.value.trim() === '') {
                showFieldError(field, 'Sila lengkapkan semua maklumat.');
                return false;
            }
        }
    }

    // ================= STEP 3 VALIDATION =================
    if (currentStep === 3) {
        let kategoriBantuan = getSelectedKategoriBantuan();

        // KEPELUAN ASAS
        if (kategoriBantuan === 'keperluan_asas') {
            let pakejField = document.querySelector('[name="bantuan_data[pakej]"]');
            let pakej = pakejField?.value;

            if (!pakej) {
                showStepWarning('Pakej bantuan diperlukan', 'Sila pilih pakej bantuan terlebih dahulu.');
                return false;
            }

            let basicRequiredFields = [
                {
                    field: document.querySelector('[name="bantuan_data[alamat_rumah]"]'),
                    message: 'Sila isi alamat rumah.'
                },
                {
                    field: document.querySelector('[name="bantuan_data[bandar]"]'),
                    message: 'Sila isi bandar.'
                },
                {
                    field: document.querySelector('[name="bantuan_data[poskod]"]'),
                    message: 'Sila isi poskod.'
                },
                {
                    field: document.querySelector('[name="bantuan_data[negeri]"]'),
                    message: 'Sila pilih negeri.'
                },
                {
                    field: document.querySelector('[name="bantuan_data[jenis_kediaman]"]'),
                    message: 'Sila pilih jenis kediaman.'
                }
            ];

            for (let item of basicRequiredFields) {
                if (!item.field || item.field.value.trim() === '') {
                    showFieldError(item.field || pakejField, item.message);
                    return false;
                }
            }

            let poskodField = document.querySelector('[name="bantuan_data[poskod]"]');
            let poskod = poskodField?.value.trim() || '';

            if (!/^[0-9]{5}$/.test(poskod)) {
                showFieldError(poskodField, 'Poskod mesti nombor sahaja dan tepat 5 digit.');
                return false;
            }

            let limit = getBasicPackageLimit();
            let memberCount = getBasicMemberCount();

            if (limit > 1 && memberCount !== limit) {
                showStepWarning(
                    'Jumlah ahli rumah tidak lengkap',
                    'Sila tambah tepat ' + limit + ' ahli rumah mengikut pakej yang dipilih.'
                );
                return false;
            }
        }

        // PEMBELAJARAN
        if (kategoriBantuan === 'alat_tulis_pembelajaran') {
            let jenisPermohonan = document.getElementById('learning_type')?.value;

            if (!jenisPermohonan) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Jenis permohonan diperlukan',
                    text: 'Sila pilih jenis permohonan.',
                    confirmButtonColor: '#071633'
                });
                return false;
            }

            if (jenisPermohonan === 'individu') {
                let checkedItems = document.querySelectorAll(
                    '#learning-individu-section input[type="checkbox"]:checked:not(:disabled)'
                );

                if (checkedItems.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Item diperlukan',
                        text: 'Sila pilih sekurang-kurangnya satu item pembelajaran.',
                        confirmButtonColor: '#071633'
                    });
                    return false;
                }
            }

            if (jenisPermohonan === 'group') {
                let namaGroup = document.querySelector('[name="bantuan_data[group][nama_group]"]')?.value;
                let bilangan = document.querySelector('[name="bantuan_data[group][bil_ahli]"]')?.value;

                if (!namaGroup || !bilangan) {
                    showStepWarning(
                        'Maklumat tidak lengkap',
                        'Sila lengkapkan maklumat kelab / persatuan / kelas.'
                    );
                    return false;
                }

                if (parseInt(bilangan, 10) < 1) {
                    showStepWarning('Bilangan ahli tidak sah', 'Bilangan ahli mesti sekurang-kurangnya 1.');
                    return false;
                }

                let checkedItems = document.querySelectorAll(
                    '#learning-group-section input[type="checkbox"]:checked:not(:disabled)'
                );

                if (checkedItems.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Item diperlukan',
                        text: 'Sila pilih sekurang-kurangnya satu item pembelajaran.',
                        confirmButtonColor: '#071633'
                    });
                    return false;
                }
            }
        }

        // PERALATAN
        if (kategoriBantuan === 'peralatan_pembelajaran') {
            let equipment = document.querySelector('input[name="bantuan_data[peralatan]"]:checked');
            let reason = document.getElementById('equipment_reason')?.value;

            if (!equipment) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peralatan diperlukan',
                    text: 'Sila pilih peralatan pembelajaran.',
                    confirmButtonColor: '#071633'
                });
                return false;
            }

            if (!reason) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sebab permohonan diperlukan',
                    text: 'Sila pilih sebab permohonan.',
                    confirmButtonColor: '#071633'
                });
                return false;
            }
        }

        // SUKAN
        if (kategoriBantuan === 'sukan') {
            let sportsLevel = document.getElementById('sports_level')?.value;
            let org = document.getElementById('sports_org')?.value;
            let participantsField = document.getElementById('sports_participants');
            let participants = parseInt(participantsField?.value || '0', 10);

            if (!sportsLevel) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringkat diperlukan',
                    text: 'Sila pilih peringkat aktiviti / pertandingan.',
                    confirmButtonColor: '#071633'
                });
                return false;
            }

            if (!org) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Maklumat tidak lengkap',
                    text: 'Sila lengkapkan maklumat permohonan sukan.',
                    confirmButtonColor: '#071633'
                });
                return false;
            }

            if (participants < 1) {
                showFieldError(participantsField, 'Bilangan peserta mesti sekurang-kurangnya 1.');
                return false;
            }

            let checkedSports = document.querySelectorAll(
                '#sports-items-section input[type="checkbox"]:checked:not(:disabled)'
            );

            if (checkedSports.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Item sukan diperlukan',
                    text: 'Sila pilih sekurang-kurangnya satu peralatan sukan.',
                    confirmButtonColor: '#071633'
                });
                return false;
            }
        }

        if (!validateActiveJustifikasiRingkas()) {
            return false;
        }
    }

    return true;
}

function showFieldError(field, message) {
    if (!field) {
        showStepWarning('Maklumat Tidak Lengkap', message);
        return;
    }

    Swal.fire({
        icon: 'warning',
        title: 'Maklumat Tidak Lengkap',
        text: message,
        confirmButtonText: 'OK',
        confirmButtonColor: '#071633',
        background: '#ffffff',
        color: '#071633'
    });

    field.classList.add('border-red-500', 'ring-2', 'ring-red-300');
    field.focus();

    field.addEventListener('input', function () {
        field.classList.remove('border-red-500', 'ring-2', 'ring-red-300');
    }, { once: true });

    field.addEventListener('change', function () {
        field.classList.remove('border-red-500', 'ring-2', 'ring-red-300');
    }, { once: true });
}

function updateStepUI() {
    for (let i = 1; i <= totalSteps; i++) {
        document.getElementById('indicator-' + i).classList.remove('active');
    }

    document.getElementById('indicator-' + currentStep).classList.add('active');

    document.getElementById('backBtn')
        .classList.toggle('hidden', currentStep === 1);

    document.getElementById('nextBtn')
        .classList.toggle('hidden', currentStep === 4);

    document.getElementById('submitBtn')
        .classList.toggle('hidden', currentStep !== 4);

    document.getElementById('currentStepLabel').innerText = currentStep;

    let percent = (currentStep / totalSteps) * 100;
    document.getElementById('progressPercent').innerText = percent + '%';
    document.getElementById('progressBar').style.width = percent + '%';
}

// ================= FAMILY FUNCTIONS =================

function showFamilyForm() {
    let section = document.getElementById('familyInputSection');
    if (section) {
        section.classList.remove('hidden');
    }
}

function cancelFamilyForm() {
    let section = document.getElementById('familyInputSection');
    if (section) {
        section.classList.add('hidden');
    }
}

function toggleRelationOther() {
    let relation = document.getElementById('fam_relation');
    let other = document.getElementById('fam_relation_other');

    if (relation && other) {
        if (relation.value === 'Lain-lain') {
            other.classList.remove('hidden');
        } else {
            other.classList.add('hidden');
            other.value = '';
        }
    }
}

function toggleHealthRemark() {
    let health = document.getElementById('fam_health');
    let remark = document.getElementById('health_remark');

    if (health && remark) {
        if (health.value === 'SAKIT KRONIK' || health.value === 'LAIN-LAIN') {
            remark.classList.remove('hidden');
        } else {
            remark.classList.add('hidden');
            remark.value = '';
        }
    }
}

function appendHiddenInput(container, name, value) {
    let input = document.createElement('input');
    input.type = 'hidden';
    input.name = name;
    input.value = value ?? '';

    container.appendChild(input);
}

function appendTanggunganHiddenInputs(container, index, data) {
    let group = document.createElement('div');
    group.dataset.index = index;

    appendHiddenInput(group, `tanggungan[${index}][nama]`, data.nama);
    appendHiddenInput(group, `tanggungan[${index}][hubungan]`, data.hubungan);
    appendHiddenInput(group, `tanggungan[${index}][umur]`, data.umur);
    appendHiddenInput(group, `tanggungan[${index}][status]`, data.status);
    appendHiddenInput(group, `tanggungan[${index}][kesihatan]`, data.kesihatan);
    appendHiddenInput(group, `tanggungan[${index}][pendapatan]`, data.pendapatan);

    container.appendChild(group);
}

function getFamilyHiddenContainer() {
    let hiddenContainer = document.getElementById('familyHiddenInputs');

    if (!hiddenContainer) {
        let form = document.getElementById('permohonanForm');

        if (!form) {
            return null;
        }

        hiddenContainer = document.createElement('div');
        hiddenContainer.id = 'familyHiddenInputs';
        hiddenContainer.classList.add('hidden');
        form.appendChild(hiddenContainer);
    }

    return hiddenContainer;
}

function syncFamilyHiddenInputs() {
    let hiddenContainer = getFamilyHiddenContainer();
    if (!hiddenContainer) return;

    hiddenContainer.innerHTML = '';

    familyMembers.forEach((member, index) => {
        appendTanggunganHiddenInputs(hiddenContainer, index, member);
    });
}

function addFamilyMember() {
    let name = document.getElementById('fam_name')?.value.trim() || '';
    let relation = document.getElementById('fam_relation')?.value || '';
    let relationOther = document.getElementById('fam_relation_other')?.value.trim() || '';
    let age = document.getElementById('fam_age')?.value || '';
    let status = document.getElementById('fam_status')?.value || '';
    let health = document.getElementById('fam_health')?.value || '';
    let healthRemark = document.getElementById('health_remark')?.value.trim() || '';
    let income = document.getElementById('fam_income')?.value || 0;

    if (name === '' || relation === '') {
        showFieldError(document.getElementById('fam_name'), 'Sila lengkapkan nama dan hubungan ahli keluarga.');
        return;
    }

    if (relation === 'Lain-lain') {
        if (relationOther === '') {
            showFieldError(document.getElementById('fam_relation_other'), 'Sila nyatakan hubungan.');
            return;
        }

        relation = relationOther;
    }

    if ((health === 'SAKIT KRONIK' || health === 'LAIN-LAIN') && healthRemark !== '') {
        health += ' (' + healthRemark + ')';
    }

    let tbody = document.getElementById('familyTableBody');

    if (!tbody) {
        console.error('familyTableBody tidak dijumpai');
        return;
    }

    let rowCount = tbody.rows.length + 1;
    let index = tanggunganIndex++;
    let incomeValue = parseFloat(income || 0);
    let member = {
        index: index,
        nama: name,
        hubungan: relation,
        umur: age,
        status: status,
        kesihatan: health,
        pendapatan: incomeValue,
    };

    let row = document.createElement('tr');
    row.className = 'border-b hover:bg-slate-50';
    row.setAttribute('data-index', index);

    row.innerHTML = `
        <td class="p-4">${rowCount}</td>
        <td class="p-4">${name}</td>
        <td class="p-4">${relation}</td>
        <td class="p-4">${age}</td>
        <td class="p-4">${status}</td>
        <td class="p-4">${health}</td>
        <td class="p-4">RM${incomeValue.toFixed(2)}</td>
        <td class="p-4 text-center">
            <button type="button"
                    onclick="deleteFamilyMember(this)"
                    class="text-red-600 font-medium">
                Delete
            </button>
        </td>
    `;

    tbody.appendChild(row);
    familyMembers.push(member);

    syncFamilyHiddenInputs();

    calculateIncome();
    clearFamilyInputs();
    cancelFamilyForm();

    Swal.fire({
        icon: 'success',
        title: 'Berjaya!',
        text: 'Ahli keluarga berjaya ditambah.',
        confirmButtonColor: '#16a34a',
        timer: 1800,
        showConfirmButton: false
    });
}

function deleteFamilyMember(button) {
    let row = button.closest('tr');
    let index = row.getAttribute('data-index');

    row.remove();
    familyMembers = familyMembers.filter(member => String(member.index) !== String(index));

    syncFamilyHiddenInputs();

    refreshBil();
    calculateIncome();
}

function refreshBil() {
    let rows = document.querySelectorAll('#familyTableBody tr');

    rows.forEach((row, index) => {
        row.cells[0].innerText = index + 1;
    });
}

function clearFamilyInputs() {
    let fields = [
        'fam_name',
        'fam_relation',
        'fam_relation_other',
        'fam_age',
        'fam_status',
        'fam_health',
        'health_remark',
        'fam_income'
    ];

    fields.forEach(id => {
        let el = document.getElementById(id);
        if (el) el.value = '';
    });

    toggleRelationOther();
    toggleHealthRemark();
}

function calculateIncome() {
    let total = 0;

    let primary = parseFloat(document.getElementById('primary_income')?.value) || 0;
    total += primary;

    let rows = document.querySelectorAll('#familyTableBody tr');

    rows.forEach(row => {
        let income = row.cells[6].innerText.replace('RM', '');
        total += parseFloat(income) || 0;
    });

    let totalIncome = document.getElementById('totalIncome');

    if (totalIncome) {
        totalIncome.innerText = 'RM' + total.toFixed(2);
    }
}

// ================= KEPERLUAN ASAS =================

function getBasicPackageLimit() {
    let packageSelect = document.getElementById('basic_package');
    let selectedOption = packageSelect?.options[packageSelect.selectedIndex];

    return parseInt(selectedOption?.dataset.limit || '0', 10);
}

function getBasicMemberCount() {
    return document.querySelectorAll('#basicMemberTableBody tr').length;
}

function updateBasicMemberButtonState() {
    let addBtn = document.getElementById('addBasicMemberBtn');
    if (!addBtn) return;

    let limit = getBasicPackageLimit();
    let count = getBasicMemberCount();

    addBtn.disabled = (limit <= 1 || count >= limit);
}

function updatePackageLimit() {
    let limit = getBasicPackageLimit();
    let packageSelect = document.getElementById('basic_package');
    let selectedOption = packageSelect?.options[packageSelect.selectedIndex];
    let packageName = document.getElementById('basic_package_name');
    let packageLimit = document.getElementById('packageLimit');
    let form = document.getElementById('basicMemberForm');
    let section = document.getElementById('basicMemberSection');
    let tbody = document.getElementById('basicMemberTableBody');

    if (packageName) {
        packageName.value = selectedOption?.dataset.name || '';
    }

    if (packageLimit) {
        packageLimit.innerText = limit;
    }

    if (form && limit <= 1) {
        form.classList.add('hidden');
    }

    if (section) {
        section.classList.toggle('hidden', limit <= 1);
    }

    if (tbody && limit <= 1) {
        tbody.innerHTML = '';
    }

    if (limit <= 1) {
        let hiddenContainer = document.getElementById('basicHiddenInputs');

        if (hiddenContainer) {
            hiddenContainer.innerHTML = '';
        }
    }

    let totalMembers = document.getElementById('basic_total_members');

    if (totalMembers) {
        totalMembers.value = getBasicMemberCount();
    }

    updateBasicMemberButtonState();
}

function showBasicMemberForm() {
    let form = document.getElementById('basicMemberForm');
    if (!form) return;

    let limit = getBasicPackageLimit();
    let count = getBasicMemberCount();

    if (limit === 0) {
        showAlert('Sila pilih pakej bantuan terlebih dahulu.');
        return;
    }

    if (limit === 1) {
        return;
    }

    if (count >= limit) {
        showAlert('Jumlah ahli rumah telah mencapai had pakej yang dipilih.');
        return;
    }

    form.classList.remove('hidden');
}

function cancelBasicMemberForm() {
    let form = document.getElementById('basicMemberForm');

    if (form) {
        form.classList.add('hidden');
    }

    clearBasicMemberInputs();
}

function clearBasicMemberInputs() {
    ['basic_name', 'basic_matric', 'basic_faculty'].forEach(id => {
        let el = document.getElementById(id);
        if (el) el.value = '';
    });
}

let basicMemberIndex = 0;

function addBasicMember() {
    let name = document.getElementById('basic_name')?.value.trim() || '';
    let matricField = document.getElementById('basic_matric');
    let matric = normalizeMatricValue(matricField?.value);
    let faculty = document.getElementById('basic_faculty')?.value.trim() || '';
    let tbody = document.getElementById('basicMemberTableBody');

    if (!tbody) return;

    if (matricField) {
        matricField.value = matric;
    }

    if (name === '' || matric === '' || faculty === '') {
        showAlert('Sila lengkapkan Nama, No Matrik dan Fakulti.');
        return;
    }

    if (!/^A[0-9]{6}$/.test(matric)) {
        showAlert('No Matrik mesti format seperti A208972.');
        return;
    }

    let limit = getBasicPackageLimit();
    let count = getBasicMemberCount();

    if (limit === 0) {
        showAlert('Sila pilih pakej bantuan terlebih dahulu.');
        return;
    }

    if (limit <= 1 || count >= limit) {
        showAlert('Jumlah ahli rumah telah mencapai had pakej yang dipilih.');
        updateBasicMemberButtonState();
        return;
    }

    let rowCount = count + 1;
    let index = basicMemberIndex++;

    let row = document.createElement('tr');
    row.className = 'border-b hover:bg-slate-50';
    row.setAttribute('data-index', index);

    row.innerHTML = `
        <td class="p-4">${rowCount}</td>
        <td class="p-4">${name}</td>
        <td class="p-4">${matric}</td>
        <td class="p-4">${faculty}</td>
        <td class="p-4 text-center">
            <button type="button"
                    onclick="deleteBasicMember(this)"
                    class="text-red-600 font-medium">
                Delete
            </button>
        </td>
    `;

    tbody.appendChild(row);

    let hiddenContainer = document.getElementById('basicHiddenInputs');

    if (!hiddenContainer) {
        hiddenContainer = document.createElement('div');
        hiddenContainer.id = 'basicHiddenInputs';
        hiddenContainer.classList.add('hidden');
        (document.getElementById('form-keperluan-asas') || document.querySelector('form')).appendChild(hiddenContainer);
    }

    hiddenContainer.insertAdjacentHTML('beforeend', `
        <div data-index="${index}">
            <input type="hidden" name="bantuan_data[ahli_rumah][${index}][nama]" value="${name}">
            <input type="hidden" name="bantuan_data[ahli_rumah][${index}][no_matrik]" value="${matric}">
            <input type="hidden" name="bantuan_data[ahli_rumah][${index}][fakulti]" value="${faculty}">
        </div>
    `);

    document.getElementById('basic_total_members').value = getBasicMemberCount();

    clearBasicMemberInputs();
    cancelBasicMemberForm();
    updateBasicMemberButtonState();

    Swal.fire({
        icon: 'success',
        title: 'Berjaya!',
        text: 'Ahli rumah berjaya ditambah.',
        confirmButtonColor: '#16a34a',
        timer: 1600,
        showConfirmButton: false
    });
}

function deleteBasicMember(button) {
    let row = button.closest('tr');
    let index = row.getAttribute('data-index');

    row.remove();

    let hiddenGroup = document.querySelector(`#basicHiddenInputs div[data-index="${index}"]`);
    if (hiddenGroup) {
        hiddenGroup.remove();
    }

    let rows = document.querySelectorAll('#basicMemberTableBody tr');

    rows.forEach((currentRow, i) => {
        currentRow.cells[0].innerText = i + 1;
    });

    document.getElementById('basic_total_members').value = getBasicMemberCount();

    updateBasicMemberButtonState();
}

// ================= DYNAMIC BANTUAN =================

function syncStep1ToStep3() {
    let nama = document.querySelector('[name="nama_penuh"]')?.value || '';
    let matrik = document.querySelector('[name="no_matrik"]')?.value || '';
    let fakulti = document.querySelector('[name="fakulti"]')?.value || '';
    let tahunPengajian = document.querySelector('[name="tahun_pengajian"]')?.value || '';

    document.querySelector('[name="bantuan_data[nama_ketua]"]')?.setAttribute('value', nama);
    document.querySelector('[name="bantuan_data[no_matrik_ketua]"]')?.setAttribute('value', matrik);

    document.querySelector('[name="bantuan_data[individu][nama]"]')?.setAttribute('value', nama);
    document.querySelector('[name="bantuan_data[individu][no_matrik]"]')?.setAttribute('value', matrik);
    document.querySelector('[name="bantuan_data[individu][fakulti]"]')?.setAttribute('value', fakulti);
    document.querySelector('[name="bantuan_data[individu][tahun_pengajian]"]')?.setAttribute('value', tahunPengajian);
}

// ================= DYNAMIC BANTUAN =================

const kategoriBantuanMap = {
    bantuan_asas_hidup: [
        {
            value: 'keperluan_asas',
            label: 'Keperluan Asas',
            form: 'form-keperluan-asas'
        }
    ],
    bantuan_pembelajaran: [
        {
            value: 'alat_tulis_pembelajaran',
            label: 'Alat Tulis Pembelajaran',
            form: 'form-pembelajaran'
        },
        {
            value: 'peralatan_pembelajaran',
            label: 'Peralatan Pembelajaran',
            form: 'form-peralatan'
        }
    ],
    bantuan_sukan: [
        {
            value: 'sukan',
            label: 'Sukan',
            form: 'form-sukan'
        }
    ],
    bantuan_musibah: [
        {
            value: 'keperluan_asas',
            label: 'Keperluan Asas',
            form: 'form-keperluan-asas'
        },
        {
            value: 'peralatan_pembelajaran',
            label: 'Peralatan Pembelajaran',
            form: 'form-peralatan'
        }
    ]
};

function getBantuanFormIds() {
    return [
        'form-keperluan-asas',
        'form-pembelajaran',
        'form-peralatan',
        'form-sukan'
    ];
}

function hideAllBantuanForms() {
    getBantuanFormIds().forEach(id => {
        let form = document.getElementById(id);
        if (!form) return;

        form.classList.add('hidden');
        setContainerDisabled(form, true);
    });
}

function getSelectedKategoriBantuan() {
    let kategoriSelect = document.getElementById('kategori_bantuan');
    return kategoriSelect?.value || '';
}

function loadKategoriBantuan() {
    let jenisSelect = document.getElementById('jenis_bantuan');
    let kategoriWrapper = document.getElementById('kategori-bantuan-wrapper');
    let kategoriSelect = document.getElementById('kategori_bantuan');

    hideAllBantuanForms();

    if (!jenisSelect || !kategoriWrapper || !kategoriSelect) {
        return;
    }

    let selectedJenis = jenisSelect.value;
    let kategoriList = kategoriBantuanMap[selectedJenis] || [];

    kategoriSelect.innerHTML = '<option value="">-- Pilih Kategori Bantuan --</option>';
    kategoriSelect.value = '';
    kategoriSelect.disabled = true;
    kategoriWrapper.classList.add('hidden');

    if (!selectedJenis || kategoriList.length === 0) {
        return;
    }

    kategoriList.forEach(kategori => {
        let option = document.createElement('option');

        option.value = kategori.value;
        option.textContent = kategori.label;
        option.dataset.form = kategori.form;

        kategoriSelect.appendChild(option);
    });

    kategoriSelect.disabled = false;
    kategoriWrapper.classList.remove('hidden');
}

function loadBantuanForm() {
    let kategoriSelect = document.getElementById('kategori_bantuan');
    let selectedOption = kategoriSelect?.options[kategoriSelect.selectedIndex];
    let kategori = getSelectedKategoriBantuan();
    let formId = selectedOption?.dataset.form || '';

    hideAllBantuanForms();

    if (!formId) {
        let formMap = {
            keperluan_asas: 'form-keperluan-asas',
            alat_tulis_pembelajaran: 'form-pembelajaran',
            peralatan_pembelajaran: 'form-peralatan',
            sukan: 'form-sukan'
        };

        formId = formMap[kategori] || '';
    }

    let selectedForm = formId ? document.getElementById(formId) : null;

    if (selectedForm) {
        selectedForm.classList.remove('hidden');
        setContainerDisabled(selectedForm, false);
        syncItemQuantityState(selectedForm);
    }

    if (kategori === 'keperluan_asas') {
        updatePackageLimit();
    } else if (kategori === 'alat_tulis_pembelajaran') {
        toggleLearningType();
    } else if (kategori === 'sukan') {
        toggleSportsLevel();
    }
}

function findOptionByValue(select, value) {
    if (!select || value === null || value === undefined || value === '') {
        return null;
    }

    return Array.from(select.options).find(option => option.value === String(value));
}

function normalizeCatalogItem(value) {
    return String(value || '')
        .trim()
        .toLowerCase()
        .replace(/\s+/g, ' ');
}

function prefillCatalogItem() {
    let item = initialBantuanSelection.item;

    if (!item) {
        return;
    }

    let kategori = getSelectedKategoriBantuan();
    let normalizedItem = normalizeCatalogItem(item);

    if (kategori === 'keperluan_asas') {
        let packageSelect = document.getElementById('basic_package');
        let packageValue = String(item);
        let packageLimit = packageValue.match(/\d+/)?.[0] || '';
        let packageOption = findOptionByValue(packageSelect, packageValue);

        if (!packageOption && packageSelect) {
            packageOption = Array.from(packageSelect.options).find(option => {
                return normalizeCatalogItem(option.dataset.name || '') === normalizedItem
                    || (packageLimit !== '' && option.dataset.limit === packageLimit);
            });
        }

        if (packageSelect && packageOption) {
            packageSelect.value = packageOption.value;
            updatePackageLimit();
        }

        return;
    }

    if (kategori === 'peralatan_pembelajaran') {
        document.querySelectorAll('input[name="bantuan_data[peralatan]"]').forEach(radio => {
            if (normalizeCatalogItem(radio.value) === normalizedItem) {
                radio.checked = true;
                selectEquipment(radio);
            }
        });

        return;
    }

    if (kategori === 'alat_tulis_pembelajaran') {
        let target = normalizedItem;
        let learningType = document.getElementById('learning_type');

        if (learningType && !learningType.value) {
            learningType.value = 'individu';
            toggleLearningType();
        }

        document.querySelectorAll('#learning-individu-section input[type="checkbox"]').forEach(checkbox => {
            if (normalizeCatalogItem(checkbox.value) === target) {
                checkbox.checked = true;
                let qtyInput = checkbox.closest('tr')?.querySelector('input[type="number"]');

                if (qtyInput) {
                    qtyInput.disabled = checkbox.disabled ? true : false;
                }
            }
        });

        updateSummary('individu');
        return;
    }

    if (kategori === 'sukan') {
        document.querySelectorAll('#sports-items-section input[type="checkbox"]').forEach(checkbox => {
            if (normalizeCatalogItem(checkbox.value) === normalizedItem) {
                checkbox.checked = true;
            }
        });

        updateSportsSummary();
    }
}

function applyInitialBantuanSelection() {
    let jenis = initialBantuanSelection.jenis;

    if (!jenis) {
        loadKategoriBantuan();
        loadBantuanForm();
        return;
    }

    let jenisSelect = document.getElementById('jenis_bantuan');
    let kategoriSelect = document.getElementById('kategori_bantuan');
    let jenisOption = findOptionByValue(jenisSelect, jenis);

    if (!jenisSelect || !jenisOption || jenisOption.disabled) {
        loadKategoriBantuan();
        loadBantuanForm();
        return;
    }

    jenisSelect.value = jenis;
    loadKategoriBantuan();

    let kategori = initialBantuanSelection.kategori;
    let kategoriOption = findOptionByValue(kategoriSelect, kategori);

    if (kategoriSelect && kategoriOption) {
        kategoriSelect.value = kategori;
    }

    loadBantuanForm();
    prefillCatalogItem();
}

function toggleLearningType() {
    let type = document.getElementById('learning_type')?.value;
    let individuSection = document.getElementById('learning-individu-section');
    let groupSection = document.getElementById('learning-group-section');

    toggleSectionEnabled(individuSection, type === 'individu');
    toggleSectionEnabled(groupSection, type === 'group');

    updateSummary('individu');
    updateSummary('group');
}

function toggleItemQty(checkbox, qtyId) {
    let qtyInput = document.getElementById(qtyId);

    if (!qtyInput) return;

    if (!checkbox.disabled && checkbox.checked) {
        qtyInput.disabled = false;
        qtyInput.focus();
    } else {
        qtyInput.disabled = true;
        qtyInput.value = 1;
    }
}

function updateSummary(type) {
    let summaryBox = document.getElementById('summary-' + type);
    if (!summaryBox) return;

    let rows = [];
    let sectionId = type === 'individu' ? 'learning-individu-section' : 'learning-group-section';
    let section = document.getElementById(sectionId);

    section?.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
        let qtyInput = checkbox.closest('tr')?.querySelector('input[type="number"]');

        if (checkbox && checkbox.checked && !checkbox.disabled) {
            rows.push((checkbox.dataset.itemName || checkbox.value) + ' - ' + (qtyInput?.value || 1) + ' unit');
        }
    });

    summaryBox.innerHTML = rows.length === 0 ? 'Tiada item dipilih.' : rows.join('<br>');
}

function selectEquipment(selectedRadio) {
    document.querySelectorAll('.equipment-card').forEach(card => {
        card.classList.remove('border-blue-600', 'bg-blue-50', 'ring-2', 'ring-blue-300');
    });

    let selectedCard = selectedRadio.closest('.equipment-card');

    if (selectedCard) {
        selectedCard.classList.add('border-blue-600', 'bg-blue-50', 'ring-2', 'ring-blue-300');
    }

    if (typeof updateEquipmentSummary === 'function') {
        updateEquipmentSummary();
    }
}

function updateEquipmentSummary() {
    // Hook kept for existing inline handlers.
}

function toggleSportsLevel() {
    let level = document.getElementById('sports_level')?.value;

    let infoSection = document.getElementById('sports-info-section');
    let itemSection = document.getElementById('sports-items-section');
    let summarySection = document.getElementById('sports-summary-section');
    let justificationSection = document.getElementById('sports-justification-section');

    let hasLevel = level !== '';

    toggleSectionEnabled(infoSection, hasLevel);
    toggleSectionEnabled(itemSection, hasLevel);
    toggleSectionEnabled(justificationSection, hasLevel);
    summarySection?.classList.toggle('hidden', !hasLevel);

    updateSportsSummary();
}

function toggleSportsQty(checkbox, qtyId) {
    let qtyInput = document.getElementById(qtyId);

    if (!qtyInput) return;

    if (!checkbox.disabled && checkbox.checked) {
        qtyInput.disabled = false;
        qtyInput.focus();
    } else {
        qtyInput.disabled = true;
        qtyInput.value = 1;
    }

    updateSportsSummary();
}

function updateSportsSummary() {
    let summaryBox = document.getElementById('sports-summary');
    if (!summaryBox) return;

    let rows = [];
    let section = document.getElementById('sports-items-section');

    section?.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
        let qtyInput = checkbox.closest('tr')?.querySelector('input[type="number"]');

        if (checkbox && checkbox.checked && !checkbox.disabled) {
            rows.push((checkbox.dataset.itemName || checkbox.value) + ' - ' + (qtyInput?.value || 1) + ' unit');
        }
    });

    summaryBox.innerHTML = rows.length === 0 ? 'Tiada item dipilih.' : rows.join('<br>');
}

function loadDocumentFields() {

    let kategori = document.getElementById('kategori_bantuan')?.value || '';
    let container = document.getElementById('dynamic-document-section');

    if (!container) return;

    let title = '';
    let dokumen1 = '';
    let dokumen2 = '';
    let dokumen1Helper = '';
    let dokumen2Helper = '';
    let footerHelper = 'Format dibenarkan: PDF, JPG, PNG. Maksimum 5MB setiap fail.';

    if (kategori === 'keperluan_asas') {
        title = 'Dokumen Wajib - Keperluan Asas';
        dokumen1 = 'Dokumen Pendapatan Penjaga setiap Ahli Rumah';
        dokumen2 = 'Bukti Alamat Rumah Sewa / Bil Utiliti';
        dokumen1Helper = 'Sila muat naik satu dokumen pendapatan bagi setiap ahli rumah yang disenaraikan.';
    }

    else if (kategori === 'alat_tulis_pembelajaran') {
        title = 'Dokumen Wajib - Alat Tulis Pembelajaran';
        dokumen1 = 'Surat Sokongan Fakulti';
        dokumen2 = 'Dokumen Sokongan Tambahan';
    }

    else if (kategori === 'peralatan_pembelajaran') {
        title = 'Dokumen Wajib - Peralatan Pembelajaran';
        dokumen1 = 'Bukti Kerosakan / Gambar Peralatan';
        dokumen2 = 'Bukti Pendapatan Penjaga';
        dokumen2Helper = 'Contoh: slip gaji, surat pengesahan pendapatan, atau surat tiada pendapatan.';
        footerHelper = '';
    }

    else if (kategori === 'sukan') {
        title = 'Dokumen Wajib - Bantuan Sukan';
        dokumen1 = 'Surat Kelulusan Kelab / Program';
        dokumen2 = 'Surat Penyertaan Aktiviti / Pertandingan';
    }

    if (title === '') {
        container.innerHTML = '';
        return;
    }

    container.innerHTML = `
        <div class="border border-slate-200 rounded-3xl bg-slate-50 p-6">

            <h3 class="font-semibold text-lg text-slate-900 mb-5">
                ${title}
            </h3>

            <label class="block text-sm font-medium text-slate-700 mb-3">
                ${dokumen1}
                <span class="text-red-500">*</span>
            </label>

            <input
                type="file"
                name="dokumen_wajib[dokumen_1]"
                accept=".pdf,.jpg,.jpeg,.png"
                required
                class="w-full border border-slate-300 bg-white p-4 rounded-2xl text-sm focus:ring-2 focus:ring-blue-500 ${dokumen1Helper ? 'mb-2' : 'mb-5'}"
            >

            ${dokumen1Helper ? `
                <p class="text-sm text-slate-500 mb-5">
                    ${dokumen1Helper}
                </p>
            ` : ''}

            <label class="block text-sm font-medium text-slate-700 mb-3">
                ${dokumen2}
                <span class="text-red-500">*</span>
            </label>

            <input
                type="file"
                name="dokumen_wajib[dokumen_2]"
                accept=".pdf,.jpg,.jpeg,.png"
                required
                class="w-full border border-slate-300 bg-white p-4 rounded-2xl text-sm focus:ring-2 focus:ring-blue-500"
            >

            ${dokumen2Helper ? `
                <p class="text-sm text-slate-500 mt-2">
                    ${dokumen2Helper}
                </p>
            ` : ''}

            ${footerHelper ? `
                <p class="text-sm text-slate-500 mt-4">
                    ${footerHelper}
                </p>
            ` : ''}

        </div>
    `;
}

function showAlert(message) {
    Swal.fire({
        icon: 'warning',
        title: 'Perhatian',
        text: message,
        confirmButtonColor: '#071633'
    });
}

document.addEventListener('DOMContentLoaded', function () {
    applyInitialBantuanSelection();
    updatePackageLimit();

    let form = document.getElementById('permohonanForm');

    if (form) {
        form.addEventListener('submit', function () {
            syncFamilyHiddenInputs();
        });
    }
});
</script>

@endsection
