<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class CurrencyRateService
{
    public function latest(): array
    {
        $lastRates = Cache::get('navasan:last_rates', [
            'usd' => null,
            'aed' => null,
            'source' => 'navasan',
            'stale' => true,
        ]);

        $apiKey = config('services.navasan.key');

        if (! $apiKey) {
            return $lastRates;
        }

        try {
            $rates = Cache::remember(
                'navasan:dashboard_rates',
                now()->addMinutes(config('services.navasan.cache_minutes', 360)),
                function () use ($apiKey) {
                    $response = Http::timeout(6)
                        ->retry(1, 250)
                        ->get('https://api.navasan.tech/latest/', [
                            'api_key' => $apiKey,
                        ]);

                    $response->throw();

                    $data = $response->json();

                    $usd = $data['usd_sell'] ?? null;
                    $aed = $data['aed_sell'] ?? $data['dirham_dubai'] ?? null;

                    if (! is_array($usd) || ! is_array($aed)) {
                        throw new RuntimeException('Navasan currency rates are missing.');
                    }

                    $rates = [
                        'usd' => [
                            'value' => (int) str_replace(',', '', (string) ($usd['value'] ?? 0)),
                            'change' => (int) ($usd['change'] ?? 0),
                            'date' => $usd['date'] ?? null,
                        ],
                        'aed' => [
                            'value' => (int) str_replace(',', '', (string) ($aed['value'] ?? 0)),
                            'change' => (int) ($aed['change'] ?? 0),
                            'date' => $aed['date'] ?? null,
                        ],
                        'source' => 'navasan',
                        'stale' => false,
                    ];

                    Cache::forever('navasan:last_rates', $rates);

                    return $rates;
                }
            );

            $this->archive($rates);

            return $rates;
        } catch (Throwable $exception) {
            report($exception);

            return array_merge($lastRates, [
                'stale' => true,
            ]);
        }
    }

    public function rateForDate(string $currency, string $date): ?int
    {
        return DB::table('exchange_rates')
            ->where('currency', strtoupper($currency))
            ->where('rate_date', $date)
            ->value('rate');
    }

    public function snapshotForDate(string $currency, string $date): ?array
    {
        if ($date === now()->toDateString()) {
            $this->latest();
        }

        $rate = DB::table('exchange_rates')
            ->where('currency', strtoupper($currency))
            ->where('rate_date', $date)
            ->first();

        if (! $rate) {
            return null;
        }

        return [
            'rate' => (int) $rate->rate,
            'rate_date' => $rate->rate_date,
            'source' => $rate->source,
        ];
    }

    private function archive(array $rates): void
    {
        $today = now()->toDateString();
        $source = $rates['source'] ?? 'navasan';

        foreach (['usd', 'aed'] as $currency) {
            $value = (int) ($rates[$currency]['value'] ?? 0);

            if ($value <= 0) {
                continue;
            }

            DB::table('exchange_rates')->updateOrInsert(
                [
                    'currency' => strtoupper($currency),
                    'rate_date' => $today,
                ],
                [
                    'rate' => $value,
                    'source' => $source,
                    'fetched_at' => now(),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
