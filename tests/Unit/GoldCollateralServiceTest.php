<?php

use App\Services\GoldCollateralService;

test('gold collateral covers principal plus two months of contract profit', function () {
    $result = app(GoldCollateralService::class)->calculate(
        salePrice: 140_000_000,
        downPayment: 40_000_000,
        monthlyProfitRate: 6.5,
        goldRatePerGram: 19_052_130,
    );

    expect($result['base_principal'])->toBe(100_000_000)
        ->and($result['coverage_months'])->toBe(2)
        ->and($result['coverage_profit'])->toBe(13_000_000)
        ->and($result['coverage_amount'])->toBe(113_000_000)
        ->and($result['required_weight'])->toBe(5.9311);
});
