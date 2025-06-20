<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\CarController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/cars');

Route::get('/cars', [CarController::class, 'index']);
Route::get('/calendar', [CarController::class, 'calendar'])->name("calendar");