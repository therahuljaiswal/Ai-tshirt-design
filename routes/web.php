<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Generator
    Route::get('/generator', [ImageController::class, 'index'])->name('generator');
    Route::post('/api/generate', [ImageController::class, 'generate'])->name('api.generate');

    // Payment
    Route::get('/buy-credits', [PaymentController::class, 'index'])->name('pricing');
    Route::post('/create-order', [PaymentController::class, 'createOrder'])->name('payment.create-order');
    Route::post('/payment/verify', [PaymentController::class, 'verify'])->name('payment.verify');

    // Admin
    Route::middleware('admin')->group(function () {
        Route::get('/admin/users', [AdminController::class, 'index'])->name('admin.users');
        Route::post('/admin/add-credits', [AdminController::class, 'addCredits'])->name('admin.add-credits');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
