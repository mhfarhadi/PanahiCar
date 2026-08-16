<?php

namespace App\Http\Controllers;

use App\Services\GoldCollateralService;
use App\Services\GoldRateService;
use App\Services\InstallmentCalculatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class PublicGoldCollateralController extends Controller
{
    public function index(GoldRateService $goldRateService): Response
    {
        return Inertia::render('Features/GoldCollateral/Index', [
            'goldRate' => $goldRateService->latest(),
        ]);
    }

    public function calculate(
        Request $request,
        GoldRateService $goldRateService,
        GoldCollateralService $goldCollateralService,
        InstallmentCalculatorService $installmentCalculatorService
    ): JsonResponse {
        $validator = Validator::make($request->all(), [
            'sale_price' => ['required', 'integer', 'min:0'],
            'down_payment' => [
                'required',
                'integer',
                'min:0',
                'lte:sale_price',
            ],
            'monthly_profit_rate' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
            ],
            'installment_count' => [
                'required',
                'integer',
                'min:1',
                'max:60',
            ],
            'sale_date' => ['required', 'date'],
            'first_due_date' => [
                'required',
                'date',
                'after_or_equal:sale_date',
            ],
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

        $goldRate = $goldRateService->latest();
        $ratePerGram = (int) ($goldRate['rate_per_gram'] ?? 0);

        if ($ratePerGram <= 0) {
            return response()->json([
                'message' => 'نرخ طلای ۱۸ عیار در حال حاضر در دسترس نیست. کمی بعد دوباره تلاش کنید.',
            ], 503);
        }

        $collateral = $goldCollateralService->calculate(
            salePrice: (int) $validated['sale_price'],
            downPayment: (int) $validated['down_payment'],
            monthlyProfitRate: (float) $validated['monthly_profit_rate'],
            goldRatePerGram: $ratePerGram,
        );

        $installments = $installmentCalculatorService->calculate(
            salePrice: (int) $validated['sale_price'],
            downPayment: (int) $validated['down_payment'],
            monthlyProfitRate: (float) $validated['monthly_profit_rate'],
            installmentCount: (int) $validated['installment_count'],
            saleDate: $validated['sale_date'],
            firstDueDate: $validated['first_due_date'],
        );

        return response()->json([
            'result' => [
                'collateral' => $collateral,
                'installments' => $installments,
                'gold_rate' => [
                    'item' => GoldRateService::ITEM,
                    'rate_per_gram' => $ratePerGram,
                    'rate_date' => $goldRate['rate_date'] ?? null,
                    'source' => $goldRate['source'] ?? 'navasan',
                    'stale' => (bool) ($goldRate['stale'] ?? false),
                ],
            ],
        ]);
    }
}
