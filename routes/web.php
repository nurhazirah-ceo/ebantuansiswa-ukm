<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Donor;
use App\Models\Item;
use App\Models\Permohonan;
use App\Models\Sumbangan;
use App\Support\AssistanceCatalog;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DonorController;
use App\Http\Controllers\Admin\AgihanController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\PermohonanSemakanController;
use App\Http\Controllers\Admin\SumbanganController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PermohonanController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\CashDonationController;
use App\Http\Controllers\PendermaController;
use App\Http\Controllers\Admin\CashDonationController as AdminCashDonationController;



/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/
Route::get('/', function () {

    $homepageDonors = Donor::with('user')
        ->where('show_on_homepage', 1)
        ->orderBy('homepage_order', 'asc')
        ->limit(4)
        ->get();

    $homeBantuanItems = AssistanceCatalog::groupedActiveItems();
    $learningItems = $homeBantuanItems->get(Item::CATEGORY_PEMBELAJARAN, collect());
    $homeLearningStationeryItems = AssistanceCatalog::learningStationeryItems($learningItems);
    $homeLearningEquipmentItems = AssistanceCatalog::learningEquipmentItems($learningItems);

    return view('home', compact(
        'homepageDonors',
        'homeBantuanItems',
        'homeLearningStationeryItems',
        'homeLearningEquipmentItems'
    ));
});

/*
|--------------------------------------------------------------------------
| Login
|--------------------------------------------------------------------------
*/
Route::get('/login', [LoginController::class, 'show'])
    ->name('login');

Route::post('/login', [LoginController::class, 'login'])
    ->middleware('throttle:5,1');
/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->get(
    '/dashboard',
    [DashboardController::class, 'index']
)->name('dashboard');

Route::middleware(['auth', 'verified', 'role:pelajar'])->get(
    '/dashboard/pelajar',
    [DashboardController::class, 'pelajar']
)->name('dashboard.pelajar');

Route::middleware(['auth', 'role:penderma'])->get(
    '/dashboard/penderma',
    [DashboardController::class, 'penderma']
)->name('dashboard.penderma');

Route::middleware(['auth', 'role:admin'])->get(
    '/dashboard/admin',
    [DashboardController::class, 'admin']
)->name('dashboard.admin');

/*
|--------------------------------------------------------------------------
| Pelajar – Permohonan + Jenis Bantuan
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:pelajar'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Permohonan
    |--------------------------------------------------------------------------
    */
Route::get('/permohonan', [PermohonanController::class, 'index'])
    ->name('permohonan.index');

Route::post('/permohonan', [PermohonanController::class, 'store'])
    ->name('permohonan.store');

Route::get('/status', [StatusController::class, 'index'])
    ->name('status.index');

    /*
    |--------------------------------------------------------------------------
    | Jenis Bantuan Main Page
    |--------------------------------------------------------------------------
    */
    Route::get('/bantuan', function () {
        $catalogItems = AssistanceCatalog::groupedActiveItems();
        $basicPreviewItems = collect(['Beras', 'Minyak masak', 'Biskut']);

        return view('pelajar.jenis-bantuan.jenis-bantuan', [
            'basicPreviewItems' => $basicPreviewItems,
            'learningPreviewItems' => AssistanceCatalog::categoryPreview($catalogItems->get(Item::CATEGORY_PEMBELAJARAN, collect())),
            'sportsPreviewItems' => AssistanceCatalog::categoryPreview($catalogItems->get(Item::CATEGORY_SUKAN, collect())),
        ]);
    })->name('bantuan.index');

    /*
    |--------------------------------------------------------------------------
    | Keperluan Asas
    |--------------------------------------------------------------------------
    */
    Route::get('/bantuan/asas', function () {
        return view('pelajar.jenis-bantuan.bantuan-asas', [
            'items' => AssistanceCatalog::activeItems(Item::CATEGORY_KEPERLUAN_ASAS),
        ]);
    })->name('bantuan.asas');

    /*
    |--------------------------------------------------------------------------
    | Pembelajaran
    |--------------------------------------------------------------------------
    */
    Route::get('/bantuan/pembelajaran', function () {
        $learningItems = AssistanceCatalog::activeItems(Item::CATEGORY_PEMBELAJARAN);

        return view('pelajar.jenis-bantuan.pembelajaran', [
            'stationeryPreviewItems' => AssistanceCatalog::categoryPreview(AssistanceCatalog::learningStationeryItems($learningItems)),
            'equipmentPreviewItems' => AssistanceCatalog::categoryPreview(AssistanceCatalog::learningEquipmentItems($learningItems)),
        ]);
    })->name('pembelajaran');

    Route::redirect('/pembelajaran', '/bantuan/pembelajaran');

    Route::get('/bantuan/pembelajaran/alat-tulis', function () {
        return view('pelajar.jenis-bantuan.alat-tulis', [
            'items' => AssistanceCatalog::learningStationeryItems(),
        ]);
    })->name('alat.tulis');

    Route::redirect('/alat-tulis', '/bantuan/pembelajaran/alat-tulis');

    Route::get('/bantuan/pembelajaran/peralatan', function () {
        return view('pelajar.jenis-bantuan.peralatan', [
            'items' => AssistanceCatalog::learningEquipmentItems(),
        ]);
    })->name('peralatan');

    Route::redirect('/peralatan', '/bantuan/pembelajaran/peralatan');

    Route::get('/bantuan/pembelajaran/peralatan/mohon/{item}', function ($item) {
        return redirect()->route('permohonan.index', [
            'jenis' => 'bantuan_pembelajaran',
            'kategori' => 'peralatan_pembelajaran',
            'item' => $item,
        ]);
    })->name('peralatan.mohon');

    Route::get('/peralatan/mohon/{item}', function ($item) {
        return redirect()->route('peralatan.mohon', ['item' => $item]);
    });

    /*
    |--------------------------------------------------------------------------
    | Sukan
    |--------------------------------------------------------------------------
    */
    Route::get('/bantuan/sukan', function () {
        return view('pelajar.jenis-bantuan.sukan', [
            'items' => AssistanceCatalog::activeItems(Item::CATEGORY_SUKAN),
        ]);
    })->name('sukan');

    Route::redirect('/sukan', '/bantuan/sukan');

    /*
    |--------------------------------------------------------------------------
    | Jenis Bantuan Extra Page
    |--------------------------------------------------------------------------
    */
    Route::get('/jenis-bantuan', function () {
        $catalogItems = AssistanceCatalog::groupedActiveItems();
        $basicPreviewItems = collect(['Beras', 'Minyak masak', 'Biskut']);

        return view('pelajar.jenis-bantuan.jenis-bantuan', [
            'basicPreviewItems' => $basicPreviewItems,
            'learningPreviewItems' => AssistanceCatalog::categoryPreview($catalogItems->get(Item::CATEGORY_PEMBELAJARAN, collect())),
            'sportsPreviewItems' => AssistanceCatalog::categoryPreview($catalogItems->get(Item::CATEGORY_SUKAN, collect())),
        ]);
    })->name('jenis.bantuan');
    /*
    |--------------------------------------------------------------------------
    | Status - Lihat Permohonan
    |--------------------------------------------------------------------------
    */
    Route::get('/status-permohonan', [StatusController::class, 'index'])
        ->name('status-permohonan.index');

    Route::get('/status-permohonan/{id}', [StatusController::class, 'show'])
        ->whereNumber('id')
        ->name('status-permohonan.show');
});

Route::middleware('auth')->get('/permohonan/dokumen/{dokumen}', [StatusController::class, 'document'])
    ->whereNumber('dokumen')
    ->name('permohonan.dokumen.show');
/*
|--------------------------------------------------------------------------
| Penderma -Sumbangan
|--------------------------------------------------------------------------
*/

Route::get('/penderma/sumbangan', [PendermaController::class, 'sumbangan'])
    ->name('penderma.sumbangan');

Route::get('/penderma/keperluan-sumbang', [PendermaController::class, 'keperluanSumbang'])
    ->name('penderma.keperluan-sumbang');

Route::get('/penderma/pembelajaran-sumbang', [PendermaController::class, 'pembelajaranSumbang'])
    ->name('penderma.pembelajaran-sumbang');

Route::get('/penderma/sukan-sumbang', [PendermaController::class, 'sukanSumbang'])
    ->name('penderma.sukan-sumbang');

Route::get('/penderma/menyumbang-bantuan', [PendermaController::class, 'menyumbangBantuan'])
    ->name('penderma.menyumbang-bantuan');

Route::get('/penderma/tabung', [CashDonationController::class, 'create'])
    ->middleware(['auth', 'role:penderma'])
    ->name('penderma.tabung');

Route::post('/penderma/tabung', [CashDonationController::class, 'store'])
    ->middleware(['auth', 'role:penderma'])
    ->name('penderma.tabung.store');

Route::get('/penderma/tabung/{cashDonation}/status', [CashDonationController::class, 'status'])
    ->middleware(['auth', 'role:penderma'])
    ->whereNumber('cashDonation')
    ->name('penderma.tabung.status');

Route::get('/penderma/tabung/{cashDonation}/resit', [CashDonationController::class, 'receipt'])
    ->middleware(['auth', 'role:penderma'])
    ->whereNumber('cashDonation')
    ->name('penderma.tabung.receipt');

Route::get('/penderma/tabung/{cashDonation}/resit/download', [CashDonationController::class, 'downloadReceipt'])
    ->middleware(['auth', 'role:penderma'])
    ->whereNumber('cashDonation')
    ->name('penderma.tabung.receipt.download');

Route::get('/penderma/tabung/return', [CashDonationController::class, 'return'])
    ->middleware(['auth', 'role:penderma'])
    ->name('penderma.tabung.return');

Route::post('/penderma/tabung/callback', [CashDonationController::class, 'callback'])
    ->name('penderma.tabung.callback');

Route::post('/penderma/sumbangan/store', [PendermaController::class, 'storeSumbangan'])
    ->middleware(['auth', 'role:penderma'])
    ->name('penderma.sumbangan.store');

Route::post('/penderma/toyyibpay/callback', [PendermaController::class, 'toyyibPayCallback'])
    ->name('penderma.toyyibpay.callback');

Route::get('/penderma/toyyibpay/return', [PendermaController::class, 'toyyibPayReturn'])
    ->middleware(['auth', 'role:penderma'])
    ->name('penderma.toyyibpay.return');

Route::get('/penderma/checkout-sumbangan', [PendermaController::class, 'checkoutSumbangan'])
    ->name('penderma.checkout-sumbangan');

Route::get('/penderma/sumbangan/{id}/resit', [PendermaController::class, 'receiptSumbangan'])
    ->middleware(['auth', 'role:penderma'])
    ->name('penderma.sumbangan.receipt');

Route::get('/penderma/sumbangan/{id}/resit/download', [PendermaController::class, 'downloadReceiptSumbangan'])
    ->middleware(['auth', 'role:penderma'])
    ->name('penderma.sumbangan.receipt.download');

Route::get('/penderma/sijil-penghargaan', [PendermaController::class, 'sijilPenghargaan'])
    ->middleware(['auth', 'role:penderma'])
    ->name('penderma.sijil-penghargaan');

Route::get('/penderma/sijil-penghargaan/download', [PendermaController::class, 'downloadSijilPenghargaan'])
    ->middleware(['auth', 'role:penderma'])
    ->name('penderma.sijil-penghargaan.download');

Route::get('/penderma/sejarah-sumbangan/{sumbangan}/agihan/{permohonan}/bukti', [PendermaController::class, 'buktiAgihan'])
    ->middleware(['auth', 'role:penderma'])
    ->name('penderma.agihan-bukti');

Route::get('/penderma/sasaran-sumbangan', function () {
    return redirect()
        ->route('dashboard.penderma')
        ->with('info', 'Halaman tersebut telah digantikan dengan dashboard impak sumbangan.');
})->name('penderma.sasaran-sumbangan');

Route::get('/penderma/sejarah-sumbangan', [PendermaController::class, 'sejarahSumbangan'])
    ->middleware(['auth', 'role:penderma'])
    ->name('penderma.sejarah-sumbangan');

Route::get('/penderma/sejarah-sumbangan/export', [PendermaController::class, 'exportSejarahSumbangan'])
    ->middleware(['auth', 'role:penderma'])
    ->name('penderma.sejarah-sumbangan.export');

Route::get('/penderma/sejarah-sumbangan/{id}', [PendermaController::class, 'sejarahSumbanganShow'])
    ->middleware(['auth', 'role:penderma'])
    ->name('penderma.sejarah-sumbangan.show');


/*
|--------------------------------------------------------------------------
| Profile
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| ADMIN – PENGURUSAN PENDERMA
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/penderma', [DonorController::class, 'landing'])
            ->name('penderma.landing');

        Route::get('/permohonan', [PermohonanSemakanController::class, 'index'])
            ->name('permohonan.index');

        Route::get('/permohonan/status', [PermohonanSemakanController::class, 'status'])
            ->name('permohonan.status');

        Route::get('/permohonan/{permohonan}', [PermohonanSemakanController::class, 'show'])
            ->name('permohonan.show');

        Route::patch('/permohonan/{permohonan}/keputusan', [PermohonanSemakanController::class, 'keputusan'])
            ->name('permohonan.keputusan');

        Route::get('/agihan', [AgihanController::class, 'index'])
            ->name('agihan.index');

        Route::get('/agihan/{permohonan}/bukti', [AgihanController::class, 'bukti'])
            ->name('agihan.bukti');

        Route::patch('/agihan/{permohonan}/mula', [AgihanController::class, 'mula'])
            ->name('agihan.mula');

        Route::patch('/agihan/{permohonan}/selesai', [AgihanController::class, 'selesai'])
            ->name('agihan.selesai');

        Route::get('/penderma/list', [DonorController::class, 'index'])
            ->name('penderma.index');

        Route::get('/sumbangan', [SumbanganController::class, 'index'])
            ->name('sumbangan.index');

        Route::post('/sumbangan', [SumbanganController::class, 'store'])
            ->name('sumbangan.store');

        Route::patch('/sumbangan/{item}', [SumbanganController::class, 'update'])
            ->name('sumbangan.update');

        Route::patch('/sumbangan/{item}/remove', [SumbanganController::class, 'remove'])
            ->name('sumbangan.remove');

        Route::get('/tabung', [AdminCashDonationController::class, 'index'])
            ->name('tabung.index');

        Route::patch('/tabung/target', [AdminCashDonationController::class, 'updateTarget'])
            ->name('tabung.target.update');

        Route::get('/laporan', [LaporanController::class, 'index'])
            ->name('laporan.index');

        Route::get('/laporan/agihan', [AgihanController::class, 'laporan'])
            ->name('laporan.agihan');

        Route::get('/laporan/agihan/export-csv', [AgihanController::class, 'laporanCsv'])
            ->name('laporan.agihan.csv');

        Route::get('/statistik/permohonan', [PermohonanSemakanController::class, 'statistik'])
            ->name('statistik.permohonan');

        Route::get('/statistik/permohonan/export-csv', [PermohonanSemakanController::class, 'statistikCsv'])
            ->name('statistik.permohonan.csv');

        Route::get('/statistik/sumbangan', [SumbanganController::class, 'statistik'])
            ->name('statistik.sumbangan');

        Route::get('/statistik/sumbangan/export-csv', [SumbanganController::class, 'statistikCsv'])
            ->name('statistik.sumbangan.csv');

        Route::get('/statistik/inventori', [LaporanController::class, 'inventori'])
            ->name('statistik.inventori');

        Route::get('/statistik/inventori/export-csv', [LaporanController::class, 'inventoriCsv'])
            ->name('statistik.inventori.csv');

        Route::get('/penderma/create', [DonorController::class, 'create'])
            ->name('penderma.create');

        Route::post('/penderma', [DonorController::class, 'store'])
            ->name('penderma.store');

Route::get('/penderma/{user}', [DonorController::class, 'show'])
    ->name('penderma.show');

Route::get('/penderma/{user}/edit', [DonorController::class, 'edit'])
    ->name('penderma.edit');

Route::match(['put', 'patch'], '/penderma/{user}', [DonorController::class, 'update'])
    ->name('penderma.update');

    
        Route::post('/penderma/{user}/activate',
            [DonorController::class, 'activate']
        )->name('penderma.activate');

        Route::post('/penderma/{user}/resend',
            [DonorController::class, 'resend']
        )->name('penderma.resend');

        Route::delete('/penderma/{user}',
            [DonorController::class, 'destroy']
        )->name('penderma.destroy');

        Route::post('/penderma/check-email', function (Request $request) {
            return response()->json([
                'exists' => User::where('email', $request->email)->exists()
            ]);
        })->name('penderma.check-email');
    });

    



/*
|--------------------------------------------------------------------------
| Chatbot - Gemini API Fallback
|--------------------------------------------------------------------------
*/
Route::post('/chatbot/gemini', function (Request $request) {
    $fallbackAnswer = 'Maaf, saya belum dapat memberikan jawapan yang tepat buat masa ini. Sila cuba semula dengan soalan yang lebih ringkas atau hubungi pentadbir untuk bantuan lanjut.';
    $question = trim((string) $request->input('question'));

    if ($question === '') {
        return response()->json([
            'answer' => 'Sila masukkan soalan terlebih dahulu supaya eBantu Bot boleh membantu anda dengan tepat.'
        ], 422);
    }

    $apiKey = config('services.gemini.api_key');
    $apiKey = is_string($apiKey) ? trim($apiKey) : '';

    Log::info('Gemini chatbot API key configuration checked.', [
        'api_key_configured' => $apiKey !== '',
        'gemini_api_key_detected' => $apiKey !== '',
        'source' => 'config:services.gemini.api_key',
    ]);

    if ($apiKey === '') {
        Log::warning('Gemini chatbot request skipped because API key is not configured.');

        return response()->json([
            'answer' => 'Maaf, perkhidmatan AI belum tersedia kerana tetapan sistem belum lengkap. Sila hubungi pentadbir untuk bantuan lanjut.'
        ], 500);
    }

    $prompt = <<<PROMPT
Anda ialah eBantu Bot untuk sistem eBantuan Siswa UKM.
Jawab dalam Bahasa Melayu yang profesional, sopan, ringkas dan jelas.
Sentiasa berikan jawapan yang sesuai. Jangan pulangkan jawapan kosong.
Jika maklumat tidak mencukupi, nyatakan batasan dengan sopan dan cadangkan pengguna menghubungi pentadbir.

Jawab HANYA berkaitan sistem eBantuan Siswa UKM berdasarkan maklumat berikut:
- Sistem ini membantu pelajar UKM memohon bantuan dan penderma menyalurkan sumbangan.
- Kategori bantuan ialah Keperluan Asas, Alat Tulis Pembelajaran, Peralatan Pembelajaran dan Sukan.
-DOKUMEN KHUSUS:

Keperluan Asas:
- bukti pendapatan
- surat tiada pendapatan
- bil utiliti
- bukti alamat

Alat Tulis Pembelajaran:
- individu perlu dokumen kewangan
- group/persatuan tidak perlu dokumen kewangan

Peralatan Pembelajaran:
- boleh mohon laptop
- tablet
- kalkulator saintifik
- boleh upload bukti kerosakan

Sukan:
- perlukan surat penyertaan
- surat kelulusan aktiviti

- Pelajar boleh mendaftar akaun, log masuk, membuat permohonan bantuan, memuat naik dokumen sokongan dan menyemak status permohonan.
- Penderma boleh log masuk, melihat keperluan bantuan dan membuat sumbangan barangan fizikal.
- Penderma boleh membuat sumbangan tunai melalui Tabung Bantuan Pelajar menggunakan ToyyibPay.
- Penderma boleh melihat sejarah sumbangan, resit dan sijil penghargaan jika berkaitan.
- Pentadbir mengurus permohonan, sumbangan, agihan bantuan, inventori dan laporan.
- Jika soalan di luar skop atau maklumat tidak tersedia, jawab: "Maaf, saya hanya boleh membantu berkaitan sistem eBantuan Siswa UKM. Sila hubungi pentadbir untuk maklumat lanjut."

Soalan pengguna:
{$question}
PROMPT;

    try {
        Log::info('Gemini chatbot API request sending.', [
            'request_sent' => true,
        ]);

        $response = Http::timeout(20)->post(
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey,
            [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]
        );

        Log::info('Gemini chatbot API response received.', [
            'status' => $response->status(),
            'successful' => $response->successful(),
        ]);

        if ($response->failed()) {
            Log::warning('Gemini chatbot API returned an error response.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return response()->json([
                'answer' => 'Maaf, perkhidmatan AI tidak dapat dihubungi buat masa ini. Sila cuba semula sebentar lagi.'
            ], 502);
        }

        $data = $response->json();

        $answer = trim((string) data_get(
            $data,
            'candidates.0.content.parts.0.text'
        ));

        if ($answer === '') {
            $answer = $fallbackAnswer;
        }

       return response()->json([
            'answer' => $answer
        ]);
        
    } catch (Throwable $e) {
        Log::error('Gemini chatbot API request failed with an exception.', [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
        ]);

        return response()->json([
            'answer' => 'Maaf, berlaku masalah semasa menghubungi perkhidmatan AI. Sila cuba semula sebentar lagi.'
        ], 500);
    }
})->middleware('throttle:20,1')->name('chatbot.gemini');

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
