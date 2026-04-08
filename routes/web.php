<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('home');
});

// Halaman Hasil Pencarian (yang ada filternya)
Route::get('/results', [App\Http\Controllers\DocumentController::class, 'index']);

Route::get('/search', [DocumentController::class, 'search']);
Route::get('/document/{document_number}', [DocumentController::class, 'show']);

Route::get('/verify/{token}', [App\Http\Controllers\DocumentController::class, 'verifyEmail']);
// Route untuk menampilkan halaman UI form
Route::get('/submit', function () {
    return view('submit');
});

// (Ini route yang sudah kamu buat sebelumnya)
Route::post('/submit-index', [App\Http\Controllers\DocumentController::class, 'store'])
    ->middleware('throttle:3,1');

// Halaman URL Tanda Terima (Bisa di-bookmark / masuk history)
Route::get('/receipt/{id}', [App\Http\Controllers\DocumentController::class, 'receipt']);

// API untuk proses Kirim Ulang Email (Maks 3x per menit)
Route::post('/resend-email', [App\Http\Controllers\DocumentController::class, 'resendEmail'])
    ->middleware('throttle:3,1');


