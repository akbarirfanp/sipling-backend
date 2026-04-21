<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => '/users'], function() {
    Route::get('/{id?}', [UserController::class, 'getUserDetail']);
    Route::get('/', [UserController::class, 'getAllUser']);
    Route::post('/', [UserController::class, 'createUser']);
    Route::put('/{id}', [UserController::class, 'updateUser']);
});
