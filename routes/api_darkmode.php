<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DarkModeController;

Route::post('/user/theme-preference', [DarkModeController::class, 'setPreference']);
