<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Morilog\Jalali\Jalalian;

beforeEach(function () {
    Http::fake();
});

it('renders the public features hub', function () {
    $this->get(route('features.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Features/Index'));
});

it('renders public feature tools', function (string $routeName) {
    $this->get(route($routeName))->assertOk();
})->with([
    'features.installments.index',
    'features.contracts.index',
    'features.price-estimates.index',
    'features.gold-collateral.index',
    'features.wanted.index',
    'features.wanted-market.index',
    'features.check-printer.index',
]);

it('calculates a public installment plan', function () {
    $saleDate = '2026-08-13';
    $firstDueDate = Jalalian::fromCarbon(Carbon::parse($saleDate))
        ->addMonths(1)
        ->toCarbon()
        ->toDateString();

    $this->postJson(route('features.installments.calculate'), [
        'mode' => 'regular',
        'sale_price' => 800_000_000,
        'down_payment' => 200_000_000,
        'monthly_profit_rate' => 6.5,
        'installment_count' => 6,
        'sale_date' => $saleDate,
        'first_due_date' => $firstDueDate,
    ])
        ->assertOk()
        ->assertJsonPath('available', true)
        ->assertJsonPath('result.installment_count', 6);
});

it('calculates irregular installment payments', function () {
    $saleDate = '2026-08-13';
    $firstDueDate = Jalalian::fromCarbon(Carbon::parse($saleDate))
        ->addMonths(1)
        ->toCarbon()
        ->toDateString();
    $secondDueDate = Jalalian::fromCarbon(Carbon::parse($firstDueDate))
        ->addMonths(2)
        ->toCarbon()
        ->toDateString();

    $this->postJson(route('features.installments.calculate'), [
        'mode' => 'custom',
        'sale_price' => 800_000_000,
        'down_payment' => 200_000_000,
        'monthly_profit_rate' => 6.5,
        'sale_date' => $saleDate,
        'payments' => [
            ['due_date' => $firstDueDate, 'amount' => 80_000_000],
            ['due_date' => $secondDueDate, 'amount' => 120_000_000],
        ],
    ])
        ->assertOk()
        ->assertJsonPath('available', true)
        ->assertJsonPath('result.payments.0.amount', 80_000_000)
        ->assertJsonPath('result.payments.1.amount', 120_000_000);
});

it('calculates gold collateral from a manual gram price', function () {
    $saleDate = '2026-08-13';
    $firstDueDate = Jalalian::fromCarbon(Carbon::parse($saleDate))
        ->addMonths(1)
        ->toCarbon()
        ->toDateString();

    $this->postJson(route('features.gold-collateral.calculate'), [
        'sale_price' => 800_000_000,
        'down_payment' => 200_000_000,
        'monthly_profit_rate' => 6.5,
        'installment_count' => 6,
        'gold_rate_per_gram' => 8_000_000,
        'sale_date' => $saleDate,
        'first_due_date' => $firstDueDate,
    ])
        ->assertOk()
        ->assertJsonPath('result.gold_rate.source', 'manual')
        ->assertJsonPath('result.gold_rate.rate_per_gram', 8_000_000)
        ->assertJsonPath('result.collateral.gold_rate_per_gram', 8_000_000);
});

it('estimates vehicle price from showroom inventory', function () {
    $device = \App\Models\Device::create([
        'brand' => 'ایران‌خودرو',
        'model' => 'دنا پلاس',
        'model_year' => 1402,
        'mileage' => 12000,
        'color' => 'سفید',
        'transmission' => 'automatic',
        'fuel_type' => 'petrol',
        'body_condition' => 'pristine',
        'status' => 'in_stock',
    ]);

    \Illuminate\Support\Facades\DB::table('purchases')->insert([
        'device_id' => $device->id,
        'purchase_price' => 800_000_000,
        'purchase_date' => now()->toDateString(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->get(route('features.price-estimates.index', [
        'brand' => 'ایران‌خودرو',
        'model' => 'دنا پلاس',
    ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Features/PriceEstimate/Index')
            ->where('estimate.available', true)
            ->where('estimate.suggested_price', 880_000_000)
        );
});
