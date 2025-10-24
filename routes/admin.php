<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\MauKhaoSatController;
use App\Http\Controllers\Admin\DotKhaoSatController;
use App\Http\Controllers\Admin\BaoCaoController;
use App\Http\Controllers\Admin\CauHoiController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\NamHocController;
use App\Http\Controllers\Admin\CtdtController;
use App\Http\Controllers\Admin\PhieuKhaoSatController;
use App\Http\Controllers\Admin\DBBackupController;


Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // User Management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        Route::get('/create', [UserManagementController::class, 'create'])->name('create');
        Route::post('/', [UserManagementController::class, 'store'])->name('store');
        Route::get('/{tendangnhap}/edit', [UserManagementController::class, 'edit'])->name('edit');
        Route::put('/{tendangnhap}', [UserManagementController::class, 'update'])->name('update');
        Route::delete('/{tendangnhap}', [UserManagementController::class, 'destroy'])->name('destroy');
    });

    // Mẫu khảo sát
    Route::prefix('mau-khao-sat')->name('mau-khao-sat.')->group(function () {
        Route::get('/', [MauKhaoSatController::class, 'index'])->name('index');
        Route::get('/create', [MauKhaoSatController::class, 'create'])->name('create');
        Route::get('/{mauKhaoSat}/questions', [MauKhaoSatController::class, 'getQuestionsJson'])->name('questions');
        Route::post('/store', [MauKhaoSatController::class, 'store'])->name('store');
        Route::get('/{mauKhaoSat}/edit', [MauKhaoSatController::class, 'edit'])->name('edit');
        Route::post('/{mauKhaoSat}/copy', [MauKhaoSatController::class, 'copy'])->name('copy');
        Route::put('/{mauKhaoSat}', [MauKhaoSatController::class, 'update'])->name('update');
        Route::delete('/{mauKhaoSat}', [MauKhaoSatController::class, 'destroy'])->name('destroy');
        Route::post('/{mauKhaoSat}/cau-hoi', [CauHoiController::class, 'store'])->name('cau-hoi.store');
    });

    // Câu hỏi
    Route::prefix('cau-hoi')->name('cau-hoi.')->group(function () {
        Route::get('/{cauHoi}', [CauHoiController::class, 'show'])->name('show');
        Route::put('/{cauHoi}', [CauHoiController::class, 'update'])->name('update');
        Route::delete('/{cauHoi}', [CauHoiController::class, 'destroy'])->name('destroy');
        Route::post('/update-order', [CauHoiController::class, 'updateOrder'])->name('update-order');
    });


    // Đợt khảo sát
    Route::prefix('dot-khao-sat')->name('dot-khao-sat.')->group(function () {
        Route::get('/', [DotKhaoSatController::class, 'index'])->name('index');
        Route::get('/create', [DotKhaoSatController::class, 'create'])->name('create');
        Route::post('/store', [DotKhaoSatController::class, 'store'])->name('store');
        Route::get('/{dotKhaoSat}', [DotKhaoSatController::class, 'show'])->name('show');
        Route::get('/{dotKhaoSat}/edit', [DotKhaoSatController::class, 'edit'])->name('edit');
        Route::put('/{dotKhaoSat}', [DotKhaoSatController::class, 'update'])->name('update');
        Route::post('/{dotKhaoSat}/activate', [DotKhaoSatController::class, 'activate'])->name('activate');
        Route::post('/{dotKhaoSat}/close', [DotKhaoSatController::class, 'close'])->name('close');
        // Route::resource('/', DotKhaoSatController::class)->parameters(['' => 'dotKhaoSat']);
    });

    // Báo cáo
    Route::prefix('bao-cao')->name('bao-cao.')->group(function () {
        Route::get('/', [BaoCaoController::class, 'index'])->name('index');
        Route::get('/dot-khao-sat/{dotKhaoSat}', [BaoCaoController::class, 'dotKhaoSat'])->name('dot-khao-sat');
        Route::get('/export/{dotKhaoSat}', [BaoCaoController::class, 'export'])->name('export');
        Route::post('{dotKhaoSat}/summarize', [BaoCaoController::class, 'summarizeWithAi'])->name('summarize');
        Route::delete('/response/{phieuKhaoSatChiTiet}', [BaoCaoController::class, 'deleteResponse'])->name('delete-response');
        Route::delete('/survey/{phieuKhaoSat}', [BaoCaoController::class, 'deleteSurvey'])->name('delete-survey');
    });

    // Phiếu khảo sát
    Route::get('phieu-khao-sat/{phieuKhaoSat}', [PhieuKhaoSatController::class, 'showJson'])->name('phieu-khao-sat.show');

    // Năm học
    Route::prefix('nam-hoc')->name('nam-hoc.')->group(function () {
        Route::get('/', [NamHocController::class, 'index'])->name('index');
        Route::post('/', [NamHocController::class, 'store'])->name('store');
        Route::put('/{nam_hoc}', [NamHocController::class, 'update'])->name('update');
        Route::delete('/{nam_hoc}', [NamHocController::class, 'destroy'])->name('destroy');
    });

    // Chương trình đào tạo
    Route::prefix('ctdt')->name('ctdt.')->group(function () {
        Route::get('/', [CtdtController::class, 'index'])->name('index');
        Route::post('/', [CtdtController::class, 'store'])->name('store');
        Route::put('/{mactdt}', [CtdtController::class, 'update'])->name('update');
        Route::delete('/{mactdt}', [CtdtController::class, 'destroy'])->name('destroy');
    });

    // FAQ
    Route::resource('faq', FaqController::class)->except(['show']);

    // System Logs
    // Route::get('log', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');
    Route::prefix('logs')->name('logs.')->group(function () {
        Route::get('/', [LogController::class, 'index'])->name('index');
        Route::get('/user', [LogController::class, 'userLogs'])->name('user');
        Route::get('/system', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index')->name('system');
        Route::get('/download', [LogController::class, 'download'])->name('download');
        Route::get('/{id}', [LogController::class, 'show'])->name('show');
        Route::delete('/clear', [LogController::class, 'clear'])->name('clear');
    });

    // Database Backup
    Route::prefix('db-backups')->name('dbbackups.')->group(function () {
        Route::get('/', [DBBackupController::class, 'index'])->name('index');
        Route::post('/create', [DBBackupController::class, 'create'])->name('create');
        Route::post('/restore', [DBBackupController::class, 'restore'])->name('restore');
        Route::get('/download/{file}', [DBBackupController::class, 'download'])->name('download');
        Route::delete('/delete/{file}', [DBBackupController::class, 'destroy'])->name('destroy');
    });
});