<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FeeController;

Route::group(['prefix' => '/users'], function() {
    Route::post('/', [UserController::class, 'createUser']);
    Route::get('/', [UserController::class, 'getAllUser']);
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
