<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SensorDataController;

Route::post('/sensor-data', [SensorDataController::class, 'store']);
Route::get('/sensor-data/latest', [SensorDataController::class, 'latest']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
