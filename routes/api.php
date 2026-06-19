<?php

use App\Http\Controllers\Api\ContactController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('contacts', ContactController::class);

Route::get('/users', function () {
    return App\Http\Resources\UserResource::collection(
        App\Models\User::with('images')->get()
    );
});
