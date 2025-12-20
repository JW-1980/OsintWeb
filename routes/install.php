<?php

declare(strict_types=1);

use App\Http\Controllers\Install\AdminController;
use App\Http\Controllers\Install\DatabaseController;
use App\Http\Controllers\Install\EmailController;
use App\Http\Controllers\Install\FinishController;
use App\Http\Controllers\Install\MigrationController;
use App\Http\Controllers\Install\SearchController;
use App\Http\Controllers\Install\SettingsController;
use App\Http\Controllers\Install\WelcomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Installation Routes
|--------------------------------------------------------------------------
|
| These routes are used for the one-time installation wizard.
| They are protected by the RedirectIfInstalled middleware to prevent
| access after installation is complete.
|
*/

Route::middleware(['web', 'install.check'])->prefix('install')->name('install.')->group(function (): void {
    // Step 1: Welcome & Requirements
    Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

    // Step 2: Database Configuration
    Route::get('/database', [DatabaseController::class, 'index'])->name('database');
    Route::post('/database/test', [DatabaseController::class, 'test'])->name('database.test');
    Route::post('/database', [DatabaseController::class, 'save'])->name('database.save');

    // Step 3: Run Migrations
    Route::get('/migrations', [MigrationController::class, 'index'])->name('migrations');
    Route::post('/migrations/run', [MigrationController::class, 'run'])->name('migrations.run');

    // Step 4: Application Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings', [SettingsController::class, 'save'])->name('settings.save');

    // Step 5: Admin Account
    Route::get('/admin', [AdminController::class, 'index'])->name('admin');
    Route::post('/admin', [AdminController::class, 'create'])->name('admin.create');

    // Step 6: Email Configuration (Optional)
    Route::get('/email', [EmailController::class, 'index'])->name('email');
    Route::post('/email/test', [EmailController::class, 'test'])->name('email.test');
    Route::post('/email', [EmailController::class, 'save'])->name('email.save');
    Route::post('/email/skip', [EmailController::class, 'skip'])->name('email.skip');

    // Step 7: Search Configuration (Optional)
    Route::get('/search', [SearchController::class, 'index'])->name('search');
    Route::post('/search/test', [SearchController::class, 'test'])->name('search.test');
    Route::post('/search', [SearchController::class, 'save'])->name('search.save');

    // Step 8: Finish Installation
    Route::get('/finish', [FinishController::class, 'index'])->name('finish');
    Route::post('/finish', [FinishController::class, 'finish'])->name('finish.complete');
});
