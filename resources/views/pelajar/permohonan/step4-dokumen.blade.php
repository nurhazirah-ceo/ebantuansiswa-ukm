{{-- resources/views/pelajar/permohonan/step4-dokumen.blade.php --}}

<div id="step-4" class="hidden space-y-8">

    <div class="bg-white border border-slate-200 rounded-3xl shadow-lg overflow-hidden">

        <div class="bg-[#071633] px-8 py-6 text-white">
            <h2 class="text-xl font-semibold tracking-tight">
                Dokumen Sokongan
            </h2>

            <p class="text-sm text-slate-300 mt-1">
                Sila muat naik dokumen yang diperlukan mengikut jenis bantuan yang dipohon.
            </p>
        </div>

        <div class="p-8 space-y-8">

            <div class="bg-yellow-50 border border-yellow-200 rounded-2xl px-5 py-4 flex items-start gap-3">
                <div class="text-yellow-600 text-lg mt-0.5">
                    ⚠
                </div>

                <div>
                    <p class="text-sm font-semibold text-yellow-800">
                        Peringatan Penting
                    </p>

                    <p class="text-sm text-yellow-700 mt-1 leading-relaxed">
                        Pastikan semua dokumen dimuat naik dengan jelas, lengkap,
                        dan mengikut format yang dibenarkan sebelum menghantar permohonan.
                    </p>
                </div>
            </div>

            {{-- Dokumen wajib akan keluar ikut jenis bantuan --}}
            <div id="dynamic-document-section" class="space-y-6">
                {{-- Inject by JavaScript --}}
            </div>

            <div class="border border-slate-200 rounded-3xl bg-slate-50 p-6">

                <h3 class="font-semibold text-lg text-slate-900 mb-5">
                    Dokumen Tambahan
                    <span class="text-slate-400 text-sm">(Optional)</span>
                </h3>

                <label class="block text-sm font-medium text-slate-700 mb-3">
                    Lampiran Tambahan
                </label>

                <input
                    type="file"
                    name="dokumen_tambahan"
                    accept=".pdf,.jpg,.jpeg,.png"
                    class="w-full border border-slate-300 bg-white p-4 rounded-2xl text-sm focus:ring-2 focus:ring-blue-500"
                >

                <p class="text-sm text-slate-500 mt-3">
                    Format dibenarkan: PDF, JPG, PNG. Maksimum 5MB.
                </p>

            </div>

        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const step = document.getElementById('step-4');
    const form = step?.closest('form');
    const documentSection = document.getElementById('dynamic-document-section');
    let confirmedSubmit = false;

    function markRequiredDocumentInputs() {
        step?.querySelectorAll('input[type="file"][name^="dokumen_wajib"]').forEach(function (input) {
            input.dataset.requiredDocument = 'true';
            input.removeAttribute('required');
        });
    }

    function hasMissingRequiredDocuments() {
        markRequiredDocumentInputs();

        return Array.from(step?.querySelectorAll('input[type="file"][data-required-document="true"]') || [])
            .some(function (input) {
                return !input.disabled && !input.closest('.hidden') && input.files.length === 0;
            });
    }

    if (documentSection) {
        new MutationObserver(markRequiredDocumentInputs)
            .observe(documentSection, { childList: true, subtree: true });
    }

    markRequiredDocumentInputs();

    form?.addEventListener('submit', function (event) {
        if (confirmedSubmit) {
            confirmedSubmit = false;
            return;
        }

        event.preventDefault();

        if (typeof validateActiveJustifikasiRingkas === 'function' && !validateActiveJustifikasiRingkas()) {
            return;
        }

        if (hasMissingRequiredDocuments()) {
            Swal.fire({
                icon: 'warning',
                title: 'Dokumen belum lengkap',
                text: 'Sila muat naik semua dokumen wajib sebelum menghantar permohonan.',
                confirmButtonColor: '#071633'
            });
            return;
        }

        Swal.fire({
            icon: 'question',
            title: 'Hantar permohonan?',
            text: 'Pastikan semua maklumat dan dokumen adalah betul sebelum dihantar.',
            showCancelButton: true,
            confirmButtonText: 'Ya, hantar',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#16a34a',
            cancelButtonColor: '#64748b'
        }).then(function (result) {
            if (result.isConfirmed) {
                confirmedSubmit = true;
                form.requestSubmit();
            }
        });
    });
});
</script>
