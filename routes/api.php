<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\UploadHistoryController;
use App\Http\Controllers\Api\InstrumentDataController;


Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/upload', [UploadController::class, 'store']);
    Route::get('/upload-history', [UploadHistoryController::class, 'index']);
    Route::get('/data', [InstrumentDataController::class, 'index']);
});
