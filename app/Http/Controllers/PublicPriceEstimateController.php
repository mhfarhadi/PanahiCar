<?php

namespace App\Http\Controllers;

use App\Services\VehiclePriceEstimateService;
use App\Support\VehicleCatalogPayload;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicPriceEstimateController extends Controller
{
    public function index(
        Request $request,
        VehiclePriceEstimateService $vehiclePriceEstimateService
    ): Response {
        $brand = trim((string) $request->query('brand'));
        $model = trim((string) $request->query('model'));
        $modelYear = $request->filled('model_year')
            ? $request->integer('model_year')
            : null;
        $bodyCondition = $request->filled('body_condition')
            ? (string) $request->query('body_condition')
            : null;

        $estimate = null;

        if ($brand !== '' && $model !== '') {
            $estimate = $vehiclePriceEstimateService->estimate(
                $brand,
                $model,
                $modelYear,
                $bodyCondition
            );
        }

        return Inertia::render('Features/PriceEstimate/Index', [
            'catalog' => VehicleCatalogPayload::make(),
            'filters' => [
                'brand' => $brand,
                'model' => $model,
                'model_year' => $modelYear,
                'body_condition' => $bodyCondition,
            ],
            'estimate' => $estimate,
        ]);
    }
}
