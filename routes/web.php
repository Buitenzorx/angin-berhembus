<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SensorDataController;

Route::get('/', [SensorDataController::class, 'index']);
Route::post('/sensor-data', [SensorDataController::class, 'store']);

// Route::get('/sensor-data/latest', function () {
//     $data = \App\Models\SensorData::latest()->first();
//     return response()->json($data);
// });


// Route::get('/', function () {
//     return view('welcome');
// });
