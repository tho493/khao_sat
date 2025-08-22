<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\SystemConfigController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\MauKhaoSatController;
use App\Http\Controllers\Admin\DotKhaoSatController;
use App\Http\Controllers\Admin\BaoCaoController;
use App\Http\Controllers\Admin\CauHoiController;


Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard.index');

    // User Management
    Route::get('users', [UserManagementController::class, 'index'])->name('users.index');
    Route::get('users/create', [UserManagementController::class, 'create'])->name('users.create');
    Route::post('users', [UserManagementController::class, 'store'])->name('users.store');
    Route::get('users/{tendangnhap}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
    Route::put('users/{tendangnhap}', [UserManagementController::class, 'update'])->name('users.update')->middleware('prevent.double.submit:update_users');
    Route::delete('users/{tendangnhap}', [UserManagementController::class, 'destroy'])->name('users.destroy');

    // Mẫu khảo sát
    Route::prefix('mau-khao-sat')->name('mau-khao-sat.')->group(function () {
        Route::get('/', [MauKhaoSatController::class, 'index'])->name('index');
        Route::get('/create', [MauKhaoSatController::class, 'create'])->name('create');
        Route::post('/store', [MauKhaoSatController::class, 'store'])->name('store');
        Route::get('/{mauKhaoSat}/edit', [MauKhaoSatController::class, 'edit'])->name('edit');
        Route::post('/{mauKhaoSat}/copy', [MauKhaoSatController::class, 'copy'])->name('copy')->middleware('prevent.double.submit:copy_template');
        Route::put('/{mauKhaoSat}', [MauKhaoSatController::class, 'update'])->name('update')->middleware('prevent.double.submit:update_thongtin_maukahosat');
        Route::delete('/{mauKhaoSat}', [MauKhaoSatController::class, 'destroy'])->name('destroy')->middleware('prevent.double.submit:delete_form');
    });

    // Câu hỏi
    Route::post('mau-khao-sat/{mauKhaoSat}/cau-hoi', [CauHoiController::class, 'store'])->name('cau-hoi.store');
    Route::get('cau-hoi/{cauHoi}', [CauHoiController::class, 'show'])->name('cau-hoi.show');
    Route::put('cau-hoi/{cauHoi}', [CauHoiController::class, 'update'])->name('cau-hoi.update');
    Route::delete('cau-hoi/{cauHoi}', [CauHoiController::class, 'destroy'])->name('cau-hoi.destroy');
    Route::post('cau-hoi/update-order', [CauHoiController::class, 'updateOrder'])->name('cau-hoi.update-order');


    // Đợt khảo sát
    Route::prefix('dot-khao-sat')->name('dot-khao-sat.')->group(function () {
        Route::resource('/', DotKhaoSatController::class)->parameters(['' => 'dotKhaoSat']);
        Route::post('/store', [DotKhaoSatController::class, 'store'])->name('store');
        Route::get('/{dotKhaoSat}/edit', [DotKhaoSatController::class, 'edit'])->name('edit');
        Route::get('/{dotKhaoSat}', [DotKhaoSatController::class, 'show'])->name('show');
        Route::post('/{dotKhaoSat}/activate', [DotKhaoSatController::class, 'activate'])->name('activate');
        Route::post('/{dotKhaoSat}/close', [DotKhaoSatController::class, 'close'])->name('close');
    });

    // Báo cáo
    Route::prefix('bao-cao')->name('bao-cao.')->group(function () {
        Route::get('/', [BaoCaoController::class, 'index'])->name('index');
        Route::get('/dot-khao-sat/{dotKhaoSat}', [BaoCaoController::class, 'dotKhaoSat'])->name('dot-khao-sat');
        Route::get('/export/{dotKhaoSat}', [BaoCaoController::class, 'export'])->name('export');
    });

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/survey/{dotKhaoSat}', [ReportController::class, 'survey'])->name('survey');
        Route::get('/export', [ReportController::class, 'export'])->name('export');
        Route::get('/analytics', [ReportController::class, 'analytics'])->name('analytics');
    });

    // System Configuration
    Route::prefix('config')->name('config.')->group(function () {
        Route::get('/', [SystemConfigController::class, 'index'])->name('index');

        // Cập nhật template email
        Route::post('/email-template/store', [SystemConfigController::class, 'storeEmailTemplate'])->name('email-template.store');
        Route::put('/email-template/{template}', [SystemConfigController::class, 'updateEmailTemplate'])->name('email-template.update');
        Route::delete('/email-template/{template}', [SystemConfigController::class, 'destroyEmailTemplate'])->name('email-template.destroy');
        Route::post('/test-email', [SystemConfigController::class, 'testEmail'])->name('test-email');
        Route::post('/backup', [SystemConfigController::class, 'backup'])->name('backup');
    });

    // System Logs
    Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');
    Route::prefix('logs')->name('logs.')->group(function () {
        Route::get('/', [LogController::class, 'index'])->name('index');
        Route::get('/user', [LogController::class, 'userLogs'])->name('user');
        Route::get('/system', [LogController::class, 'systemLogs'])->name('system');
        Route::get('/download', [LogController::class, 'download'])->name('download');
        Route::get('/{id}', [LogController::class, 'show'])->name('show');
        Route::delete('/clear', [LogController::class, 'clear'])->name('clear');
    });
});