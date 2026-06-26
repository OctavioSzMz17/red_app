<?php

use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\ImageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('contacts', ContactController::class);

Route::apiResource('contacts.images', ImageController::class)
    ->shallow();
