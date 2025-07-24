<?php

use App\Http\Controllers\Api\UploadController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/upload', [UploadController::class, 'store']);

Route::get('/test', function () {
    return response()->json(['message' => 'A rota funciona!']);
});

Route::post('/test-post', function () {
    return response()->json(['message' => 'A requisição POST para a API funcionou!']);
});
