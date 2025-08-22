<?php

use Illuminate\Mail\SentMessage;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\KhaoSatController;

// Nạp các route dành cho admin:
require __DIR__ . '/admin.php';

// Public routes
Route::get('/', function () {
    return redirect()->route('khao-sat.index');
});
Route::get('/thank-you', [KhaoSatController::class, 'thanks'])->name('thanks');

// Authentication
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login'])->middleware('prevent.double.submit');
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Khảo sát công khai
Route::prefix('khao-sat')->name('khao-sat.')->group(function () {
    Route::get('/', [KhaoSatController::class, 'index'])->name('index');
    Route::get('/{dotKhaoSat}', [KhaoSatController::class, 'show'])->name('show');
    Route::post('/{dotKhaoSat}', [KhaoSatController::class, 'store'])->name('store')->middleware('prevent.double.submit');
});
