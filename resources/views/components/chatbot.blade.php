<div class="fixed bottom-5 right-5 z-[9999] font-sans" data-ebs-chatbot>
    <div
        class="absolute bottom-20 right-0 hidden h-[80vh] max-h-[640px] w-[calc(100vw-2rem)] max-w-sm overflow-hidden rounded-2xl border border-blue-100 bg-white shadow-2xl shadow-blue-900/20 sm:w-96"
        data-chatbot-panel
        aria-live="polite"
    >
        <div class="flex items-center justify-between gap-3 bg-gradient-to-r from-[#2563EB] to-[#3B82F6] px-5 py-4 text-white">
            <div class="flex min-w-0 items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white/20 text-white ring-1 ring-white/30">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 3v2.2M7.4 9h9.2M9 13h.01M15 13h.01M8 18h8a4 4 0 0 0 4-4v-3.5a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4V14a4 4 0 0 0 4 4Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M8.5 21h7M4 12H2.5M21.5 12H20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h2 class="truncate text-sm font-semibold leading-tight">eBantu Bot</h2>
                    <p class="mt-0.5 truncate text-xs text-blue-50">Sedia membantu pelajar dan penderma</p>
                </div>
            </div>

            <button
                type="button"
                class="shrink-0 rounded-full p-2 text-blue-50 transition hover:bg-white/15 hover:text-white focus:outline-none focus:ring-2 focus:ring-white/70"
                data-chatbot-close
                aria-label="Tutup chatbot"
            >
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="m6 6 12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
        </div>

        <div class="flex h-[calc(100%-76px)] min-h-0 flex-col bg-slate-50">
            <div class="min-h-0 flex-1 space-y-2 overflow-y-auto px-4 py-3" data-chatbot-messages>
                <div class="mr-6 rounded-2xl rounded-tl-sm bg-white px-3 py-2 text-xs leading-relaxed text-slate-700 shadow-sm ring-1 ring-slate-200 sm:text-sm" data-chatbot-message="bot">
                    Hai! 👋 Saya eBantu Bot.<br>
                    Untuk mula, pilih kategori supaya saya boleh bantu anda dengan lebih tepat 😊
                </div>
            </div>

            <div class="shrink-0 border-t border-slate-200 bg-white px-4 py-3">
                <div class="mb-3 flex min-h-7 flex-nowrap items-center gap-1.5 overflow-hidden" data-chatbot-actions>
                    <button type="button" class="max-w-full rounded-full border border-blue-100 bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 transition hover:border-blue-300 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-200" data-role="pelajar">Pelajar</button>
                    <button type="button" class="max-w-full rounded-full border border-blue-100 bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 transition hover:border-blue-300 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-200" data-role="penderma">Penderma</button>
                </div>

                <form class="flex items-center gap-2" data-chatbot-form>
                    <label class="sr-only" data-chatbot-input-label>Taip soalan anda</label>
                    <input
                        type="text"
                        class="min-w-0 flex-1 rounded-full border-slate-300 px-4 py-2.5 text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Taip soalan anda..."
                        autocomplete="off"
                        data-chatbot-input
                    >
                    <button
                        type="submit"
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-blue-600 text-white shadow-lg shadow-blue-600/25 transition hover:bg-blue-700 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-300"
                        aria-label="Hantar soalan"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="m4 12 16-8-6 16-3-7-7-1Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <button
        type="button"
        class="group relative flex h-16 w-16 items-center justify-center rounded-full bg-blue-600 text-white shadow-lg shadow-blue-700/30 transition duration-200 hover:scale-105 hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200"
        data-chatbot-toggle
        aria-expanded="false"
        aria-label="Buka chatbot"
    >
        <span class="absolute inset-0 rounded-full bg-blue-400/40 animate-ping"></span>
        <span class="relative flex h-16 w-16 items-center justify-center rounded-full">
            <svg class="h-8 w-8 transition group-hover:scale-105" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M12 3v2.2M7.4 9h9.2M9 13h.01M15 13h.01M8 18h8a4 4 0 0 0 4-4v-3.5a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4V14a4 4 0 0 0 4 4Z" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M8.5 21h7M4 12H2.5M21.5 12H20" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>
            </svg>
        </span>
    </button>
</div>

@once
    <style>
        [data-ebs-chatbot] {
            position: fixed;
            right: 1.25rem;
            bottom: 1.25rem;
            z-index: 9999;
            font-family: Poppins, Figtree, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        [data-ebs-chatbot] .hidden {
            display: none !important;
        }

        [data-chatbot-panel] {
            position: absolute;
            right: 0;
            bottom: 5rem;
            width: min(24rem, calc(100vw - 2rem));
            height: min(80vh, 640px);
            overflow: hidden;
            border: 1px solid #dbeafe;
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 24px 60px rgba(30, 64, 175, 0.2);
        }

        [data-chatbot-panel] > div:first-child {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 1rem 1.25rem;
            color: #fff;
            background: linear-gradient(90deg, #2563eb, #3b82f6);
        }

        [data-chatbot-panel] > div:first-child > div {
            display: flex;
            min-width: 0;
            align-items: center;
            gap: 0.75rem;
        }

        [data-chatbot-panel] > div:first-child > div > div:first-child {
            display: flex;
            width: 2.75rem;
            height: 2.75rem;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
            background: rgba(255, 255, 255, 0.2);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.3);
        }

        [data-chatbot-panel] h2 {
            margin: 0;
            overflow: hidden;
            font-size: 0.875rem;
            font-weight: 600;
            line-height: 1.25rem;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        [data-chatbot-panel] p {
            margin: 0.125rem 0 0;
            overflow: hidden;
            color: #eff6ff;
            font-size: 0.75rem;
            line-height: 1rem;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        [data-chatbot-panel] svg,
        [data-chatbot-toggle] svg {
            display: block;
        }

        [data-chatbot-panel] > div:nth-child(2) {
            display: flex;
            height: calc(100% - 76px);
            min-height: 0;
            flex-direction: column;
            background: #f8fafc;
        }

        [data-chatbot-messages] {
            min-height: 0;
            flex: 1 1 0%;
            overflow-y: auto;
            padding: 0.75rem 1rem;
        }

        [data-chatbot-message],
        [data-chatbot-typing] {
            margin-bottom: 0.5rem;
            border-radius: 1rem;
            padding: 0.5rem 0.75rem;
            font-size: 0.8125rem;
            line-height: 1.45;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
        }

        [data-chatbot-message="bot"],
        [data-chatbot-typing] {
            margin-right: 1.5rem;
            border-top-left-radius: 0.25rem;
            color: #334155;
            background: #fff;
            border: 1px solid #e2e8f0;
        }

        [data-chatbot-message="user"] {
            margin-left: 1.5rem;
            border-top-right-radius: 0.25rem;
            color: #fff;
            background: #2563eb;
        }

        [data-chatbot-panel] > div:nth-child(2) > div:last-child {
            flex-shrink: 0;
            border-top: 1px solid #e2e8f0;
            background: #fff;
            padding: 0.75rem 1rem;
        }

        [data-chatbot-actions] {
            display: flex;
            min-height: 1.75rem;
            align-items: center;
            gap: 0.375rem;
            margin-bottom: 0.75rem;
            overflow: hidden;
        }

        [data-chatbot-actions] button {
            max-width: 100%;
            border: 1px solid #dbeafe;
            border-radius: 9999px;
            background: #eff6ff;
            color: #1d4ed8;
            cursor: pointer;
            font-size: 0.75rem;
            font-weight: 500;
            line-height: 1rem;
            padding: 0.375rem 0.75rem;
        }

        [data-chatbot-actions] button:hover {
            border-color: #93c5fd;
            background: #dbeafe;
        }

        [data-chatbot-actions] button:disabled {
            cursor: not-allowed;
            opacity: 0.5;
        }

        [data-chatbot-form] {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        [data-chatbot-input] {
            min-width: 0;
            flex: 1 1 0%;
            border: 1px solid #cbd5e1;
            border-radius: 9999px;
            color: #1e293b;
            font-size: 0.875rem;
            line-height: 1.25rem;
            padding: 0.625rem 1rem;
        }

        [data-chatbot-form] button,
        [data-chatbot-toggle] {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            color: #fff;
            background: #2563eb;
            cursor: pointer;
        }

        [data-chatbot-form] button {
            width: 2.75rem;
            height: 2.75rem;
            flex-shrink: 0;
            border-radius: 9999px;
            box-shadow: 0 10px 18px rgba(37, 99, 235, 0.25);
        }

        [data-chatbot-close] {
            display: inline-flex;
            width: 2.25rem;
            height: 2.25rem;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 9999px;
            color: #fff;
            background: rgba(255, 255, 255, 0.12);
            cursor: pointer;
        }

        [data-chatbot-actions] button[data-question] {
            min-width: 0;
            flex: 1 1 0%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        [data-chatbot-toggle] {
            position: relative;
            width: 4rem;
            height: 4rem;
            border-radius: 9999px;
            box-shadow: 0 16px 28px rgba(29, 78, 216, 0.3);
        }

        [data-chatbot-toggle]:hover,
        [data-chatbot-form] button:hover {
            background: #1d4ed8;
        }

        [data-chatbot-toggle] > span:first-child {
            position: absolute;
            inset: 0;
            border-radius: 9999px;
            background: rgba(96, 165, 250, 0.4);
            animation: ebs-chatbot-pulse 1.8s cubic-bezier(0, 0, 0.2, 1) infinite;
        }

        [data-chatbot-toggle] > span:last-child {
            position: relative;
            display: flex;
            width: 4rem;
            height: 4rem;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
        }

        .sr-only[data-chatbot-input-label] {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        @keyframes ebs-chatbot-pulse {
            75%, 100% {
                opacity: 0;
                transform: scale(1.5);
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const greetingAnswer = 'Hai! 👋\nSaya eBantu Bot.\n\nUntuk mula, pilih kategori supaya saya boleh bantu anda dengan lebih tepat 😊';
            const unknownAnswer = 'Maaf, saya belum pasti tentang soalan itu. Sila pilih topik yang disediakan atau cuba tanya semula dengan lebih ringkas.';
            const aiFallbackAnswer = 'Maaf, saya belum dapat memberikan jawapan yang tepat buat masa ini. Sila cuba semula dengan soalan yang lebih ringkas atau hubungi pentadbir untuk bantuan lanjut.';
            const greetingKeywords = ['hai', 'hi', 'hello', 'salam', 'assalamualaikum'];
            const actionButtonClass = 'max-w-full rounded-full border border-blue-100 bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 transition hover:border-blue-300 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-200';
            const topicButtonClass = 'min-w-0 flex-1 basis-0 truncate whitespace-nowrap rounded-full border border-blue-100 bg-blue-50 px-2.5 py-1 text-[11px] font-medium leading-5 text-blue-700 transition hover:border-blue-300 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-200';
            const navButtonClass = 'flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-blue-200 bg-white text-xs font-semibold leading-none text-blue-700 shadow-sm transition hover:border-blue-300 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-200';

            const quickReplies = {
                pelajar: [
                    [
                        'Cara mohon bantuan',
                        'Jenis bantuan'
                    ],
                    [
                        'Dokumen diperlukan',
                        'Status permohonan'
                    ],
                    [
                        'Progress permohonan',
                        'Hubungi admin'
                    ]
                ],
                penderma: [
                    [
                        'Cara daftar penderma',
                        'Cara buat sumbangan'
                    ],
                    [
                        'Jenis sumbangan',
                        'Borang bantuan'
                    ],
                    [
                        'Sejarah sumbangan',
                        'Manfaat penderma'
                    ],
                    [
                        'Privasi penderma',
                        'Nama dipaparkan'
                    ]
                ]
            };

            const topics = {
                pelajar: [
                    {
                        label: 'Tentang sistem',
                        keywords: ['tentang sistem', 'ebantuansiswa', 'ebantuan siswa', 'apa sistem', 'sistem bantuan'],
                        answer: 'Tentang eBantuanSiswa UKM:\n\n• Sistem bantuan barangan untuk pelajar UKM.\n• Digunakan oleh pelajar UKM.\n• Bantuan dipohon secara dalam talian.'
                    },
                    {
                        label: 'Cara mohon bantuan',
                        keywords: ['cara mohon', 'mohon', 'permohonan', 'apply', 'hantar permohonan', 'buat permohonan'],
                        answer: 'Langkah permohonan bantuan adalah:\n\n1. Log masuk akaun.\n2. Pilih jenis bantuan.\n3. Lengkapkan borang.\n4. Muat naik dokumen.\n5. Hantar permohonan.'
                    },
                    {
                        label: 'Had permohonan',
                        keywords: ['had permohonan', 'berapa permohonan', 'satu bantuan', 'setiap semester', 'limit'],
                        answer: 'Had permohonan adalah:\n\n• Satu pelajar hanya boleh mohon satu jenis bantuan.\n• Had ini untuk setiap semester.\n• Pilih kategori yang paling diperlukan.'
                    },
                    {
                        label: 'Jenis bantuan',
                        keywords: ['jenis bantuan', 'kategori bantuan', 'kategori', 'senarai bantuan'],
                        answer: 'Kategori bantuan yang ada ialah:\n\n• Keperluan Asas\n• Alat Tulis Pembelajaran\n• Peralatan Pembelajaran\n• Sukan'
                    },
                    {
                        label: 'Keperluan asas',
                        keywords: ['keperluan asas', 'barangan harian', 'beras', 'minyak', 'gula', 'tepung', 'maggi', 'biskut'],
                        answer: 'Bantuan Keperluan Asas merangkumi:\n\n• Bantuan barangan harian.\n• Contoh: beras, minyak, gula, tepung, maggi dan biskut.\n• Pilih pakej mengikut keperluan.'
                    },
                    {
                        label: 'Pakej keperluan asas',
                        keywords: ['pakej keperluan asas', 'pakej asas', 'jumlah individu', 'pakej makanan'],
                        answer: 'Pilihan pakej keperluan asas adalah:\n\n• Pakej ikut jumlah individu.\n• Contoh: 1, 3, 5, 7 atau 10 orang.\n• Pilih pakej yang paling sesuai.'
                    },
                    {
                        label: 'Dokumen keperluan asas',
                        keywords: ['dokumen keperluan asas', 'dokumen asas', 'bukti pendapatan', 'surat tiada pendapatan', 'bukti alamat', 'bil utiliti', 'rumah sewa'],
                        answer: 'Dokumen untuk keperluan asas ialah:\n\n• Bukti pendapatan penjaga.\n• Surat tiada pendapatan jika berkaitan.\n• Bukti alamat seperti bil utiliti atau rumah sewa.'
                    },
                    {
                        label: 'Alat tulis pembelajaran',
                        keywords: ['alat tulis pembelajaran', 'alat tulis', 'stationery', 'pen', 'buku nota'],
                        answer: 'Bantuan alat tulis pembelajaran adalah untuk:\n\n• Keperluan alat tulis.\n• Contoh: pen, buku nota dan alat tulis asas.\n• Boleh mohon individu atau group.'
                    },
                    {
                        label: 'Individu atau group',
                        keywords: ['individu atau group', 'individu', 'group', 'persatuan', 'kelas', 'kumpulan'],
                        answer: 'Pilihan permohonan adalah:\n\n• Individu: untuk seorang pelajar.\n• Group / Persatuan / Kelas: untuk kegunaan bersama.\n• Pilih ikut tujuan permohonan.'
                    },
                    {
                        label: 'Dokumen kewangan pembelajaran',
                        keywords: ['dokumen kewangan pembelajaran', 'dokumen kewangan', 'slip gaji', 'surat pendapatan', 'dokumen individu'],
                        answer: 'Dokumen kewangan pembelajaran adalah:\n\n• Wajib untuk permohonan Individu.\n• Tidak perlu untuk Group / Persatuan / Kelas.\n• Upload slip gaji atau surat pendapatan jika individu.'
                    },
                    {
                        label: 'Surat sokongan fakulti',
                        keywords: ['surat sokongan fakulti', 'surat sokongan', 'fakulti', 'pensyarah', 'optional'],
                        answer: 'Surat sokongan fakulti bersifat optional:\n\n• Boleh bantu kuatkan permohonan.\n• Boleh dapatkan daripada fakulti atau pensyarah.\n• Upload jika berkaitan.'
                    },
                    {
                        label: 'Peralatan pembelajaran',
                        keywords: ['peralatan pembelajaran', 'peranti akademik', 'laptop', 'tablet', 'kalkulator saintifik'],
                        answer: 'Bantuan peralatan pembelajaran adalah untuk:\n\n• Peranti akademik.\n• Contoh: laptop, tablet dan kalkulator saintifik.\n• Pilih satu peralatan utama sahaja.'
                    },
                    {
                        label: 'Pilih satu peralatan',
                        keywords: ['pilih satu peralatan', 'satu peralatan', 'satu peranti', 'laptop atau tablet'],
                        answer: 'Syarat pemilihan peralatan adalah:\n\n• Hanya satu peralatan utama dibenarkan.\n• Pilih yang paling diperlukan.\n• Contoh: laptop atau tablet sahaja.'
                    },
                    {
                        label: 'Bukti kerosakan peralatan',
                        keywords: ['bukti kerosakan peralatan', 'bukti kerosakan', 'gambar rosak', 'peralatan rosak', 'peranti rosak'],
                        answer: 'Bukti kerosakan peralatan boleh disediakan begini:\n\n• Upload gambar peralatan rosak.\n• Sertakan bukti peranti tidak sesuai digunakan.\n• Pastikan gambar jelas.'
                    },
                    {
                        label: 'Sebutharga / quotation',
                        keywords: ['sebutharga', 'quotation', 'rujukan harga', 'harga'],
                        answer: 'Sebutharga atau quotation ialah dokumen sokongan:\n\n• Dokumen optional.\n• Digunakan sebagai rujukan harga.\n• Boleh upload jika ada.'
                    },
                    {
                        label: 'Justifikasi permohonan',
                        keywords: ['justifikasi permohonan', 'justifikasi', 'sebab bantuan', 'kenapa mohon'],
                        answer: 'Justifikasi permohonan boleh ditulis begini:\n\n• Nyatakan sebab bantuan diperlukan.\n• Contoh: tiada peranti, rosak atau berkongsi.\n• Tulis ringkas tetapi jelas.'
                    },
                    {
                        label: 'Peralatan sukan',
                        keywords: ['peralatan sukan', 'sukan', 'bola', 'raket', 'shuttlecock'],
                        answer: 'Bantuan peralatan sukan adalah untuk:\n\n• Aktiviti sukan pelajar.\n• Contoh: bola, raket dan shuttlecock.\n• Perlu berkaitan aktiviti rasmi atau penyertaan.'
                    },
                    {
                        label: 'Kategori sukan',
                        keywords: ['kategori sukan', 'kelab sukan', 'wakil ukm', 'pertandingan', 'aktiviti rasmi universiti'],
                        answer: 'Kategori permohonan sukan adalah:\n\n• Kelab / Persatuan Berdaftar UKM.\n• Wakil UKM ke pertandingan.\n• Program / Aktiviti Rasmi Universiti.'
                    },
                    {
                        label: 'Dokumen wajib sukan',
                        keywords: ['dokumen wajib sukan', 'dokumen sukan', 'surat kelulusan', 'surat penyertaan', 'pertandingan'],
                        answer: 'Dokumen wajib sukan ialah:\n\n• Surat kelulusan kelab / program.\n• Surat penyertaan aktiviti / pertandingan.\n• Pastikan dokumen jelas.'
                    },
                    {
                        label: 'Semak status',
                        keywords: ['status', 'semak', 'status permohonan', 'keputusan permohonan'],
                        answer: 'Cara semak status permohonan:\n\n• Log masuk akaun pelajar.\n• Buka Status Permohonan.\n• Semak keputusan terkini.'
                    },
                    {
                        label: 'Status sedang disemak',
                        keywords: ['status sedang disemak', 'sedang disemak', 'diproses', 'dokumen disemak'],
                        answer: 'Status sedang disemak bermaksud:\n\n• Permohonan sedang diproses.\n• Dokumen sedang disemak.\n• Tunggu keputusan pentadbir.'
                    },
                    {
                        label: 'Status diluluskan',
                        keywords: ['status diluluskan', 'diluluskan', 'lulus', 'berjaya diluluskan'],
                        answer: 'Status diluluskan bermaksud:\n\n• Permohonan berjaya diluluskan.\n• Bantuan diteruskan ke proses seterusnya.\n• Tunggu maklumat agihan.'
                    },
                    {
                        label: 'Status ditolak',
                        keywords: ['status ditolak', 'ditolak', 'tidak berjaya', 'reject'],
                        answer: 'Status ditolak bermaksud:\n\n• Permohonan tidak berjaya.\n• Biasanya kerana dokumen tidak lengkap atau tidak memenuhi syarat.\n• Semak semula maklumat permohonan.'
                    },
                    {
                        label: 'Progress permohonan',
                        keywords: ['progress permohonan', 'progres permohonan', 'perkembangan permohonan', 'proses permohonan', 'timeline'],
                        answer: 'Progress permohonan biasanya melalui:\n\n• Permohonan Dihantar.\n• Semakan Dokumen.\n• Menunggu Kelulusan.\n• Agihan Bantuan.'
                    },
                    {
                        label: 'Agihan bantuan',
                        keywords: ['agihan bantuan', 'pengambilan bantuan', 'ambil bantuan', 'maklumat agihan'],
                        answer: 'Agihan bantuan dibuat seperti berikut:\n\n• Dibuat selepas permohonan diluluskan.\n• Maklumat pengambilan akan dimaklumkan.\n• Sila pantau status permohonan.'
                    },
                    {
                        label: 'Dokumen diperlukan',
                        keywords: ['dokumen diperlukan', 'dokumen', 'upload', 'muat naik', 'sokongan', 'dokumen permohonan'],
                        answer: 'Dokumen diperlukan bergantung pada bantuan:\n\n• Contoh: bukti pendapatan, bukti alamat atau surat sokongan.\n• Pastikan fail jelas sebelum dihantar.\n• Semak syarat kategori sebelum upload.'
                    },
                    {
                        label: 'Lupa kata laluan',
                        keywords: ['lupa password', 'password', 'kata laluan', 'terlupa'],
                        answer: 'Jika lupa kata laluan:\n\n• Klik Lupa kata laluan?.\n• Masukkan emel berdaftar.\n• Ikut arahan tetapan semula.'
                    },
                    {
                        label: 'Hubungi admin',
                        keywords: ['admin', 'hubungi', 'pentadbir', 'contact admin', 'bantuan admin', 'maklumat admin'],
                        answer: 'Anda boleh menghubungi pihak pentadbir melalui:\n\n• Laman web: https://www.ukm.my\n• Emel: helpdeskdigital@ukm.edu.my\n• Telefon: 03-8921 4356\n\nPihak admin sedia membantu berkaitan bantuan pelajar dan sumbangan.'
                    }
                ],
                penderma: [
                    {
                        label: 'Dashboard penderma',
                        keywords: ['dashboard penderma', 'dashboard', 'paparan penderma', 'ringkasan sumbangan'],
                        answer: 'Dashboard penderma memaparkan ringkasan berikut:\n\n• Ringkasan sumbangan anda\n• Status sumbangan terkini\n• Impak sumbangan yang telah dibuat'
                    },
                    {
                        label: 'Cara daftar penderma',
                        keywords: ['daftar penderma', 'cara daftar', 'register donor', 'pendaftaran penderma', 'macam mana nak jadi penderma', 'cara jadi penderma', 'buka akaun penderma', 'verify akaun penderma'],
                        answer: 'Pendaftaran penderma dikendalikan oleh pihak pentadbir sistem.\n\n• Akaun penderma akan didaftarkan oleh admin terlebih dahulu.\n• Emel pengesahan akan dihantar kepada penderma.\n• Penderma perlu membuat verifikasi akaun melalui emel tersebut.\n• Selepas pengesahan berjaya, penderma boleh log masuk ke dalam sistem.\n\nJika anda berminat untuk menjadi penderma, sila hubungi pihak pentadbir untuk proses pendaftaran.'
                    },
                    {
                        label: 'Manfaat penderma',
                        keywords: ['apa saya dapat', 'apa penderma dapat', 'manfaat derma', 'kelebihan derma', 'benefit penderma', 'dapat apa kalau derma', 'pengiktirafan penderma', 'penghargaan penderma'],
                        answer: 'Sebagai penderma, anda akan mendapat:\n\n• Membantu pelajar UKM yang memerlukan\n• Pengiktirafan daripada pihak UKM\n• Nama penderma berpotensi dipaparkan pada halaman utama sistem sebagai penghargaan\n• Menjadi sebahagian daripada komuniti kebajikan pelajar UKM\n\nSetiap sumbangan anda membantu meringankan beban pelajar yang memerlukan bantuan.'
                    },
                    {
                        label: 'Privasi penderma',
                        keywords: ['anonymous', 'anon', 'tanpa nama', 'privasi', 'sembunyikan nama', 'nama dipaparkan', 'private donor'],
                        answer: 'Maklumat penderma dikendalikan secara profesional oleh sistem.\n\n• Nama penderma mungkin dipaparkan sebagai penghargaan daripada pihak UKM.\n• Paparan dibuat untuk menghargai sumbangan komuniti penderma.\n• Jika anda mempunyai keperluan privasi tertentu, sila hubungi pihak pentadbir untuk pertimbangan lanjut.'
                    },
                    {
                        label: 'Nama dipaparkan',
                        keywords: ['nama dipaparkan', 'nama keluar dekat main page', 'leaderboard', 'halaman utama', 'penghargaan umum', 'public recognition', 'penderma tertinggi', 'penyumbang aktif'],
                        answer: 'Nama penderma boleh dipaparkan sebagai penghargaan.\n\n• Paparan ini bertujuan menghargai sumbangan penderma.\n• Contohnya pada bahagian penderma tertinggi atau penyumbang aktif.\n• Paparan bergantung kepada rekod dan tetapan sistem.'
                    },
                    {
                        label: 'Cara buat sumbangan',
                        keywords: ['cara buat sumbangan', 'cara menyumbang', 'penderma', 'derma', 'sumbang', 'menyumbang', 'sumbangan'],
                        answer: 'Langkah membuat sumbangan adalah:\n\n1. Pilih kategori bantuan.\n2. Lihat item diperlukan.\n3. Tambah item ke senarai.\n4. Tetapkan kuantiti.\n5. Teruskan sumbangan.'
                    },
                    {
                        label: 'Jenis sumbangan',
                        keywords: ['jenis sumbangan', 'jenis bantuan', 'kategori sumbangan', 'kategori bantuan', 'item diperlukan'],
                        answer: 'Jenis sumbangan yang disediakan adalah:\n\n• Keperluan Asas\n• Pembelajaran\n• Sukan'
                    },
                    {
                        label: 'Lihat sumbangan',
                        keywords: ['lihat sumbangan', 'semak sumbangan', 'jumlah diperlukan', 'item disumbang'],
                        answer: 'Untuk melihat sumbangan yang diperlukan:\n\n• Klik Lihat Sumbangan.\n• Semak jumlah diperlukan.\n• Pilih item yang ingin disumbang.'
                    },
                    {
                        label: 'Tambah ke senarai',
                        keywords: ['tambah ke senarai', 'senarai sumbangan', 'troli', 'cart', 'tambah item'],
                        answer: 'Item boleh ditambah ke senarai sumbangan seperti berikut:\n\n• Masukkan item ke senarai sumbangan.\n• Senarai ini berfungsi seperti troli.\n• Item boleh diubah sebelum bayar atau serah.'
                    },
                    {
                        label: 'Ubah dalam senarai',
                        keywords: ['ubah dalam senarai', 'ubah senarai', 'tambah kuantiti', 'kurang kuantiti', 'buang item'],
                        answer: 'Dalam senarai sumbangan, anda boleh:\n\n• Tambah atau kurang kuantiti.\n• Buang item jika tidak mahu.\n• Jumlah dikira automatik.'
                    },
                    {
                        label: 'Borang bantuan',
                        keywords: ['borang bantuan', 'borang sumbangan', 'item dipilih', 'kuantiti dan harga'],
                        answer: 'Borang bantuan akan memaparkan:\n\n• Item yang dipilih.\n• Kuantiti dan harga.\n• Ringkasan untuk disemak sebelum terus menyumbang.'
                    },
                    {
                        label: 'Kuantiti sumbangan',
                        keywords: ['kuantiti sumbangan', 'kuantiti', 'butang tambah', 'butang kurang', 'jumlah dikira'],
                        answer: 'Cara tetapkan kuantiti sumbangan adalah:\n\n• Guna butang + atau -.\n• Jumlah dikira ikut kuantiti.\n• Pastikan kuantiti betul sebelum teruskan.'
                    },
                    {
                        label: 'Impak sumbangan',
                        keywords: ['impak sumbangan', 'bantuan diagihkan', 'kesan sumbangan', 'bukti agihan'],
                        answer: 'Dashboard penderma memaparkan ringkasan impak secara privasi selamat:\n\n• Jumlah bantuan yang telah diagihkan.\n• Unit dan kategori yang disokong.\n• Bukti agihan boleh dilihat jika agihan telah selesai.'
                    },
                    {
                        label: 'Sijil penghargaan',
                        keywords: ['sijil penghargaan', 'sijil penderma', 'certificate', 'penghargaan'],
                        answer: 'Sijil penghargaan boleh dimuat turun daripada dashboard penderma selepas sumbangan selesai direkodkan.'
                    },
                    {
                        label: 'Tahap pengiktirafan',
                        keywords: ['tahap pengiktirafan', 'level penderma', 'penyumbang prihatin', 'penaja utama'],
                        answer: 'Tahap pengiktirafan dikira berdasarkan jumlah sumbangan selesai dalam akaun penderma.'
                    },
                    {
                        label: 'Resit sumbangan',
                        keywords: ['resit sumbangan', 'resit', 'receipt', 'bukti bayaran'],
                        answer: 'Resit sumbangan tersedia untuk sumbangan yang telah selesai. Buka Sejarah Sumbangan dan pilih rekod berkaitan.'
                    },
                    {
                        label: 'Bukti agihan',
                        keywords: ['bukti agihan', 'lihat bukti', 'proof agihan', 'bukti bantuan'],
                        answer: 'Bukti agihan dipaparkan selepas bantuan selesai disalurkan. Paparan ini tidak menunjukkan maklumat peribadi pelajar.'
                    },
                    {
                        label: 'Tabung Bantuan Pelajar',
                        keywords: ['tabung bantuan pelajar', 'sumbangan tunai', 'sumbangan wang umum', 'cash donation', 'cash', 'tunai', 'wang umum', 'sumbang ke tabung', 'sumbangan tabung', 'tabung', 'toyyibpay'],
                        answer: 'Penderma boleh membuat sumbangan tunai melalui menu Tabung Bantuan Pelajar / Sumbang Ke Tabung.\n\nPembayaran dibuat secara dalam talian melalui ToyyibPay.\n\nSelepas pembayaran berjaya, penderma boleh melihat status dan memuat turun resit sumbangan.'
                    },
                    {
                        label: 'Pembayaran sumbangan',
                        keywords: ['pembayaran sumbangan', 'bayar', 'toyyibpay', 'bayaran online'],
                        answer: 'Pembayaran atas talian diproses melalui ToyyibPay. Status sumbangan akan dikemas kini selepas pembayaran disahkan.'
                    },
                    {
                        label: 'Sejarah sumbangan',
                        keywords: ['sejarah sumbangan', 'sejarah', 'rekod', 'rekod sumbangan', 'id sumbangan', 'detail sumbangan'],
                        answer: 'Sejarah sumbangan memaparkan:\n\n• Rekod semua sumbangan anda\n• ID sumbangan\n• Kategori bantuan\n• Jumlah sumbangan\n• Kaedah sumbangan\n• Status semasa sumbangan'
                    },
                    {
                        label: 'Status selesai',
                        keywords: ['status selesai', 'selesai', 'berjaya diproses', 'rekod lengkap'],
                        answer: 'Status selesai bermaksud:\n\n• Sumbangan berjaya diproses.\n• Rekod telah lengkap.\n• Tiada tindakan lanjut diperlukan.'
                    },
                    {
                        label: 'Status menunggu penghantaran',
                        keywords: ['status menunggu penghantaran', 'menunggu penghantaran', 'barang belum diserahkan', 'penghantaran'],
                        answer: 'Status menunggu penghantaran bermaksud:\n\n• Barang belum diserahkan.\n• Sila lengkapkan proses penghantaran.\n• Semak semula status selepas penghantaran dibuat.'
                    },
                    {
                        label: 'Status dalam semakan',
                        keywords: ['status dalam semakan', 'dalam semakan', 'maklumat disemak', 'pengesahan'],
                        answer: 'Status dalam semakan bermaksud:\n\n• Maklumat sumbangan sedang disemak.\n• Tunggu pengesahan sistem atau pentadbir.\n• Pastikan maklumat lengkap.'
                    },
                    {
                        label: 'Status ditolak',
                        keywords: ['status ditolak', 'ditolak', 'tidak berjaya', 'reject'],
                        answer: 'Status ditolak bermaksud:\n\n• Sumbangan tidak berjaya.\n• Mungkin maklumat tidak lengkap.\n• Semak semula rekod sumbangan.'
                    },
                    {
                        label: 'Impak sumbangan',
                        keywords: ['impak sumbangan', 'impak', 'kesan bantuan', 'unit disumbangkan', 'kategori dibantu'],
                        answer: 'Impak sumbangan menunjukkan kesan bantuan anda:\n\n• Jumlah unit yang disumbangkan.\n• Kategori yang telah dibantu.\n• Gambaran kesan sumbangan kepada pelajar.'
                    },
                    {
                        label: 'Lupa kata laluan',
                        keywords: ['lupa password', 'password', 'kata laluan', 'terlupa'],
                        answer: 'Jika lupa kata laluan, anda boleh cuba langkah ini:\n\n• Klik Lupa kata laluan?.\n• Masukkan emel berdaftar.\n• Ikut arahan tetapan semula.'
                    },
                    {
                        label: 'Hubungi admin',
                        keywords: ['admin', 'hubungi', 'pentadbir', 'contact admin', 'bantuan admin', 'maklumat admin'],
                        answer: 'Anda boleh menghubungi pihak pentadbir melalui:\n\n• Laman web: https://www.ukm.my\n• Emel: helpdeskdigital@ukm.edu.my\n• Telefon: 03-8921 4356\n\nPihak admin sedia membantu berkaitan bantuan pelajar dan sumbangan.'
                    }
                ]
            };

            const normalize = (value) => value
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9\s]/g, ' ')
                .replace(/\s+/g, ' ')
                .trim();

            const isGreeting = (query) => greetingKeywords.some((keyword) => query === keyword || query.startsWith(`${keyword} `));

            const formatMessage = (text) => {
                return text
                    // bold markdown
                    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')

                    // bullet points
                    .replace(/^\* (.*$)/gm, '<li>$1</li>')

                    // line breaks
                    .replace(/\n/g, '<br>')

                    // wrap list
                    .replace(/(<li>.*<\/li>)/gs, '<ul class="list-disc pl-5 space-y-1 my-2">$1</ul>');
            };

            const appendMessage = (messages, text, role) => {
                const bubble = document.createElement('div');
                bubble.innerHTML = formatMessage(text);
                bubble.dataset.chatbotMessage = role;

                if (role === 'user') {
                    bubble.className = 'ml-6 whitespace-pre-line rounded-2xl rounded-tr-sm bg-blue-600 px-3 py-2 text-xs leading-relaxed text-white shadow-sm sm:text-sm';
                } else {
                    bubble.className = 'mr-6 whitespace-pre-line rounded-2xl rounded-tl-sm bg-white px-3 py-2 text-xs leading-relaxed text-slate-700 shadow-sm ring-1 ring-slate-200 prose prose-sm sm:text-sm';
                }

                messages.appendChild(bubble);
                messages.scrollTop = messages.scrollHeight;

                return bubble;
            };

            const removeTypingIndicator = (typingIndicator) => {
                if (typingIndicator?.parentNode) {
                    typingIndicator.remove();
                }
            };

            const showTypingIndicator = (messages) => {
                const bubble = document.createElement('div');
                bubble.className = 'mr-6 flex w-fit items-center gap-2 rounded-2xl rounded-tl-sm bg-white px-3 py-2 text-xs leading-relaxed text-slate-600 shadow-sm ring-1 ring-slate-200 sm:text-sm';
                bubble.dataset.chatbotTyping = 'true';
                bubble.setAttribute('role', 'status');
                bubble.setAttribute('aria-label', 'eBantu Bot sedang menaip...');
                bubble.innerHTML = `
                    <span class="flex items-center gap-1" aria-hidden="true">
                        <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-blue-400"></span>
                        <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-blue-400 [animation-delay:120ms]"></span>
                        <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-blue-400 [animation-delay:240ms]"></span>
                    </span>
                    <span>eBantu Bot sedang menaip...</span>
                `;

                messages.appendChild(bubble);
                messages.scrollTop = messages.scrollHeight;

                return bubble;
            };

            const createActionButton = (label, datasetKey, datasetValue) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = actionButtonClass;
                button.textContent = label;
                button.title = label;
                button.dataset[datasetKey] = datasetValue;

                return button;
            };

            const getRoleReply = (role) => role === 'pelajar'
                ? 'Baik, sila pilih topik pelajar di bawah.'
                : 'Baik, sila pilih topik penderma di bawah.';

            const getWords = (value) => normalize(value)
                .split(' ')
                .filter((word) => word.length > 2);

            const scoreTopic = (topic, query) => {
                const label = normalize(topic.label);
                const keywords = topic.keywords.map((keyword) => normalize(keyword));
                let score = 0;

                if (query === label) {
                    score += 100;
                }

                if (query.includes(label) && query !== label) {
                    score += 70;
                }

                keywords.forEach((keyword) => {
                    if (query === keyword) {
                        score += 45;
                    } else if (query.includes(keyword)) {
                        score += 35;
                    }
                });

                const searchableWords = new Set(getWords([topic.label, ...topic.keywords].join(' ')));
                const queryWords = new Set(getWords(query));

                queryWords.forEach((word) => {
                    if (searchableWords.has(word)) {
                        score += 6;
                    }
                });

                return score;
            };

            document.querySelectorAll('[data-ebs-chatbot]').forEach((widget, index) => {
                let selectedRole = null;
                const quickReplyPage = {
                    pelajar: 0,
                    penderma: 0
                };

                const panel = widget.querySelector('[data-chatbot-panel]');
                const toggle = widget.querySelector('[data-chatbot-toggle]');
                const close = widget.querySelector('[data-chatbot-close]');
                const form = widget.querySelector('[data-chatbot-form]');
                const input = widget.querySelector('[data-chatbot-input]');
                const inputLabel = widget.querySelector('[data-chatbot-input-label]');
                const messages = widget.querySelector('[data-chatbot-messages]');
                const actions = widget.querySelector('[data-chatbot-actions]');
                const inputId = `ebs-chatbot-input-${index + 1}`;

                input.id = inputId;
                inputLabel?.setAttribute('for', inputId);

                const renderRoleButtons = () => {
                    actions.innerHTML = '';
                    actions.appendChild(createActionButton('Pelajar', 'role', 'pelajar'));
                    actions.appendChild(createActionButton('Penderma', 'role', 'penderma'));
                };

                const renderTopicButtons = (role) => {
                    const groups = quickReplies[role] || [];
                    const maxPage = Math.max(groups.length - 1, 0);
                    const currentPage = Math.min(Math.max(quickReplyPage[role] || 0, 0), maxPage);

                    quickReplyPage[role] = currentPage;
                    actions.innerHTML = '';

                    if (groups.length > 1) {
                        const previousButton = createActionButton('<', 'quickReplyNav', 'previous');
                        previousButton.className = navButtonClass;
                        previousButton.disabled = currentPage === 0;

                        if (previousButton.disabled) {
                            previousButton.classList.add('cursor-not-allowed', 'opacity-50');
                        }

                        actions.appendChild(previousButton);
                    }

                    (groups[currentPage] || []).forEach((label) => {
                        const topicButton = createActionButton(label, 'question', label);
                        topicButton.className = topicButtonClass;
                        actions.appendChild(topicButton);
                    });

                    if (groups.length > 1) {
                        const nextButton = createActionButton('>', 'quickReplyNav', 'next');
                        nextButton.className = navButtonClass;
                        nextButton.disabled = currentPage === maxPage;

                        if (nextButton.disabled) {
                            nextButton.classList.add('cursor-not-allowed', 'opacity-50');
                        }

                        actions.appendChild(nextButton);
                    }
                };

                const activateRole = (role) => {
                    selectedRole = role;
                    quickReplyPage[role] = 0;
                    renderTopicButtons(role);
                };

                const setRole = (role, shouldEcho = true) => {
                    activateRole(role);

                    if (shouldEcho) {
                        appendMessage(messages, role === 'pelajar' ? 'Pelajar' : 'Penderma', 'user');
                    }

                    appendMessage(messages, getRoleReply(role), 'bot');
                };

                const findAnswer = async (question) => {
                    const cleanQuestion = String(question || '').trim();

                    if (!cleanQuestion) {
                        return unknownAnswer;
                    }

                    try {
                        const response = await fetch('/chatbot/gemini', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                question: selectedRole
                                    ? `[Peranan dipilih: ${selectedRole}]\n${cleanQuestion}`
                                    : cleanQuestion
                            })
                        });

                        let data = {};

                        try {
                            data = await response.json();
                        } catch (parseError) {
                            console.error('Gemini response parse error:', parseError);
                        }

                        if (!response.ok) {
                            console.error('Gemini route error:', data);
                            return data.answer || data.message || data.error || aiFallbackAnswer;
                        }

                        return data.answer || aiFallbackAnswer;
                    } catch (error) {
                        console.error('Gemini fetch error:', error);
                        return aiFallbackAnswer;
                    }
                };

                const askQuestion = (question) => {
                    const cleanQuestion = String(question || '').trim();

                    if (!cleanQuestion) {
                        return;
                    }

                    appendMessage(messages, cleanQuestion, 'user');
                    input.value = '';
                    const typingIndicator = showTypingIndicator(messages);

                    setTimeout(async () => {
                        let answer = unknownAnswer;

                        try {
                            answer = await findAnswer(cleanQuestion);
                        } catch (error) {
                            console.error('Chatbot answer error:', error);
                        } finally {
                            removeTypingIndicator(typingIndicator);
                        }

                        appendMessage(messages, answer || unknownAnswer, 'bot');
                    }, 160);
                };

                const setOpen = (isOpen) => {
                    panel.classList.toggle('hidden', !isOpen);
                    toggle.setAttribute('aria-expanded', String(isOpen));

                    if (isOpen) {
                        setTimeout(() => input.focus(), 80);
                    }
                };

                renderRoleButtons();

                toggle.addEventListener('click', () => {
                    setOpen(panel.classList.contains('hidden'));
                });

                close.addEventListener('click', () => setOpen(false));

                form.addEventListener('submit', (event) => {
                    event.preventDefault();
                    askQuestion(input.value);
                });

                actions.addEventListener('click', (event) => {
                    const button = event.target.closest('button');

                    if (!button) {
                        return;
                    }

                    if (button.dataset.quickReplyNav && selectedRole) {
                        const direction = button.dataset.quickReplyNav;
                        quickReplyPage[selectedRole] += direction === 'next' ? 1 : -1;
                        renderTopicButtons(selectedRole);
                        return;
                    }

                    if (button.dataset.role) {
                        setRole(button.dataset.role);
                        return;
                    }

                    if (button.dataset.question) {
                        askQuestion(button.dataset.question);
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        setOpen(false);
                    }
                });
            });
        });
    </script>
@endonce
