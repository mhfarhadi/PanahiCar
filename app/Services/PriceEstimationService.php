<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PriceEstimationService
{
    public function estimate(
        string $brand,
        string $model,
        string $storage,
        int $currentUsdRate
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
            ->map(function ($sale) use ($currentUsdRate) {
                $sale->normalized_price = (int) round(
                    ((int) $sale->sale_price / (int) $sale->usd_rate)
                    * $currentUsdRate
                );

                return $sale;
            });

        if ($comparables->isEmpty()) {
            return $this->emptyResult('no_exact_comparables');
        }

        $prices = $comparables
            ->pluck('normalized_price')
            ->map(fn ($price) => (int) $price)
            ->sort()
            ->values();

        $estimate = $this->median($prices);

        $minimum = (int) $prices->min();
        $maximum = (int) $prices->max();

        $count = $prices->count();

        $confidence = match (true) {
            $count >= 6 => 'high',
            $count >= 3 => 'medium',
            default => 'low',
        };

        return [
            'available' => true,
            'reason' => null,
            'estimate' => $estimate,
            'range_min' => $minimum,
            'range_max' => $maximum,
            'comparable_count' => $count,
            'confidence' => $confidence,
            'current_usd_rate' => $currentUsdRate,
            'comparables' => $comparables->values()->all(),
        ];
    }

    private function median(Collection $values): int
    {
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
            'comparables' => [],
        ];
    }
}
