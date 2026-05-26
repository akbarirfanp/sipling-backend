<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;

Route::group(['prefix' => '/users'], function() {
    Route::post('/', [UserController::class, 'createUser']);
    Route::get('/', [UserController::class, 'getAllUser']);
    Route::get('/count-user', [UserController::class, 'countUser']);
    Route::put('/change-password/{id}', [UserController::class, 'changePassword']); // ✅ static dulu
    Route::get('/{id?}', [UserController::class, 'getUserDetail']);
    Route::put('/{id}', [UserController::class, 'updateUser']);
    Route::delete('/{id}', [UserController::class, 'deleteUser']);
});

Route::group(['prefix' => '/fees'], function() {
    Route::post('/', [FeeController::class, 'createFee']);
    Route::get('/', [FeeController::class, 'getAllFee']);
    Route::get('/{id?}', [FeeController::class, 'getFeeDetail']);
    Route::put('/{id}', [FeeController::class, 'updateFee']);
    Route::delete('/{id}', [FeeController::class, 'deleteFee']);
});

Route::group(['prefix' => '/roles'], function() {
    Route::get('/', [RoleController::class, 'getAllRole']);
});

Route::group(['prefix' => '/dashboard'], function() {
    Route::get('/', [DashboardController::class, 'getDashboardStatistics']);
    Route::get('/recent-activity', [DashboardController::class, 'getRecentActivity']);
    Route::get('/generate-report', [DashboardController::class, 'generateReport']);
});

Route::group(['prefix' => '/bills'], function() {
    Route::get('/', [BillController::class, 'getAllBill']);
    Route::post('/generate-bill', [BillController::class, 'generate']);        // ✅ static dulu
    Route::get('/snap-token/{id}', [BillController::class, 'getSnapToken']);   // ✅ static dulu
    Route::get('/{id}', [BillController::class, 'getBillDetail']);             // dynamic belakangan
});

Route::group(['prefix' => '/payments'], function() {
    Route::get('/history', [PaymentController::class, 'getAllPaymentHistory']);
});