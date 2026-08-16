<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Morilog\Jalali\Jalalian;
use RuntimeException;
use Throwable;

class GoldRateService
{
    public const ITEM = '18ayar';

    public function latest(): array
    {
        $lastRate = Cache::get('navasan:last_gold_18ayar', [
            'rate_per_gram' => null,
            'rate_date' => null,
            'source' => 'navasan',
            'stale' => true,
        ]);

        $apiKey = config('services.navasan.key');

        if (! $apiKey) {
            return $lastRate;
        }

        try {
            $snapshot = Cache::remember(
                'navasan:gold_18ayar',
                now()->addMinutes(config('services.navasan.cache_minutes', 360)),
                function () use ($apiKey) {
                    $response = Http::timeout(6)
                        ->retry(1, 250)
                        ->get('https://api.navasan.tech/latest/', [
                            'api_key' => $apiKey,
                        ]);

                    $response->throw();

                    $data = $response->json();
                    $gold = $data[self::ITEM] ?? null;

                    if (! is_array($gold)) {
                        throw new RuntimeException('Navasan 18ayar rate is missing.');
                    }

                    $value = (int) str_replace(
                        ',',
                        '',
                        (string) ($gold['value'] ?? 0)
                    );

                    if ($value <= 0) {
                        throw new RuntimeException('Navasan 18ayar rate is invalid.');
                    }

                    $snapshot = [
                        'rate_per_gram' => $value,
                        'rate_date' => now()->toDateString(),
                        'source' => 'navasan',
                        'stale' => false,
                    ];

                    Cache::forever('navasan:last_gold_18ayar', $snapshot);

                    return $snapshot;
                }
            );

            $this->archive($snapshot);

            return $snapshot;
        } catch (Throwable $exception) {
            report($exception);

            return array_merge($lastRate, [
                'stale' => true,
            ]);
        }
    }

    public function snapshotForDate(string $date): ?array
    {
        $today = now()->toDateString();

        if ($date === $today) {
            $this->latest();
        }

        $rate = $this->archivedRate($date);

        if (! $rate && $date < $today) {
            $this->fetchHistoricalRate($date);
            $rate = $this->archivedRate($date);
        }

        if (! $rate) {
            return null;
        }

        return [
            'rate_per_gram' => (int) $rate->rate_per_gram,
            'rate_date' => $rate->rate_date,
            'source' => $rate->source,
        ];
    }

    private function archivedRate(string $date): ?object
    {
        return DB::table('gold_rates')
            ->where('item', self::ITEM)
            ->where('rate_date', $date)
            ->first();
    }

    private function fetchHistoricalRate(string $date): ?int
    {
        $apiKey = config('services.navasan.key');

        if (! $apiKey) {
            return null;
        }

        try {
            $jalaliDate = Jalalian::fromCarbon(
                Carbon::parse($date)
            )->format('Y-m-d');

            $response = Http::timeout(30)
                ->retry(2, 1000)
                ->get('https://api.navasan.tech/dailyCurrency/', [
                    'api_key' => $apiKey,
                    'item' => self::ITEM,
                    'date' => $jalaliDate,
                ]);

            $response->throw();

            $rows = $response->json();

            if (! is_array($rows) || $rows === []) {
                return null;
            }

            $lastRow = collect($rows)
                ->filter(fn ($row) =>
                    is_array($row)
                    && isset($row['value'])
                    && is_numeric($row['value'])
                    && (float) $row['value'] > 0
                )
                ->sortBy(fn ($row) => (int) ($row['timestamp'] ?? 0))
                ->last();

            if (! $lastRow) {
                return null;
            }

            $value = (int) round((float) $lastRow['value']);

            DB::table('gold_rates')->updateOrInsert(
                [
                    'item' => self::ITEM,
                    'rate_date' => $date,
                ],
                [
                    'rate_per_gram' => $value,
                    'source' => 'navasan_historical_last',
                    'fetched_at' => now(),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            return $value;
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }

    private function archive(array $snapshot): void
    {
        $value = (int) ($snapshot['rate_per_gram'] ?? 0);
        $date = $snapshot['rate_date'] ?? null;

        if ($value <= 0 || ! $date) {
            return;
        }

        DB::table('gold_rates')->updateOrInsert(
            [
                'item' => self::ITEM,
                'rate_date' => $date,
            ],
            [
                'rate_per_gram' => $value,
                'source' => $snapshot['source'] ?? 'navasan',
                'fetched_at' => now(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
