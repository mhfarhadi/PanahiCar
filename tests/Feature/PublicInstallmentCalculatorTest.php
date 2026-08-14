<?php

use Carbon\Carbon;
use Morilog\Jalali\Jalalian;

test('public installment calculator page is accessible without authentication', function () {
    $this
        ->get(route('features.installments.index'))
        ->assertOk();
});

test('public installment calculator returns the current mayahamrah regular calculation', function () {
    $saleDate = '2026-08-13';

    $firstDueDate = Jalalian::fromCarbon(
        Carbon::parse($saleDate)
    )
        ->addMonths(1)
        ->toCarbon()
        ->toDateString();

    $this
        ->postJson(route('features.installments.calculate'), [
            'mode' => 'regular',
            'sale_price' => 300_000_000,
            'down_payment' => 100_000_000,
            'monthly_profit_rate' => 6.5,
            'installment_count' => 3,
            'sale_date' => $saleDate,
            'first_due_date' => $firstDueDate,
        ])
        ->assertOk()
        ->assertJsonPath('result.principal', 200_000_000)
        ->assertJsonPath('result.installment_amount', 79_670_000)
        ->assertJsonPath('result.installment_total', 239_010_000)
        ->assertJsonPath('result.installment_profit', 39_010_000)
        ->assertJsonPath('result.contract_total', 339_010_000)
        ->assertJsonCount(3, 'result.installments');
});

test('public installment calculator can find the smallest count within a monthly payment cap', function () {
    $saleDate = '2026-08-13';

    $firstDueDate = Jalalian::fromCarbon(
        Carbon::parse($saleDate)
    )
        ->addMonths(1)
        ->toCarbon()
        ->toDateString();

    $this
        ->postJson(route('features.installments.calculate'), [
            'mode' => 'monthly_cap',
            'sale_price' => 300_000_000,
            'down_payment' => 100_000_000,
            'monthly_profit_rate' => 6.5,
            'monthly_cap' => 80_000_000,
            'sale_date' => $saleDate,
            'first_due_date' => $firstDueDate,
        ])
        ->assertOk()
        ->assertJsonPath('result.installment_count', 3)
        ->assertJsonPath('result.installment_amount', 79_670_000);
});

test('public installment calculator validates current mayahamrah financial limits', function () {
    $this
        ->postJson(route('features.installments.calculate'), [
            'mode' => 'regular',
            'sale_price' => 100_000_000,
            'down_payment' => 110_000_000,
            'monthly_profit_rate' => 101,
            'installment_count' => 61,
            'sale_date' => '2026-08-13',
            'first_due_date' => '2026-08-13',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'down_payment',
            'monthly_profit_rate',
            'installment_count',
        ]);
});

test('public installment calculator rejects a first due date before the standard jalali month', function () {
    $this
        ->postJson(route('features.installments.calculate'), [
            'mode' => 'regular',
            'sale_price' => 300_000_000,
            'down_payment' => 100_000_000,
            'monthly_profit_rate' => 6.5,
            'installment_count' => 3,
            'sale_date' => '2026-08-13',
            'first_due_date' => '2026-08-20',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('first_due_date');
});

test('public installment calculator supports carried balance custom payments', function () {
    $date = fn (string $jalali) => Jalalian::fromFormat(
        'Y/m/d',
        $jalali
    )
        ->toCarbon()
        ->toDateString();

    $this
        ->postJson(route('features.installments.calculate'), [
            'mode' => 'custom',
            'sale_price' => 300_000_000,
            'down_payment' => 0,
            'monthly_profit_rate' => 6.5,
            'sale_date' => $date('1405/05/23'),
            'first_due_date' => $date('1405/06/23'),
            'payments' => [
                [
                    'due_date' => $date('1405/06/23'),
                    'amount' => 50_000_000,
                ],
                [
                    'due_date' => $date('1405/08/10'),
                    'amount' => 70_000_000,
                ],
                [
                    'due_date' => $date('1405/10/05'),
                    'amount' => 100_000_000,
                ],
            ],
        ])
        ->assertOk()
        ->assertJsonPath('available', true)
        ->assertJsonPath('result.payments.0.amount', 50_000_000)
        ->assertJsonPath('result.payments.0.profit', 19_500_000)
        ->assertJsonPath('result.payments.0.balance_after', 269_500_000)
        ->assertJsonPath('result.payments.1.amount', 70_000_000)
        ->assertJsonPath('result.payments.1.balance_after', 226_944_083)
        ->assertJsonPath('result.payments.2.amount', 100_000_000)
        ->assertJsonPath('result.remaining_balance', 153_988_253);
});

test('custom mode uses the actual first payment date without requiring a standard first due date', function () {
    $date = fn (string $jalali) => Jalalian::fromFormat(
        'Y/m/d',
        $jalali
    )
        ->toCarbon()
        ->toDateString();

    $this
        ->postJson(route('features.installments.calculate'), [
            'mode' => 'custom',
            'sale_price' => 300_000_000,
            'down_payment' => 0,
            'monthly_profit_rate' => 6.5,
            'sale_date' => $date('1405/05/23'),
            'payments' => [
                [
                    'due_date' => $date('1405/06/05'),
                    'amount' => 50_000_000,
                ],
            ],
        ])
        ->assertOk()
        ->assertJsonPath('result.payments.0.amount', 50_000_000)
        ->assertJsonPath('result.payments.0.interval_months', 0)
        ->assertJsonPath('result.payments.0.interval_days', 13)
        ->assertJsonPath('result.payments.0.profit', 8_450_000)
        ->assertJsonPath('result.payments.0.balance_after', 258_450_000);
});
