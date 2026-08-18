<?php

namespace App\Http\Controllers;

use App\Services\CurrencyRateService;
use App\Support\VehicleCatalogPayload;
use App\Support\VehicleOptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PublicWantedDeviceController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Features/WantedDevice/Index', [
            'catalog' => VehicleCatalogPayload::make(),
        ]);
    }

    public function store(
        Request $request,
        CurrencyRateService $currencyRateService
    ): JsonResponse {
        $request->merge([
            'requester_mobile' => $this->normalizePhone(
                $request->input('requester_mobile')
            ),
        ]);

        $validator = Validator::make($request->all(), [
            'requester_name' => ['required', 'string', 'max:150'],
            'requester_mobile' => [
                'required',
                'string',
                'min:10',
                'max:15',
                'regex:/^\+?[0-9]+$/',
            ],
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:150'],
            'model_year' => ['required', 'integer', 'min:1370', 'max:1500'],
            'color' => ['nullable', 'string', 'max:100'],
            'body_condition' => ['required', Rule::in(array_keys(VehicleOptions::bodyConditions()))],
            'max_price' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'اطلاعات واردشده معتبر نیست.',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $validated = $validator->validated();

        $brand = DB::table('brands')
            ->where('name', $validated['brand'])
            ->where('is_active', true)
            ->first(['id']);

        if (! $brand) {
            return $this->validationError('brand', 'برند انتخاب‌شده معتبر نیست.');
        }

        $model = DB::table('device_models')
            ->where('brand_id', $brand->id)
            ->where('name', $validated['model'])
            ->where('is_active', true)
            ->first(['id']);

        if (! $model) {
            return $this->validationError('model', 'مدل انتخاب‌شده برای این برند معتبر نیست.');
        }

        $usdSnapshot = $currencyRateService->snapshotForDate(
            'USD',
            now()->toDateString()
        ) ?? [];

        $row = [
            'requester_name' => trim($validated['requester_name']),
            'requester_mobile' => $validated['requester_mobile'],
            'brand' => $validated['brand'],
            'model' => $validated['model'],
            'storage' => (string) $validated['model_year'],
            'color' => $validated['color'] ?? null,
            'condition_grade' => $validated['body_condition'],
            'registration_status' => null,
            'battery_health' => null,
            'battery_condition' => null,
            'max_price' => (int) $validated['max_price'],
            'description' => isset($validated['description'])
                ? trim($validated['description'])
                : null,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('wanted_device_requests', 'origin')) {
            $row['origin'] = 'organic';
        }

        if (Schema::hasColumn('wanted_device_requests', 'usd_rate')) {
            $row['usd_rate'] = $usdSnapshot['rate'] ?? null;
            $row['usd_rate_date'] = $usdSnapshot['rate_date'] ?? null;
            $row['usd_rate_source'] = $usdSnapshot['source'] ?? null;
        }

        $id = DB::table('wanted_device_requests')->insertGetId($row);

        return response()->json([
            'message' => 'درخواست خرید خودرو ثبت شد.',
            'request' => [
                'id' => $id,
                'brand' => $validated['brand'],
                'model' => $validated['model'],
                'model_year' => (int) $validated['model_year'],
                'color' => $validated['color'] ?? null,
                'max_price' => (int) $validated['max_price'],
            ],
        ], 201);
    }

    private function validationError(string $field, string $message): JsonResponse
    {
        return response()->json([
            'message' => 'اطلاعات واردشده معتبر نیست.',
            'errors' => [
                $field => [$message],
            ],
        ], 422);
    }

    private function normalizePhone(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = strtr(trim($value), [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);

        return preg_replace('/[\s\-()]/u', '', $normalized);
    }
}
