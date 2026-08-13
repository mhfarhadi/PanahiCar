<?php

namespace App\Http\Controllers;

use App\Services\CurrencyRateService;
use App\Services\PriceEstimationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PriceEstimateController extends Controller
{
    public function index(
        Request $request,
        CurrencyRateService $currencyRateService,
        PriceEstimationService $priceEstimationService
    ): Response {
        $brand = trim((string) $request->query('brand'));
        $model = trim((string) $request->query('model'));
        $storage = trim((string) $request->query('storage'));

        $currentRates = $currencyRateService->latest();
        $currentUsdRate = (int) ($currentRates['usd']['value'] ?? 0);

        $estimate = null;

        if ($brand !== '' && $model !== '' && $storage !== '') {
            $estimate = $priceEstimationService->estimate(
                $brand,
                $model,
                $storage,
                $currentUsdRate
            );
        }

        return Inertia::render('PriceEstimates/Index', [
            'catalog' => [
                'brands' => DB::table('brands')
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get([
                        'id',
                        'name',
                    ]),

                'models' => DB::table('device_models')
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get([
                        'id',
                        'brand_id',
                        'name',
                    ]),

                'storages' => DB::table('storage_options')
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->get([
                        'id',
                        'name',
                    ]),

                'modelStorages' => DB::table('device_model_storage_option')
                    ->get([
                        'device_model_id',
                        'storage_option_id',
                    ]),
            ],

            'filters' => [
                'brand' => $brand,
                'model' => $model,
                'storage' => $storage,
            ],

            'currentUsdRate' => $currentUsdRate,
            'estimate' => $estimate,
        ]);
    }
}
