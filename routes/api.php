<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\SlotController;
use App\Http\Controllers\Api\VehicleController;
use Illuminate\Support\Facades\Route;

Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/signin', [AuthController::class, 'signin']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/vehicle', [VehicleController::class, 'store']);
    Route::get('/vehicles', [VehicleController::class, 'index']);
    Route::delete('/vehicle/{id}', [VehicleController::class, 'destroy']);

    Route::get('/parking-slots', [SlotController::class, 'index']);

    Route::post('/booking/{booking_id}/close', [BookingController::class, 'close']);
    Route::post('/booking/{slot_id}', [BookingController::class, 'store']);
    Route::get('/bookings', [BookingController::class, 'index']);
});