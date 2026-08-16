<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WantedMarketSignalService
{
    private const MIN_UNIQUE_DEMAND = 5;
    private const LOOKBACK_DAYS = 45;
    private const RAW_FALLBACK_DAYS = 14;
    private const MIN_RELATIVE_SCALE = 0.08;
    private const ROBUST_DEVIATION_LIMIT = 4.0;

    public function evaluateCandidate(
        string $brand,
        string $model,
        string $storage,
        int $candidatePrice,
        int $currentUsdRate = 0,
        ?string $conditionGrade = null,
        ?int $batteryHealth = null,
        ?string $batteryCondition = null,
        ?string $registrationStatus = null,
        ?string $color = null
    ): array {
        $rows = $this->preparedDemandRows(
            $brand,
            $model,
            $storage,
            $currentUsdRate,
            $conditionGrade,
            $batteryHealth,
            $batteryCondition,
            $registrationStatus,
            $color
        );

        $rows = $rows
            ->filter(
                fn (array $row) =>
                    ($row['origin'] ?? 'organic') === 'organic'
            )
            ->values();

        $count = $rows->count();

        if ($count < self::MIN_UNIQUE_DEMAND) {
            return [
                'accepted' => true,
                'reason' => 'insufficient_history',
                'unique_demand_count' => $count,
                'reference_price' => null,
                'lower_bound' => null,
                'upper_bound' => null,
                'normalized_candidate_price' => $candidatePrice,
                'method' => 'weighted_median_mad',
            ];
        }

        $referencePrice = $this->weightedMedian(
            $rows,
            'combined_weight'
        );

        $absoluteDeviations = $rows
            ->pluck('normalized_price')
            ->map(fn ($price) => abs((int) $price - $referencePrice))
            ->sort()
            ->values();

        $mad = $this->median($absoluteDeviations);

        $robustScale = max(
            $mad * 1.4826,
            $referencePrice * self::MIN_RELATIVE_SCALE
        );

        $distance = self::ROBUST_DEVIATION_LIMIT * $robustScale;

        $lowerBound = max(
            1,
            (int) round($referencePrice - $distance)
        );

        $upperBound = (int) round(
            $referencePrice + $distance
        );

        $accepted = $candidatePrice >= $lowerBound
            && $candidatePrice <= $upperBound;

        return [
            'accepted' => $accepted,
            'reason' => $accepted ? null : 'gross_price_outlier',
            'unique_demand_count' => $count,
            'reference_price' => $referencePrice,
            'lower_bound' => $lowerBound,
            'upper_bound' => $upperBound,
            'normalized_candidate_price' => $candidatePrice,
            'method' => 'weighted_median_mad',
        ];
    }

    public function demandSummary(
        string $brand,
        string $model,
        string $storage,
        int $currentUsdRate = 0,
        ?string $conditionGrade = null,
        ?int $batteryHealth = null,
        ?string $batteryCondition = null,
        ?string $registrationStatus = null,
        ?string $color = null
    ): array {
        $rows = $this->preparedDemandRows(
            $brand,
            $model,
            $storage,
            $currentUsdRate,
            $conditionGrade,
            $batteryHealth,
            $batteryCondition,
            $registrationStatus,
            $color
        );

        $organicRows = $rows
            ->filter(
                fn (array $row) =>
                    ($row['origin'] ?? 'organic') === 'organic'
            )
            ->values();

        $bootstrapRows = $rows
            ->filter(
                fn (array $row) =>
                    ($row['origin'] ?? 'organic') === 'bootstrap_market'
            )
            ->values();

        // Five unique real colleagues are enough to retire bootstrap
        // influence for this exact brand/model/storage signal.
        $effectiveRows = $organicRows->count() >= self::MIN_UNIQUE_DEMAND
            ? $organicRows
            : $rows;

        if ($effectiveRows->isEmpty()) {
            return $this->emptySummary();
        }

        $referencePrice = $this->weightedMedian(
            $effectiveRows,
            'combined_weight'
        );

        $prices = $effectiveRows
            ->pluck('normalized_price')
            ->map(fn ($price) => (int) $price)
            ->sort()
            ->values();

        $absoluteDeviations = $prices
            ->map(
                fn ($price) =>
                    abs((int) $price - $referencePrice)
            )
            ->sort()
            ->values();

        $mad = $this->median($absoluteDeviations);

        $robustScale = max(
            $mad * 1.4826,
            $referencePrice * self::MIN_RELATIVE_SCALE
        );

        $robustDistance =
            self::ROBUST_DEVIATION_LIMIT * $robustScale;

        $robustLowerBound = max(
            1,
            (int) round(
                $referencePrice - $robustDistance
            )
        );

        $robustUpperBound = (int) round(
            $referencePrice + $robustDistance
        );

        $organicCount = $organicRows->count();
        $bootstrapCount = $bootstrapRows->count();
        $count = $effectiveRows->count();

        return [
            'available' => true,
            'reference_price' => $referencePrice,
            'range_min' => (int) $prices->min(),
            'range_max' => (int) $prices->max(),
            'robust_lower_bound' => $robustLowerBound,
            'robust_upper_bound' => $robustUpperBound,
            'unique_demand_count' => $count,
            'organic_demand_count' => $organicCount,
            'bootstrap_demand_count' => $bootstrapCount,
            'provisional' => $organicCount < self::MIN_UNIQUE_DEMAND
                && $bootstrapCount > 0,
            'organic_consensus' =>
                $organicCount >= self::MIN_UNIQUE_DEMAND,
            'confidence' => match (true) {
                $organicCount >= 15 => 'high',
                $organicCount >= self::MIN_UNIQUE_DEMAND => 'medium',
                default => 'low',
            },
            'lookback_days' => self::LOOKBACK_DAYS,
            'specification_adjusted' => $conditionGrade !== null
                || $batteryHealth !== null
                || $batteryCondition !== null
                || $registrationStatus !== null
                || $color !== null,
        ];
    }

    /**
     * A deliberately broad fallback for gross-price sanity only.
     *
     * Exact-storage pricing remains authoritative everywhere else.
     * Here we inspect OTHER storages of the same model only when the
     * requested storage has no reliable anchor of its own.
     *
     * One random organic request in another storage is not enough.
     * Eligible evidence must contain either bootstrap-market provenance
     * or at least three unique real colleagues for that storage.
     */
    public function modelLevelSanitySummary(
        string $brand,
        string $model,
        string $excludedStorage,
        int $currentUsdRate = 0,
        ?string $conditionGrade = null,
        ?int $batteryHealth = null,
        ?string $batteryCondition = null,
        ?string $registrationStatus = null,
        ?string $color = null
    ): array {
        $storages = DB::table('wanted_device_requests')
            ->where('brand', $brand)
            ->where('model', $model)
            ->where('storage', '!=', $excludedStorage)
            ->where(
                'created_at',
                '>=',
                now()->subDays(self::LOOKBACK_DAYS)
            )
            ->distinct()
            ->pluck('storage');

        $summaries = $storages
            ->map(function ($storage) use (
                $brand,
                $model,
                $currentUsdRate,
                $conditionGrade,
                $batteryHealth,
                $batteryCondition,
                $registrationStatus,
                $color
            ) {
                $summary = $this->demandSummary(
                    $brand,
                    $model,
                    $storage,
                    $currentUsdRate,
                    $conditionGrade,
                    $batteryHealth,
                    $batteryCondition,
                    $registrationStatus,
                    $color
                );

                if (! ($summary['available'] ?? false)) {
                    return null;
                }

                $organicCount = (int) (
                    $summary['organic_demand_count'] ?? 0
                );

                $bootstrapCount = (int) (
                    $summary['bootstrap_demand_count'] ?? 0
                );

                /*
                 * Do not let one stray organic request in another storage
                 * become a model-level price authority.
                 */
                if (
                    $bootstrapCount <= 0
                    && $organicCount < 3
                ) {
                    return null;
                }

                $referencePrice = (int) (
                    $summary['reference_price'] ?? 0
                );

                if ($referencePrice <= 0) {
                    return null;
                }

                return [
                    'storage' => $storage,
                    'normalized_price' => $referencePrice,
                    'combined_weight' => max(
                        1,
                        ($organicCount * 100)
                        + ($bootstrapCount * 35)
                    ),
                    'organic_count' => $organicCount,
                    'bootstrap_count' => $bootstrapCount,
                ];
            })
            ->filter()
            ->values();

        if ($summaries->isEmpty()) {
            return [
                'available' => false,
                'reference_price' => null,
                'robust_lower_bound' => null,
                'robust_upper_bound' => null,
                'evidence_storage_count' => 0,
                'organic_demand_count' => 0,
                'bootstrap_demand_count' => 0,
                'scope' => 'model_other_storage',
            ];
        }

        $referencePrice = $this->weightedMedian(
            $summaries,
            'combined_weight'
        );

        $prices = $summaries
            ->pluck('normalized_price')
            ->map(fn ($price) => (int) $price)
            ->sort()
            ->values();

        $absoluteDeviations = $prices
            ->map(
                fn ($price) =>
                    abs((int) $price - $referencePrice)
            )
            ->sort()
            ->values();

        $mad = $this->median($absoluteDeviations);

        $robustScale = max(
            $mad * 1.4826,
            $referencePrice * self::MIN_RELATIVE_SCALE
        );

        $distance =
            self::ROBUST_DEVIATION_LIMIT * $robustScale;

        return [
            'available' => true,
            'reference_price' => $referencePrice,
            'robust_lower_bound' => max(
                1,
                (int) round($referencePrice - $distance)
            ),
            'robust_upper_bound' =>
                (int) round($referencePrice + $distance),
            'evidence_storage_count' => $summaries->count(),
            'organic_demand_count' =>
                (int) $summaries->sum('organic_count'),
            'bootstrap_demand_count' =>
                (int) $summaries->sum('bootstrap_count'),
            'scope' => 'model_other_storage',
        ];
    }

    private function preparedDemandRows(
        string $brand,
        string $model,
        string $storage,
        int $currentUsdRate,
        ?string $conditionGrade,
        ?int $batteryHealth,
        ?string $batteryCondition,
        ?string $registrationStatus,
        ?string $color
    ): Collection {
        $hasSpecificationInputs = $conditionGrade !== null
            || $batteryHealth !== null
            || $batteryCondition !== null
            || $registrationStatus !== null
            || $color !== null;

        return $this->effectiveDemandRows(
            $brand,
            $model,
            $storage,
            $currentUsdRate
        )
            ->map(function (array $row) use (
                $conditionGrade,
                $batteryHealth,
                $batteryCondition,
                $registrationStatus,
                $color
            ) {
                $row['similarity_score'] = $this->similarityScore(
                    $row,
                    $conditionGrade,
                    $batteryHealth,
                    $batteryCondition,
                    $registrationStatus,
                    $color
                );

                $row['recency_score'] = $this->recencyScore(
                    $row['created_at']
                );

                $sourceWeight = ($row['origin'] ?? 'organic')
                    === 'bootstrap_market'
                        ? 35
                        : 100;

                $row['combined_weight'] = (int) round(
                    (
                        $row['similarity_score']
                        * $row['recency_score']
                        * $sourceWeight
                    ) / 10000
                );

                return $row;
            })
            /*
             * If the user supplied specific characteristics, a historical
             * request with zero similarity must not create fake consensus.
             * This keeps anti-abuse conservative around legitimate variants.
             */
            ->when(
                $hasSpecificationInputs,
                fn (Collection $rows) => $rows
                    ->filter(fn (array $row) => $row['combined_weight'] > 0)
            )
            ->values();
    }

    private function effectiveDemandRows(
        string $brand,
        string $model,
        string $storage,
        int $currentUsdRate
    ): Collection {
        $rawFallbackCutoff = now()
            ->subDays(self::RAW_FALLBACK_DAYS)
            ->startOfDay();

        return DB::table('wanted_device_requests')
            ->where('brand', $brand)
            ->where('model', $model)
            ->where('storage', $storage)
            ->where('created_at', '>=', now()->subDays(self::LOOKBACK_DAYS))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get([
                'id',
                'requester_mobile',
                'origin',
                'market_reference_source',
                'max_price',
                'usd_rate',
                'usd_rate_date',
                'condition_grade',
                'registration_status',
                'battery_health',
                'battery_condition',
                'color',
                'created_at',
            ])
            /*
             * One colleague = one effective opinion for the same exact
             * brand/model/storage. Their latest request replaces older ones.
             */
            ->groupBy('requester_mobile')
            ->map(fn (Collection $requests) => $requests->first())
            ->map(function ($request) use (
                $currentUsdRate,
                $rawFallbackCutoff
            ) {
                $price = (int) $request->max_price;
                $requestUsdRate = (int) ($request->usd_rate ?? 0);

                if ($currentUsdRate > 0 && $requestUsdRate > 0) {
                    $normalizedPrice = (int) round(
                        ($price / $requestUsdRate) * $currentUsdRate
                    );
                } elseif (
                    Carbon::parse($request->created_at)
                        ->gte($rawFallbackCutoff)
                ) {
                    $normalizedPrice = $price;
                } else {
                    return null;
                }

                return [
                    'id' => $request->id,
                    'requester_mobile' => $request->requester_mobile,
                    'origin' => $request->origin ?? 'organic',
                    'market_reference_source' =>
                        $request->market_reference_source ?? null,
                    'normalized_price' => $normalizedPrice,
                    'condition_grade' => $request->condition_grade,
                    'registration_status' => $request->registration_status,
                    'battery_health' => $request->battery_health !== null
                        ? (int) $request->battery_health
                        : null,
                    'battery_condition' => $request->battery_condition,
                    'color' => $request->color,
                    'created_at' => $request->created_at,
                ];
            })
            ->filter()
            ->values();
    }

    private function similarityScore(
        array $row,
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
            $actual = $conditionRanks[$row['condition_grade']] ?? null;

            $distances[] = $target !== null && $actual !== null
                ? abs($target - $actual) / 3
                : 1;
        }

        if ($batteryHealth !== null) {
            $distances[] = $row['battery_health'] !== null
                ? min(
                    1,
                    abs($batteryHealth - (int) $row['battery_health']) / 100
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
            $actual = $batteryRanks[$row['battery_condition']] ?? null;

            $distances[] = $target !== null && $actual !== null
                ? abs($target - $actual) / 3
                : 1;
        }

        if ($registrationStatus !== null) {
            $distances[] = $row['registration_status'] === $registrationStatus
                ? 0
                : 1;
        }

        if ($color !== null) {
            $distances[] = $row['color'] === $color
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

    private function recencyScore(string $createdAt): int
    {
        $daysOld = max(
            0,
            (int) Carbon::parse($createdAt)->diffInDays(now())
        );

        if ($daysOld <= 7) {
            return 100;
        }

        if ($daysOld >= self::LOOKBACK_DAYS) {
            return 55;
        }

        $progress = ($daysOld - 7)
            / (self::LOOKBACK_DAYS - 7);

        return (int) round(
            100 - ($progress * 45)
        );
    }

    private function weightedMedian(
        Collection $rows,
        string $weightField
    ): int {
        $rows = $rows
            ->map(fn (array $row) => [
                'price' => (int) $row['normalized_price'],
                'weight' => max(
                    0,
                    (int) ($row[$weightField] ?? 0)
                ),
            ])
            ->sortBy('price')
            ->values();

        $totalWeight = (int) $rows->sum('weight');

        if ($totalWeight <= 0) {
            return $this->median(
                $rows->pluck('price')
            );
        }

        $threshold = $totalWeight / 2;
        $runningWeight = 0;

        foreach ($rows as $row) {
            $runningWeight += $row['weight'];

            if ($runningWeight >= $threshold) {
                return (int) $row['price'];
            }
        }

        return (int) $rows->last()['price'];
    }

    private function median(Collection $values): int
    {
        $values = $values
            ->map(fn ($value) => (int) $value)
            ->sort()
            ->values();

        $count = $values->count();

        if ($count === 0) {
            return 0;
        }

        $middle = intdiv($count, 2);

        if ($count % 2 === 1) {
            return (int) $values[$middle];
        }

        return (int) round(
            ((int) $values[$middle - 1]
                + (int) $values[$middle]) / 2
        );
    }

    private function emptySummary(): array
    {
        return [
            'available' => false,
            'reference_price' => null,
            'range_min' => null,
            'range_max' => null,
            'robust_lower_bound' => null,
            'robust_upper_bound' => null,
            'unique_demand_count' => 0,
            'organic_demand_count' => 0,
            'bootstrap_demand_count' => 0,
            'provisional' => false,
            'organic_consensus' => false,
            'confidence' => 'none',
            'lookback_days' => self::LOOKBACK_DAYS,
            'specification_adjusted' => false,
        ];
    }
}
