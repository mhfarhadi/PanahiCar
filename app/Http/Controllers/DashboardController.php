<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;
use Throwable;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $inventoryCount = Device::where('status', 'in_stock')->count();

        return Inertia::render('Dashboard', [
            'inventoryCount' => $inventoryCount,
            'currencyRates' => $this->currencyRates(),
        ]);
    }

    private function currencyRates(): array
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
            return Cache::remember(
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
        } catch (Throwable $exception) {
            report($exception);

            return array_merge($lastRates, [
                'stale' => true,
            ]);
        }
    }
}
