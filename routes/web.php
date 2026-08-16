<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\DeviceShowController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContactShowController;
use App\Http\Controllers\AnnouncedDeviceController;
use App\Http\Controllers\AnnouncedDeviceCreateController;
use App\Http\Controllers\AnnouncedDevicePurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\PriceEstimateController;
use App\Http\Controllers\InstallmentController;
use App\Http\Controllers\PublicInstallmentCalculatorController;
use App\Http\Controllers\PublicPriceEstimateController;
use App\Http\Controllers\PublicGoldCollateralController;
use App\Http\Controllers\PublicWantedDeviceController;
use App\Http\Controllers\EntityNoteController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/features', fn () => Inertia::render('Features/Index'))
    ->name('features.index');

Route::get('/features/contracts', function () {
    return Inertia::render('Features/Contracts/Index', [
        'catalog' => [
            'brands' => DB::table('brands')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'models' => DB::table('device_models')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'storages' => DB::table('storage_options')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(),
            'colors' => DB::table('color_options')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(),
            'modelStorages' => DB::table('device_model_storage_option')->get(),
            'modelColors' => DB::table('device_model_color_option')->get(),
        ],
    ]);
})->name('features.contracts.index');

Route::get(
    '/features/installments',
    [PublicInstallmentCalculatorController::class, 'index']
)->name('features.installments.index');

Route::post(
    '/features/installments/calculate',
    [PublicInstallmentCalculatorController::class, 'calculate']
)->name('features.installments.calculate');

Route::get(
    '/features/price-estimate',
    [PublicPriceEstimateController::class, 'index']
)->name('features.price-estimates.index');

Route::get(
    '/features/gold-collateral',
    [PublicGoldCollateralController::class, 'index']
)->name('features.gold-collateral.index');

Route::post(
    '/features/gold-collateral/calculate',
    [PublicGoldCollateralController::class, 'calculate']
)->name('features.gold-collateral.calculate');

Route::get(
    '/features/wanted',
    [PublicWantedDeviceController::class, 'index']
)->name('features.wanted.index');

Route::post(
    '/features/wanted',
    [PublicWantedDeviceController::class, 'store']
)->name('features.wanted.store');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/devices', [DeviceController::class, 'index'])->name('devices.index');
    Route::get('/devices/{device}', [DeviceShowController::class, 'show'])
        ->whereNumber('device')
        ->name('devices.show');

    Route::get('/devices/{device}/edit', [DeviceController::class, 'edit'])
        ->whereNumber('device')
        ->name('devices.edit');

    Route::patch('/devices/{device}', [DeviceController::class, 'update'])
        ->whereNumber('device')
        ->name('devices.update');

    Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
    Route::get('/contacts/create', [ContactController::class, 'create'])->name('contacts.create');
    Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');
    Route::get('/contacts/{contact}/edit', [ContactController::class, 'edit'])
        ->whereNumber('contact')
        ->name('contacts.edit');

    Route::patch('/contacts/{contact}', [ContactController::class, 'update'])
        ->whereNumber('contact')
        ->name('contacts.update');

    Route::post('/contacts/{contact}/archive', [ContactController::class, 'archive'])
        ->whereNumber('contact')
        ->name('contacts.archive');

    Route::post('/contacts/{contact}/restore', [ContactController::class, 'restore'])
        ->whereNumber('contact')
        ->name('contacts.restore');

    Route::delete('/contacts/{contact}', [ContactController::class, 'destroy'])
        ->whereNumber('contact')
        ->name('contacts.destroy');

    Route::get('/contacts/{contact}', [ContactShowController::class, 'show'])
        ->whereNumber('contact')
        ->name('contacts.show');
    Route::get('/announced-devices', [AnnouncedDeviceController::class, 'index'])->name('announced-devices.index');
    Route::get('/announced-devices/create', [AnnouncedDeviceCreateController::class, 'create'])->name('announced-devices.create');
    Route::post('/announced-devices', [AnnouncedDeviceCreateController::class, 'store'])->name('announced-devices.store');
    Route::get('/announced-devices/{device}/purchase', [AnnouncedDevicePurchaseController::class, 'create'])->name('announced-devices.purchase.create');
    Route::post('/announced-devices/{device}/purchase', [AnnouncedDevicePurchaseController::class, 'store'])->name('announced-devices.purchase.store');
    Route::get('/devices/create', [DeviceController::class, 'create'])->name('devices.create');
    Route::post('/devices', [DeviceController::class, 'store'])->name('devices.store');

    Route::get('/price-estimates', [PriceEstimateController::class, 'index'])
        ->name('price-estimates.index');

    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');

Route::get('/sales/currency-rate', [SaleController::class, 'currencyRate'])
    ->name('sales.currency-rate');

Route::get('/sales/gold-rate', [SaleController::class, 'goldRate'])
    ->name('sales.gold-rate');

    Route::get('/sales/{sale}', [SaleController::class, 'show'])
        ->whereNumber('sale')
        ->name('sales.show');

    Route::get('/devices/{device}/sell', [SaleController::class, 'create'])
        ->whereNumber('device')
        ->name('sales.create');

    Route::post('/devices/{device}/sell', [SaleController::class, 'store'])
        ->whereNumber('device')
        ->name('sales.store');

    Route::get('/installments', [InstallmentController::class, 'index'])
        ->name('installments.index');

    Route::post('/installments/{installment}/check-details', [InstallmentController::class, 'updateCheckDetails'])
        ->whereNumber('installment')
        ->name('installments.check-details');

    Route::post('/installments/{installment}/images/{image}/remove', [InstallmentController::class, 'removeImage'])
        ->whereNumber(['installment', 'image'])
        ->name('installments.images.remove');

    Route::post('/installments/{installment}/images/{image}/replace', [InstallmentController::class, 'replaceImage'])
        ->whereNumber(['installment', 'image'])
        ->name('installments.images.replace');

    Route::post('/installments/{installment}/mark-paid', [InstallmentController::class, 'markPaid'])
        ->whereNumber('installment')
        ->name('installments.mark-paid');

    Route::post('/installments/{installment}/reverse-paid', [InstallmentController::class, 'reversePaid'])
        ->whereNumber('installment')
        ->name('installments.reverse-paid');

    Route::post('/entity-notes', [EntityNoteController::class, 'store'])
        ->name('entity-notes.store');

    Route::get('/settings', fn () => Inertia::render('Settings/Index'))->name('settings.index');


    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
