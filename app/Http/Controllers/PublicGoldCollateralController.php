<?php

namespace App\Http\Controllers;

use App\Services\GoldCollateralService;
use App\Services\InstallmentCalculatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class PublicGoldCollateralController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Features/GoldCollateral/Index');
    }

    public function calculate(
        Request $request,
        GoldCollateralService $goldCollateralService,
        InstallmentCalculatorService $installmentCalculatorService
    ): JsonResponse {
        $validator = Validator::make($request->all(), [
            'sale_price' => ['required', 'integer', 'min:0'],
            'down_payment' => ['required', 'integer', 'min:0', 'lte:sale_price'],
            'monthly_profit_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'installment_count' => ['required', 'integer', 'min:1', 'max:60'],
            'sale_date' => ['required', 'date'],
            'first_due_date' => ['required', 'date', 'after_or_equal:sale_date'],
            'gold_rate_per_gram' => ['required', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'اطلاعات واردشده معتبر نیست.',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $validated = $validator->validated();

        $deferment = $installmentCalculatorService->calculateDeferment(
            $validated['sale_date'],
            $validated['first_due_date']
        );

        if ($deferment['is_before_standard']) {
            return response()->json([
                'message' => 'اطلاعات واردشده معتبر نیست.',
                'errors' => [
                    'first_due_date' => [
                        'اولین سررسید نمی‌تواند قبل از یک ماه شمسی پس از تاریخ فروش باشد.',
                    ],
                ],
            ], 422);
        }

        $ratePerGram = (int) $validated['gold_rate_per_gram'];

        return response()->json([
            'result' => [
                'collateral' => $goldCollateralService->calculate(
                    salePrice: (int) $validated['sale_price'],
                    downPayment: (int) $validated['down_payment'],
                    monthlyProfitRate: (float) $validated['monthly_profit_rate'],
                    goldRatePerGram: $ratePerGram,
                ),
                'installments' => $installmentCalculatorService->calculate(
                    salePrice: (int) $validated['sale_price'],
                    downPayment: (int) $validated['down_payment'],
                    monthlyProfitRate: (float) $validated['monthly_profit_rate'],
                    installmentCount: (int) $validated['installment_count'],
                    saleDate: $validated['sale_date'],
                    firstDueDate: $validated['first_due_date'],
                ),
                'gold_rate' => [
                    'rate_per_gram' => $ratePerGram,
                    'source' => 'manual',
                ],
            ],
        ]);
    }
}
