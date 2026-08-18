<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class VehiclePriceEstimateService
{
    public function estimate(
        string $brand,
        string $model,
        ?int $modelYear = null,
        ?string $bodyCondition = null
    ): array {
        $inventoryQuery = DB::table('devices as d')
            ->join('purchases as p', 'p.device_id', '=', 'd.id')
            ->where('d.brand', $brand)
            ->where('d.model', $model)
            ->where('d.status', 'in_stock')
            ->whereNotNull('p.purchase_price')
            ->where('p.purchase_price', '>', 0);

        $salesQuery = DB::table('sales as s')
            ->join('devices as d', 'd.id', '=', 's.device_id')
            ->where('d.brand', $brand)
            ->where('d.model', $model)
            ->whereNotNull('s.sale_price')
            ->where('s.sale_price', '>', 0);

        if ($modelYear) {
            $inventoryQuery->where('d.model_year', $modelYear);
            $salesQuery->where('d.model_year', $modelYear);
        }

        if ($bodyCondition) {
            $inventoryQuery->where('d.body_condition', $bodyCondition);
            $salesQuery->where('d.body_condition', $bodyCondition);
        }

        $inventoryPrices = $inventoryQuery
            ->orderByDesc('d.id')
            ->limit(24)
            ->get([
                'd.model_year',
                'd.mileage',
                'd.color',
                'd.body_condition',
                'p.purchase_price',
            ])
            ->map(fn ($row) => [
                'source' => 'inventory',
                'model_year' => $row->model_year,
                'mileage' => (int) $row->mileage,
                'color' => $row->color,
                'body_condition' => $row->body_condition,
                'price' => (int) round($row->purchase_price * 1.10),
            ]);

        $salePrices = $salesQuery
            ->orderByDesc('s.sale_date')
            ->limit(24)
            ->get([
                'd.model_year',
                'd.mileage',
                'd.color',
                'd.body_condition',
                's.sale_price',
            ])
            ->map(fn ($row) => [
                'source' => 'sale',
                'model_year' => $row->model_year,
                'mileage' => (int) $row->mileage,
                'color' => $row->color,
                'body_condition' => $row->body_condition,
                'price' => (int) $row->sale_price,
            ]);

        $samples = $inventoryPrices
            ->concat($salePrices)
            ->values();

        $prices = $samples
            ->pluck('price')
            ->filter(fn ($price) => $price > 0)
            ->sort()
            ->values();

        if ($prices->isEmpty()) {
            return [
                'available' => false,
                'sample_count' => 0,
                'inventory_count' => $inventoryPrices->count(),
                'sale_count' => $salePrices->count(),
                'suggested_price' => null,
                'low_price' => null,
                'high_price' => null,
                'comparables' => [],
            ];
        }

        $count = $prices->count();
        $mid = (int) floor(($count - 1) / 2);
        $median = $count % 2 === 1
            ? (int) $prices[$mid]
            : (int) round(($prices[$mid] + $prices[$mid + 1]) / 2);

        return [
            'available' => true,
            'sample_count' => $count,
            'inventory_count' => $inventoryPrices->count(),
            'sale_count' => $salePrices->count(),
            'suggested_price' => $median,
            'low_price' => (int) $prices->first(),
            'high_price' => (int) $prices->last(),
            'comparables' => $samples
                ->sortByDesc('price')
                ->take(8)
                ->values()
                ->all(),
        ];
    }
}
