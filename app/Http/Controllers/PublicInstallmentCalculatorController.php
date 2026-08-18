<?php

namespace App\Http\Controllers;

use App\Services\InstallmentCalculatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class PublicInstallmentCalculatorController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Features/Installments/Index');
    }

    public function calculate(
        Request $request,
        InstallmentCalculatorService $installmentCalculatorService
    ): JsonResponse {
        $validator = Validator::make($request->all(), [
            'mode' => ['required', 'in:regular,monthly_cap,custom'],
            'sale_price' => ['required', 'integer', 'min:0'],
            'down_payment' => ['required', 'integer', 'min:0', 'lte:sale_price'],
            'monthly_profit_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'installment_count' => ['required_if:mode,regular', 'nullable', 'integer', 'min:1', 'max:60'],
            'monthly_cap' => ['required_if:mode,monthly_cap', 'nullable', 'integer', 'min:1'],
            'payments' => ['required_if:mode,custom', 'nullable', 'array', 'min:1'],
            'payments.*.due_date' => ['required_if:mode,custom', 'date', 'after_or_equal:sale_date'],
            'payments.*.amount' => ['required_if:mode,custom', 'integer', 'min:1'],
            'sale_date' => ['required', 'date'],
            'first_due_date' => ['required_unless:mode,custom', 'nullable', 'date', 'after_or_equal:sale_date'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'اطلاعات واردشده معتبر نیست.',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $validated = $validator->validated();

        if ($validated['mode'] === 'custom') {
            $previousDate = $validated['sale_date'];

            foreach ($validated['payments'] as $payment) {
                if ($payment['due_date'] < $previousDate) {
                    return response()->json([
                        'message' => 'اطلاعات واردشده معتبر نیست.',
                        'errors' => [
                            'payments' => ['تاریخ چک‌ها باید به ترتیب زمانی وارد شوند.'],
                        ],
                    ], 422);
                }

                $previousDate = $payment['due_date'];
            }

            return response()->json([
                'available' => true,
                'result' => $installmentCalculatorService->calculateCustom(
                    salePrice: (int) $validated['sale_price'],
                    downPayment: (int) $validated['down_payment'],
                    monthlyProfitRate: (float) $validated['monthly_profit_rate'],
                    saleDate: $validated['sale_date'],
                    payments: $validated['payments'],
                ),
            ]);
        }

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

        if ($validated['mode'] === 'monthly_cap') {
            $result = $installmentCalculatorService->findWithinMonthlyCap(
                salePrice: (int) $validated['sale_price'],
                downPayment: (int) $validated['down_payment'],
                monthlyProfitRate: (float) $validated['monthly_profit_rate'],
                monthlyCap: (int) $validated['monthly_cap'],
                saleDate: $validated['sale_date'],
                firstDueDate: $validated['first_due_date'],
            );

            return response()->json([
                'available' => $result !== null,
                'result' => $result,
            ]);
        }

        return response()->json([
            'available' => true,
            'result' => $installmentCalculatorService->calculate(
                salePrice: (int) $validated['sale_price'],
                downPayment: (int) $validated['down_payment'],
                monthlyProfitRate: (float) $validated['monthly_profit_rate'],
                installmentCount: (int) $validated['installment_count'],
                saleDate: $validated['sale_date'],
                firstDueDate: $validated['first_due_date'],
            ),
        ]);
    }
}
