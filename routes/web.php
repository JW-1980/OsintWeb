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

Route::get('/{any?}', function () {
    // If installed, show the Vue SPA
    if (file_exists(storage_path('installed'))) {
        return view('app');
    }
    // Otherwise show the installation welcome page
    return view('welcome');
})->where('any', '^(?!api|install|sanctum).*$');
