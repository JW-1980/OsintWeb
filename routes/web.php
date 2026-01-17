<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group.
|
*/

// Include installation routes
require __DIR__.'/install.php';

// Offline page for service worker
Route::get('/offline', function () {
    return view('offline');
})->name('offline');

// Email Unsubscribe Routes (must be before the catch-all)
Route::match(['get', 'post'], '/email/unsubscribe', [\App\Http\Controllers\EmailUnsubscribeController::class, 'unsubscribe'])
    ->name('email.unsubscribe');

Route::post('/email/unsubscribe/one-click', [\App\Http\Controllers\EmailUnsubscribeController::class, 'oneClickUnsubscribe'])
    ->name('email.unsubscribe.one-click');

Route::get('/{any?}', function () {
    // If installed, show the Vue SPA
    if (file_exists(storage_path('installed'))) {
        return view('app');
    }
    // Otherwise show the installation welcome page
    return view('welcome');
})->where('any', '^(?!api|install|sanctum).*$');
