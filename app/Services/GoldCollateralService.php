<?php

namespace App\Services;

use InvalidArgumentException;

class GoldCollateralService
{
    public const COVERAGE_MONTHS = 2;

    public function calculate(
        int $salePrice,
        int $downPayment,
        float $monthlyProfitRate,
        int $goldRatePerGram
    ): array {
        if ($salePrice < 0 || $downPayment < 0 || $downPayment > $salePrice) {
            throw new InvalidArgumentException('Invalid sale principal.');
        }

        if ($monthlyProfitRate < 0 || $monthlyProfitRate > 100) {
            throw new InvalidArgumentException('Invalid monthly profit rate.');
        }

        if ($goldRatePerGram <= 0) {
            throw new InvalidArgumentException('Gold rate must be positive.');
        }

        $principal = $salePrice - $downPayment;

        $coverageProfit = (int) round(
            $principal
            * ($monthlyProfitRate / 100)
            * self::COVERAGE_MONTHS
        );

        $coverageAmount = $principal + $coverageProfit;

        $requiredWeight = $coverageAmount > 0
            ? $coverageAmount / $goldRatePerGram
            : 0;

        return [
            'base_principal' => $principal,
            'coverage_months' => self::COVERAGE_MONTHS,
            'monthly_profit_rate' => $monthlyProfitRate,
            'coverage_profit' => $coverageProfit,
            'coverage_amount' => $coverageAmount,
            'gold_rate_per_gram' => $goldRatePerGram,
            'required_weight' => round($requiredWeight, 4),
        ];
    }
}
