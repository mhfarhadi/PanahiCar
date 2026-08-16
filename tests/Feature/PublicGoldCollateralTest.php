<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

test('public gold collateral calculator reuses gold coverage and installment rules', function () {
    Cache::forget('navasan:gold_18ayar');
    Cache::forget('navasan:last_gold_18ayar');

    config()->set('services.navasan.key', 'test-key');

    Http::fake([
        'api.navasan.tech/latest/*' => Http::response([
            '18ayar' => [
                'value' => '19052130',
            ],
        ]),
    ]);

    $saleDate = now()->toDateString();

    $standardFirstDueDate = app(
        \App\Services\InstallmentCalculatorService::class
    )->calculateDeferment(
        $saleDate,
        \Morilog\Jalali\Jalalian::fromCarbon(now())
            ->addMonths(1)
            ->toCarbon()
            ->toDateString()
    )['standard_first_due_date'];

    $this
        ->postJson(route('features.gold-collateral.calculate'), [
            'sale_price' => 140_000_000,
            'down_payment' => 40_000_000,
            'monthly_profit_rate' => 6.5,
            'installment_count' => 3,
            'sale_date' => $saleDate,
            'first_due_date' => $standardFirstDueDate,
        ])
        ->assertOk()
        ->assertJsonPath('result.collateral.base_principal', 100_000_000)
        ->assertJsonPath('result.collateral.coverage_profit', 13_000_000)
        ->assertJsonPath('result.collateral.coverage_amount', 113_000_000)
        ->assertJsonPath('result.collateral.required_weight', 5.9311)
        ->assertJsonPath('result.gold_rate.rate_per_gram', 19_052_130)
        ->assertJsonCount(3, 'result.installments.installments');
});

test('public gold collateral calculator rejects first due date before one jalali month', function () {
    $saleDate = now()->toDateString();

    $this
        ->postJson(route('features.gold-collateral.calculate'), [
            'sale_price' => 140_000_000,
            'down_payment' => 40_000_000,
            'monthly_profit_rate' => 6.5,
            'installment_count' => 3,
            'sale_date' => $saleDate,
            'first_due_date' => $saleDate,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('first_due_date');
});
