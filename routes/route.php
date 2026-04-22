<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::group(['prefix' => '/users'], function() {
    Route::get('/', [UserController::class, 'getAllUser']);
    Route::get('/{id?}', [UserController::class, 'getUserDetail']);
    Route::post('/', [UserController::class, 'createUser']);
    Route::put('/{id}', [UserController::class, 'updateUser']);
});
