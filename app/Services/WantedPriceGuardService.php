<?php

namespace App\Services;

class WantedPriceGuardService
{
    /**
     * Bootstrap/external-market data is provisional and has reduced weight.
     * It cannot create organic consensus, but it may reject only grossly absurd
     * prices through the deliberately widened extreme-sanity corridor.
     *
     * When it is corroborated by a real completed-sale anchor, these very
     * conservative sale ratios only act as a second gate for gross extremes.
     * Normal negotiation space remains intentionally wide.
     */
    private const PROVISIONAL_SALE_FLOOR_RATIO = 0.35;
    private const PROVISIONAL_SALE_CEILING_RATIO = 1.35;
    private const MIN_SALE_SIMILARITY = 25;

    /**
     * Bootstrap is weak evidence, so its normal robust corridor does not
     * reject a colleague. Only a candidate beyond TWICE that already-wide
     * corridor is treated as an obvious sanity failure.
     *
     * This catches inputs like 2M or 7M against an ~80M market signal while
     * still allowing aggressive but plausible professional bids.
     */
    private const PROVISIONAL_EXTREME_CORRIDOR_MULTIPLIER = 2.0;

    public function __construct(
        private WantedMarketSignalService $wantedMarketSignalService,
        private PriceEstimationService $priceEstimationService
    ) {
    }

    public function evaluate(
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
        /*
         * First authority: enough UNIQUE organic colleagues.
         * If real demand consensus exists, its robust MAD guard can reject
         * without any bootstrap or external-market help.
         */
        $organicGuard = $this->wantedMarketSignalService->evaluateCandidate(
            $brand,
            $model,
            $storage,
            $candidatePrice,
            $currentUsdRate,
            $conditionGrade,
            $batteryHealth,
            $batteryCondition,
            $registrationStatus,
            $color
        );

        if (! $organicGuard['accepted']) {
            return array_merge($organicGuard, [
                'guard_source' => 'organic_demand_consensus',
            ]);
        }

        /*
         * Sparse organic history:
         * bootstrap may only PARTICIPATE if an independent real completed-sale
         * anchor corroborates that the candidate is an extreme.
         */
        $demand = $this->wantedMarketSignalService->demandSummary(
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

        if ($currentUsdRate <= 0) {
            return array_merge($organicGuard, [
                'guard_source' => 'organic_only_sparse',
            ]);
        }

        /*
         * If exact storage already has reliable real consensus and the
         * organic guard accepted the candidate, do not second-guess it
         * with another storage.
         */
        if ($demand['organic_consensus'] ?? false) {
            return array_merge($organicGuard, [
                'guard_source' =>
                    'organic_consensus_plausible',
            ]);
        }

        $referenceScope = 'exact_storage';
        $sanityDemand = $demand;

        /*
         * Sparse organic-only exact history is not a trustworthy anchor.
         * Look at OTHER storages of the same model strictly for gross
         * sanity. This never changes the actual estimator result.
         */
        if (
            ! ($demand['available'] ?? false)
            || ! ($demand['provisional'] ?? false)
        ) {
            $modelFallback =
                $this->wantedMarketSignalService
                    ->modelLevelSanitySummary(
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

            if ($modelFallback['available'] ?? false) {
                $sanityDemand = $modelFallback;
                $referenceScope = 'model_other_storage';
            }
        }

        if (! ($sanityDemand['available'] ?? false)) {
            return array_merge($organicGuard, [
                'guard_source' =>
                    'no_reliable_price_anchor',
            ]);
        }

        /*
         * Exact-storage provisional evidence or model-level fallback
         * now goes through the same very-wide extreme corridor.
         */
        $demandReference = (int) (
            $sanityDemand['reference_price'] ?? 0
        );

        $demandLower = (int) (
            $sanityDemand['robust_lower_bound'] ?? 0
        );

        $demandUpper = (int) (
            $sanityDemand['robust_upper_bound'] ?? 0
        );

        if (
            $demandReference > 0
            && $demandLower > 0
            && $demandUpper > 0
        ) {
            $lowerDistance = max(
                0,
                $demandReference - $demandLower
            );

            $upperDistance = max(
                0,
                $demandUpper - $demandReference
            );

            $extremeLower = max(
                1,
                (int) round(
                    $demandReference
                    - (
                        $lowerDistance
                        * self::PROVISIONAL_EXTREME_CORRIDOR_MULTIPLIER
                    )
                )
            );

            $extremeUpper = (int) round(
                $demandReference
                + (
                    $upperDistance
                    * self::PROVISIONAL_EXTREME_CORRIDOR_MULTIPLIER
                )
            );

            if (
                $candidatePrice < $extremeLower
                || $candidatePrice > $extremeUpper
            ) {
                return [
                    'accepted' => false,
                    'reason' => $candidatePrice < $extremeLower
                        ? 'corroborated_gross_low_outlier'
                        : 'corroborated_gross_high_outlier',
                    'guard_source' =>
                        'provisional_extreme_sanity_guard',
                    'unique_demand_count' =>
                        (int) (
                            $sanityDemand['unique_demand_count']
                            ?? (
                                (
                                    $sanityDemand['organic_demand_count']
                                    ?? 0
                                )
                                + (
                                    $sanityDemand['bootstrap_demand_count']
                                    ?? 0
                                )
                            )
                        ),
                    'reference_price' => $demandReference,
                    'lower_bound' => $extremeLower,
                    'upper_bound' => $extremeUpper,
                    'normalized_candidate_price' => $candidatePrice,
                    'demand_reference_price' => $demandReference,
                    'sale_reference_price' => null,
                    'reference_scope' => $referenceScope,
                    'method' =>
                        $referenceScope === 'model_other_storage'
                            ? 'model_level_extreme_sanity'
                            : 'extended_robust_provisional_corridor',
                ];
            }
        }

        if ($referenceScope === 'model_other_storage') {
            return array_merge($organicGuard, [
                'guard_source' =>
                    'model_fallback_plausible',
                'demand_reference_price' =>
                    $demandReference,
                'reference_scope' =>
                    'model_other_storage',
            ]);
        }

        $sale = $this->priceEstimationService->estimate(
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

        if (
            ! ($sale['available'] ?? false)
            || (int) ($sale['estimate'] ?? 0) <= 0
            || (int) ($sale['comparable_count'] ?? 0) <= 0
        ) {
            return array_merge($organicGuard, [
                'guard_source' => 'provisional_without_sale_anchor',
            ]);
        }

        $bestSaleSimilarity = collect($sale['comparables'] ?? [])
            ->max(fn ($comparable) =>
                (int) ($comparable->similarity_score ?? 0)
            );

        if ((int) $bestSaleSimilarity < self::MIN_SALE_SIMILARITY) {
            return array_merge($organicGuard, [
                'guard_source' => 'sale_anchor_too_dissimilar',
            ]);
        }

        if ($demandLower <= 0 || $demandUpper <= 0) {
            return array_merge($organicGuard, [
                'guard_source' => 'provisional_bounds_unavailable',
            ]);
        }

        $saleEstimate = (int) $sale['estimate'];

        $saleExtremeFloor = (int) round(
            $saleEstimate * self::PROVISIONAL_SALE_FLOOR_RATIO
        );

        $saleExtremeCeiling = (int) round(
            $saleEstimate * self::PROVISIONAL_SALE_CEILING_RATIO
        );

        /*
         * Reject ONLY if two independent signals agree:
         *
         * Low:
         * candidate is below robust demand corridor
         * AND absurdly below completed-sale value.
         *
         * High:
         * candidate is above robust demand corridor
         * AND even above completed-sale value by a large margin.
         *
         * Bootstrap remains provisional here: it cannot establish organic consensus,
         * but a gross extreme outside the widened sanity corridor may still be rejected.
         */
        $grossLowOutlier = $candidatePrice < $demandLower
            && $candidatePrice < $saleExtremeFloor;

        $grossHighOutlier = $candidatePrice > $demandUpper
            && $candidatePrice > $saleExtremeCeiling;

        if (! $grossLowOutlier && ! $grossHighOutlier) {
            return array_merge($organicGuard, [
                'guard_source' => 'corroborated_but_plausible',
                'demand_reference_price' =>
                    $demand['reference_price'] ?? null,
                'sale_reference_price' => $saleEstimate,
            ]);
        }

        return [
            'accepted' => false,
            'reason' => $grossLowOutlier
                ? 'corroborated_gross_low_outlier'
                : 'corroborated_gross_high_outlier',
            'guard_source' => 'demand_plus_completed_sale',
            'unique_demand_count' =>
                (int) ($demand['unique_demand_count'] ?? 0),
            'reference_price' =>
                $demand['reference_price'] ?? null,
            'lower_bound' => max(
                $demandLower,
                $saleExtremeFloor
            ),
            'upper_bound' => min(
                $demandUpper,
                $saleExtremeCeiling
            ),
            'normalized_candidate_price' => $candidatePrice,
            'demand_reference_price' =>
                $demand['reference_price'] ?? null,
            'sale_reference_price' => $saleEstimate,
            'method' => 'two_anchor_corroboration',
        ];
    }
}
