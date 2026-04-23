<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::group(['prefix' => '/users'], function() {
    Route::post('/', [UserController::class, 'createUser']);
    Route::get('/', [UserController::class, 'getAllUser']);
    Route::get('/{id?}', [UserController::class, 'getUserDetail']);
    Route::put('/{id}', [UserController::class, 'updateUser']);
    Route::delete('/{id}', [UserController::class, 'deleteUser']);
});
