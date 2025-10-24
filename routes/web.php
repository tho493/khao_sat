<?php

use App\Http\Controllers\Api\ChatbotController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\KhaoSatController;
use App\Http\Controllers\ErrorController;
use App\Http\Controllers\SafariIosController;

// Nạp các route dành cho admin:
require __DIR__ . '/admin.php';

// Authentication
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::prefix('api')->name('api.')->group(function () {
    Route::post('/chatbot/ask', [ChatbotController::class, 'ask'])->name('ask');
});

Route::get('/csrf-token', [SafariIosController::class, 'getCsrfToken'])->name('csrf-token');
Route::get('/safari-ios/session-check', [SafariIosController::class, 'checkSession'])->name('safari-ios.session-check');

// Public routes
Route::prefix('')->name('khao-sat.')->group(function () {
    Route::get('/', [KhaoSatController::class, 'index'])->name('index');
    Route::post('/{dotKhaoSat}', [KhaoSatController::class, 'store'])->name('store');
    Route::get('/review', [KhaoSatController::class, 'review'])->name('review');
    Route::get('/thank-you', [KhaoSatController::class, 'thanks'])->name('thanks');
    Route::get('/{dotKhaoSat}', [KhaoSatController::class, 'show'])->name('show');
});