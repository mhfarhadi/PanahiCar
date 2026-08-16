<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PriceEstimationService
{
    public function estimate(
        string $brand,
        string $model,
        string $storage,
        int $currentUsdRate,
        ?string $conditionGrade = null,
        ?int $batteryHealth = null,
        ?string $batteryCondition = null,
        ?string $registrationStatus = null,
        ?string $color = null
    ): array {
        if ($currentUsdRate <= 0) {
            return $this->emptyResult('current_usd_unavailable');
        }

        $comparables = DB::table('sales as s')
            ->join('devices as d', 'd.id', '=', 's.device_id')
            ->whereNotNull('s.usd_rate')
            ->where('s.usd_rate', '>', 0)
            ->where('d.brand', $brand)
            ->where('d.model', $model)
            ->where('d.storage', $storage)
            ->orderByDesc('s.sale_date')
            ->orderByDesc('s.id')
            ->get([
                's.id as sale_id',
                's.sale_price',
                's.sale_date',
                's.usd_rate',
                's.usd_rate_source',
                'd.brand',
                'd.model',
                'd.storage',
                'd.condition_grade',
                'd.battery_health',
                'd.battery_condition',
                'd.registration_status',
                'd.color',
            ])
            ->map(function ($sale) use (
                $currentUsdRate,
                $conditionGrade,
                $batteryHealth,
                $batteryCondition,
                $registrationStatus,
                $color
            ) {
                $sale->normalized_price = (int) round(
                    ((int) $sale->sale_price / (int) $sale->usd_rate)
                    * $currentUsdRate
                );

                $sale->similarity_score = $this->similarityScore(
                    $sale,
                    $conditionGrade,
                    $batteryHealth,
                    $batteryCondition,
                    $registrationStatus,
                    $color
                );

                $sale->recency_score = $this->recencyScore(
                    $sale->sale_date
                );

                $sale->combined_weight = (int) round(
                    ($sale->similarity_score * $sale->recency_score) / 100
                );

                return $sale;
            })
            ->sortByDesc('similarity_score')
            ->values();

        if ($comparables->isEmpty()) {
            return $this->emptyResult('no_exact_comparables');
        }

        $prices = $comparables
            ->pluck('normalized_price')
            ->map(fn ($price) => (int) $price)
            ->sort()
            ->values();

        $hasSpecificationInputs = $conditionGrade !== null
            || $batteryHealth !== null
            || $batteryCondition !== null
            || $registrationStatus !== null
            || $color !== null;

        $count = $comparables->count();

        if ($count >= 3) {
            $estimate = $this->weightedMedian(
                $comparables,
                'combined_weight'
            );
        } elseif ($hasSpecificationInputs) {
            $estimate = $this->weightedMedian(
                $comparables,
                'similarity_score'
            );
        } else {
            $estimate = $this->median($prices);
        }

        $confidence = match (true) {
            $count >= 6 => 'high',
            $count >= 3 => 'medium',
            default => 'low',
        };

        return [
            'available' => true,
            'reason' => null,
            'estimate' => $estimate,
            'range_min' => (int) $prices->min(),
            'range_max' => (int) $prices->max(),
            'comparable_count' => $count,
            'confidence' => $confidence,
            'current_usd_rate' => $currentUsdRate,
            'specification_adjusted' => $hasSpecificationInputs,
            'comparables' => $comparables->all(),
        ];
    }

    private function similarityScore(
        object $sale,
        ?string $conditionGrade,
        ?int $batteryHealth,
        ?string $batteryCondition,
        ?string $registrationStatus,
        ?string $color
    ): int {
        $distances = [];

        if ($conditionGrade !== null) {
            $conditionRanks = [
                'A+' => 0,
                'A' => 1,
                'B' => 2,
                'C' => 3,
            ];

            $target = $conditionRanks[$conditionGrade] ?? null;
            $actual = $conditionRanks[$sale->condition_grade] ?? null;

            $distances[] = $target !== null && $actual !== null
                ? abs($target - $actual) / 3
                : 1;
        }

        if ($batteryHealth !== null) {
            $distances[] = $sale->battery_health !== null
                ? min(
                    1,
                    abs($batteryHealth - (int) $sale->battery_health) / 100
                )
                : 1;
        }

        if ($batteryCondition !== null) {
            $batteryRanks = [
                'excellent' => 0,
                'good' => 1,
                'poor' => 2,
                'replace' => 3,
            ];

            $target = $batteryRanks[$batteryCondition] ?? null;
            $actual = $batteryRanks[$sale->battery_condition] ?? null;

            $distances[] = $target !== null && $actual !== null
                ? abs($target - $actual) / 3
                : 1;
        }

        if ($registrationStatus !== null) {
            $distances[] = $sale->registration_status === $registrationStatus
                ? 0
                : 1;
        }

        if ($color !== null) {
            $distances[] = $sale->color === $color
                ? 0
                : 1;
        }

        if ($distances === []) {
            return 100;
        }

        $averageDistance = array_sum($distances) / count($distances);

        return (int) round(
            max(0, min(1, 1 - $averageDistance)) * 100
        );
    }

    private function recencyScore(string $saleDate): int
    {
        $daysOld = max(
            0,
            Carbon::parse($saleDate)->startOfDay()->diffInDays(
                now()->startOfDay(),
                false
            )
        );

        if ($daysOld <= 30) {
            return 100;
        }

        if ($daysOld >= 365) {
            return 70;
        }

        $progress = ($daysOld - 30) / (365 - 30);

        return (int) round(
            100 - ($progress * 30)
        );
    }

    private function weightedMedian(
        Collection $comparables,
        string $weightField
    ): int {
        $rows = $comparables
            ->map(fn ($sale) => [
                'price' => (int) $sale->normalized_price,
                'weight' => max(0, (int) ($sale->{$weightField} ?? 0)),
            ])
            ->sortBy('price')
            ->values();

        $totalWeight = (int) $rows->sum('weight');

        if ($totalWeight <= 0) {
            return $this->median(
                $rows->pluck('price')->map(fn ($price) => (int) $price)
            );
        }

        $threshold = $totalWeight / 2;
        $runningWeight = 0;

        foreach ($rows as $row) {
            $runningWeight += $row['weight'];

            if ($runningWeight >= $threshold) {
                return $row['price'];
            }
        }

        return (int) $rows->last()['price'];
    }

    private function median(Collection $values): int
    {
        $values = $values->sort()->values();

        $count = $values->count();
        $middle = intdiv($count, 2);

        if ($count % 2 === 1) {
            return (int) $values[$middle];
        }

        return (int) round(
            ((int) $values[$middle - 1] + (int) $values[$middle]) / 2
        );
    }

    private function emptyResult(string $reason): array
    {
        return [
            'available' => false,
            'reason' => $reason,
            'estimate' => null,
            'range_min' => null,
            'range_max' => null,
            'comparable_count' => 0,
            'confidence' => 'none',
            'current_usd_rate' => null,
            'specification_adjusted' => false,
            'comparables' => [],
        ];
    }
}
