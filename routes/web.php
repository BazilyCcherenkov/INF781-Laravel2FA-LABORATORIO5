<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\TwoFactorVerifyController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/two-factor/verify', [TwoFactorVerifyController::class, 'show'])
        ->name('two-factor.verify');
    Route::post('/two-factor/verify', [TwoFactorVerifyController::class, 'verify'])
        ->name('two-factor.verify.post');
    Route::post('/two-factor/verify/backup', [TwoFactorVerifyController::class, 'verifyWithBackupCode'])
        ->name('two-factor.verify.backup');
});

Route::middleware(['auth', 'verified', 'two-factor'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/two-factor/setup', [TwoFactorController::class, 'show'])
        ->name('two-factor.setup');
    Route::post('/two-factor/enable', [TwoFactorController::class, 'enable'])
        ->name('two-factor.enable');
    Route::post('/two-factor/disable', [TwoFactorController::class, 'disable'])
        ->name('two-factor.disable');
    Route::post('/two-factor/backup-codes/regenerate', [TwoFactorController::class, 'regenerateBackupCodes'])
        ->name('two-factor.backup-codes.regenerate');
});

require __DIR__.'/auth.php';