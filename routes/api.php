<?php

use App\Http\Controllers\Api\InstrumentDataController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\UploadHistoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/upload', [UploadController::class, 'store']);

Route::get('/upload-history', [UploadHistoryController::class, 'index']);

Route::get('/data', [InstrumentDataController::class, 'index']);
