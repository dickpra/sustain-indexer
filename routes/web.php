<?php

use Illuminate\Support\Facades\Route;

// Import semua controller yang sudah dipecah
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\DocumentDisplayController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\PdfSubmissionController;
use App\Http\Controllers\XmlSubmissionController;
use App\Http\Controllers\AuthorInstitutionController;
use App\Http\Controllers\DocumentEditController;
use App\Http\Controllers\BetaSubmitController;
use App\Http\Controllers\JournalController;

// ==========================================
// BERANDA & PENCARIAN
// ==========================================
Route::get('/', function () {
    // 1. Top Articles (Most Cited & Most Popular)
    $mostCited = \App\Models\Document::where('is_verified', true)->orderBy('citation_count', 'desc')->take(3)->get();
    $mostPopular = \App\Models\Document::where('is_verified', true)->orderBy('views', 'desc')->take(3)->get();

    // 2. 🔥 TOP RESEARCHERS
    $topResearchers = \App\Models\Author::withCount('documents')
                        ->orderBy('documents_count', 'desc')
                        ->take(5)
                        ->get();

    // 3. 🔥 TOP INSTITUTIONS
    $topInstitutions = \App\Models\Institution::withCount('authors')
                         ->orderBy('authors_count', 'desc')
                         ->take(5)
                         ->get();

    // 4. Global Stats
    $totalDocuments = \App\Models\Document::count();
    $totalAuthors = \App\Models\Author::count();
    $totalInstitutions = \App\Models\Institution::count();
    $totalCountries = \App\Models\Institution::whereNotNull('country')->distinct('country')->count();

    return view('home', compact(
        'mostCited', 'mostPopular', 'topResearchers', 'topInstitutions',
        'totalDocuments', 'totalAuthors', 'totalInstitutions', 'totalCountries'
    )); 
});

// Route::get('/results', [HomeController::class, 'index']); // Menggantikan DocumentController@index
// Route::get('/search', [SearchController::class, 'search']);

Route::get('/results', [SearchController::class, 'search'])->name('search.results');

// ==========================================
// 1. FITUR EDIT DOKUMEN (Letakkan di ATAS rute show!)
// ==========================================
Route::post('/document/{id}/request-edit', [DocumentEditController::class, 'requestEdit']);

Route::get('/document/{id}/secure-edit', [DocumentEditController::class, 'editForm'])
    ->name('document.edit')
    ->middleware('signed');

Route::post('/document/{id}/update', [DocumentEditController::class, 'updateDocument']);


// ==========================================
// 2. TAMPILAN DOKUMEN & RECEIPT (Letakkan di BAWAH)
// ==========================================
Route::get('/document/{document_number}', [DocumentDisplayController::class, 'show'])->where('document_number', '.*');

Route::get('/receipt/{id}', [DocumentDisplayController::class, 'receipt']);

// ==========================================
// AUTHOR, INSTITUSI & JURNAL
// ==========================================
Route::get('/api/institutions', [AuthorInstitutionController::class, 'searchInstitutions']);
Route::get('/author/{id}', [AuthorInstitutionController::class, 'showAuthor']);
Route::get('/institution/{id}', [AuthorInstitutionController::class, 'showInstitution']);
Route::get('/journal/{name}', [JournalController::class, 'showJournal']);
Route::get('/publisher/{name}', [JournalController::class, 'showPublisher']);

// ==========================================
// SUBMIT PDF (MANUAL/STANDARD)
// ==========================================
Route::get('/submit', function () {
    return view('submit');
});
Route::post('/submit-index', [PdfSubmissionController::class, 'store'])->middleware('throttle:3,1');

// ==========================================
// VERIFIKASI EMAIL
// ==========================================
Route::get('/verify/{token}', [VerificationController::class, 'verifyEmail']);
Route::post('/resend-email', [VerificationController::class, 'resendEmail'])->middleware('throttle:3,1');


// ==========================================
// RUTE SUBMIT OJS XML (Sistem Baru Terpisah)
// ==========================================
// (Route ini sudah dibuka komentarnya dan diarahkan ke controller khusus XML)
// Route::get('/submit-xml', [XmlSubmissionController::class, 'createXml']); 
// Route::post('/submit-xml/scan', [XmlSubmissionController::class, 'scanXml']); 
// Route::post('/submit-xml/save', [XmlSubmissionController::class, 'storeXmlFinal']);

// ==========================================
// RUTE EXPERIMENTAL: HYBRID AI PDF SCANNER
// ==========================================
Route::get('/submit-beta', [BetaSubmitController::class, 'create']);
Route::post('/submit-beta/scan', [BetaSubmitController::class, 'scanPdfHybrid']);
Route::post('/submit-beta/save', [BetaSubmitController::class, 'storeFinal']);

// 🔥 TAMBAHAN BARU: Jaring pengaman kalau Laravel melakukan redirect back()
Route::get('/submit-beta/scan', function () {
    return redirect('/submit-beta')->with('error', 'Session expired or validation failed. Please re-upload your PDF.');
});