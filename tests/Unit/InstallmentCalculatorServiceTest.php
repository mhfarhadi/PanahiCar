<?php

use App\Services\InstallmentCalculatorService;
use Carbon\Carbon;
use Morilog\Jalali\Jalalian;

test('it preserves the current mayahamrah installment calculation', function () {
    $saleDate = '2026-08-13';

    $firstDueDate = Jalalian::fromCarbon(
        Carbon::parse($saleDate)
    )
        ->addMonths(1)
        ->toCarbon()
        ->toDateString();

    $result = app(InstallmentCalculatorService::class)->calculate(
        salePrice: 300_000_000,
        downPayment: 100_000_000,
        monthlyProfitRate: 6.5,
        installmentCount: 3,
        saleDate: $saleDate,
        firstDueDate: $firstDueDate,
    );

    expect($result['principal'])->toBe(200_000_000)
        ->and($result['base_installment_profit'])->toBe(39_000_000)
        ->and($result['deferment_profit'])->toBe(0)
        ->and($result['installment_amount'])->toBe(79_670_000)
        ->and($result['installment_total'])->toBe(239_010_000)
        ->and($result['installment_profit'])->toBe(39_010_000)
        ->and($result['contract_total'])->toBe(339_010_000)
        ->and($result['installments'])->toHaveCount(3)
        ->and(array_column($result['installments'], 'amount'))->toBe([
            79_670_000,
            79_670_000,
            79_670_000,
        ]);
});

test('monthly cap finds the smallest valid installment count using the same calculation', function () {
    $saleDate = '2026-08-13';

    $firstDueDate = Jalalian::fromCarbon(
        Carbon::parse($saleDate)
    )
        ->addMonths(1)
        ->toCarbon()
        ->toDateString();

    $service = app(InstallmentCalculatorService::class);

    $result = $service->findWithinMonthlyCap(
        salePrice: 300_000_000,
        downPayment: 100_000_000,
        monthlyProfitRate: 6.5,
        monthlyCap: 80_000_000,
        saleDate: $saleDate,
        firstDueDate: $firstDueDate,
    );

    expect($result)->not->toBeNull()
        ->and($result['installment_count'])->toBe(3)
        ->and($result['installment_amount'])->toBe(79_670_000);

    $impossible = $service->findWithinMonthlyCap(
        salePrice: 300_000_000,
        downPayment: 100_000_000,
        monthlyProfitRate: 6.5,
        monthlyCap: 12_000_000,
        saleDate: $saleDate,
        firstDueDate: $firstDueDate,
    );

    expect($impossible)->toBeNull();
});

test('custom payments keep the customer stated amounts and carry accrued profit into the remaining balance', function () {
    $date = fn (string $jalali) => Jalalian::fromFormat(
        'Y/m/d',
        $jalali
    )
        ->toCarbon()
        ->toDateString();

    $result = app(InstallmentCalculatorService::class)->calculateCustom(
        salePrice: 300_000_000,
        downPayment: 0,
        monthlyProfitRate: 6.5,
        saleDate: $date('1405/05/23'),
        payments: [
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
    );

    expect($result['principal'])->toBe(300_000_000)
        ->and($result['payments'])->toHaveCount(3)

        // 1405/05/23 -> 1405/06/23 = exactly one Jalali month.
        ->and($result['payments'][0]['amount'])->toBe(50_000_000)
        ->and($result['payments'][0]['profit'])->toBe(19_500_000)
        ->and($result['payments'][0]['balance_after'])->toBe(269_500_000)

        // Profit is now calculated on the carried balance, not the original 300m.
        ->and($result['payments'][1]['amount'])->toBe(70_000_000)
        ->and($result['payments'][1]['profit'])->toBe(27_444_083)
        ->and($result['payments'][1]['balance_after'])->toBe(226_944_083)

        ->and($result['payments'][2]['amount'])->toBe(100_000_000)
        ->and($result['payments'][2]['profit'])->toBe(27_044_170)
        ->and($result['payments'][2]['balance_after'])->toBe(153_988_253)

        ->and($result['total_paid'])->toBe(220_000_000)
        ->and($result['total_profit'])->toBe(73_988_253)
        ->and($result['remaining_balance'])->toBe(153_988_253);
});
