<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\DeviceShowController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\AnnouncedDeviceController;
use App\Http\Controllers\AnnouncedDeviceCreateController;
use App\Http\Controllers\AnnouncedDevicePurchaseController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/devices', [DeviceController::class, 'index'])->name('devices.index');
    Route::get('/devices/{device}', [DeviceShowController::class, 'show'])->name('devices.show');

    Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
    Route::get('/contacts/create', [ContactController::class, 'create'])->name('contacts.create');
    Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');
    Route::get('/announced-devices', [AnnouncedDeviceController::class, 'index'])->name('announced-devices.index');
    Route::get('/announced-devices/create', [AnnouncedDeviceCreateController::class, 'create'])->name('announced-devices.create');
    Route::post('/announced-devices', [AnnouncedDeviceCreateController::class, 'store'])->name('announced-devices.store');
    Route::get('/announced-devices/{device}/purchase', [AnnouncedDevicePurchaseController::class, 'create'])->name('announced-devices.purchase.create');
    Route::post('/announced-devices/{device}/purchase', [AnnouncedDevicePurchaseController::class, 'store'])->name('announced-devices.purchase.store');
    Route::get('/devices/create', [DeviceController::class, 'create'])->name('devices.create');
    Route::post('/devices', [DeviceController::class, 'store'])->name('devices.store');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
