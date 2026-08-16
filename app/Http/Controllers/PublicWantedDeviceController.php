<?php

namespace App\Http\Controllers;

use App\Services\CurrencyRateService;
use App\Services\WantedPriceGuardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class PublicWantedDeviceController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Features/WantedDevice/Index', [
            'catalog' => [
                'brands' => DB::table('brands')
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get(['id', 'name']),

                'models' => DB::table('device_models')
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get(['id', 'brand_id', 'name']),

                'storages' => DB::table('storage_options')
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->get(['id', 'name']),

                'colors' => DB::table('color_options')
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->get(['id', 'name']),

                'modelStorages' => DB::table('device_model_storage_option')
                    ->get([
                        'device_model_id',
                        'storage_option_id',
                    ]),

                'modelColors' => DB::table('device_model_color_option')
                    ->get([
                        'device_model_id',
                        'color_option_id',
                    ]),
            ],
        ]);
    }

    public function store(
        Request $request,
        CurrencyRateService $currencyRateService,
        WantedPriceGuardService $wantedPriceGuardService
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
            'storage' => ['required', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:100'],
            'condition_grade' => [
                'required',
                'in:A+,A,B,C',
            ],
            'registration_status' => [
                'required',
                'in:registered,unregistered',
            ],
            'battery_health' => [
                'nullable',
                'required_unless:brand,Samsung',
                'integer',
                'min:0',
                'max:100',
            ],
            'battery_condition' => [
                'nullable',
                'required_if:brand,Samsung',
                'in:excellent,good,poor,replace',
            ],
            'max_price' => [
                'required',
                'integer',
                'min:1',
            ],
            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ], [
            'condition_grade.required' =>
                'وضعیت ظاهری رو انتخاب کن؛ برای تشخیص قیمت لازمش دارم.',
            'registration_status.required' =>
                'رجیستری باید مشخص باشه؛ ثبت‌شده یا نشده.',
            'battery_health.required_unless' =>
                'سلامت باتری رو هم وارد کن؛ بدون اون قیمت دقیق درنمیاد.',
            'battery_health.integer' =>
                'سلامت باتری باید به‌صورت عدد وارد بشه.',
            'battery_health.min' =>
                'سلامت باتری نمی‌تونه کمتر از صفر باشه.',
            'battery_health.max' =>
                'سلامت باتری نمی‌تونه بیشتر از ۱۰۰ باشه.',
            'battery_condition.required_if' =>
                'وضعیت باتری رو انتخاب کن؛ برای سامسونگ این بخش لازمه.',
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
            return $this->validationError(
                'brand',
                'برند انتخاب‌شده معتبر نیست.'
            );
        }

        $model = DB::table('device_models')
            ->where('brand_id', $brand->id)
            ->where('name', $validated['model'])
            ->where('is_active', true)
            ->first(['id']);

        if (! $model) {
            return $this->validationError(
                'model',
                'مدل انتخاب‌شده برای این برند معتبر نیست.'
            );
        }

        $storage = DB::table('storage_options')
            ->where('name', $validated['storage'])
            ->where('is_active', true)
            ->first(['id']);

        $storageIsAttached = $storage
            && DB::table('device_model_storage_option')
                ->where('device_model_id', $model->id)
                ->where('storage_option_id', $storage->id)
                ->exists();

        if (! $storageIsAttached) {
            return $this->validationError(
                'storage',
                'حافظه انتخاب‌شده برای این مدل معتبر نیست.'
            );
        }

        if (! empty($validated['color'])) {
            $color = DB::table('color_options')
                ->where('name', $validated['color'])
                ->where('is_active', true)
                ->first(['id']);

            $colorIsAttached = $color
                && DB::table('device_model_color_option')
                    ->where('device_model_id', $model->id)
                    ->where('color_option_id', $color->id)
                    ->exists();

            if (! $colorIsAttached) {
                return $this->validationError(
                    'color',
                    'رنگ انتخاب‌شده برای این مدل معتبر نیست.'
                );
            }
        }

        if ($validated['brand'] === 'Samsung') {
            $validated['battery_health'] = null;
        } else {
            $validated['battery_condition'] = null;
        }

        $usdSnapshot = $currencyRateService->snapshotForDate(
            'USD',
            now()->toDateString()
        );

        $currentUsdRate = (int) ($usdSnapshot['rate'] ?? 0);

        $marketSignal = $wantedPriceGuardService->evaluate(
            $validated['brand'],
            $validated['model'],
            $validated['storage'],
            (int) $validated['max_price'],
            $currentUsdRate,
            $validated['condition_grade'] ?? null,
            $validated['battery_health'] ?? null,
            $validated['battery_condition'] ?? null,
            $validated['registration_status'] ?? null,
            $validated['color'] ?? null
        );

        if (! $marketSignal['accepted']) {
            $candidatePrice = (int) $validated['max_price'];

            $demandReference = (int) (
                $marketSignal['demand_reference_price']
                ?? $marketSignal['reference_price']
                ?? 0
            );

            $saleReference = (int) (
                $marketSignal['sale_reference_price']
                ?? 0
            );

            $isLow = in_array(
                $marketSignal['reason'] ?? null,
                [
                    'gross_price_outlier',
                    'corroborated_gross_low_outlier',
                ],
                true
            ) && (
                ! isset($marketSignal['lower_bound'])
                || $candidatePrice < (int) $marketSignal['lower_bound']
            );

            $feedback = $this->buildMarketPriceFeedback(
                $validated['model'],
                $validated['storage'],
                $candidatePrice,
                $demandReference,
                $saleReference,
                $isLow,
                $marketSignal['reference_scope'] ?? 'exact_storage'
            );

            return response()->json([
                'message' => $feedback['headline'],
                'errors' => [
                    'max_price' => [$feedback['short']],
                ],
                'market_feedback' => $feedback,
            ], 422);
        }

        $id = DB::table('wanted_device_requests')->insertGetId([
            'requester_name' => trim($validated['requester_name']),
            'requester_mobile' => $validated['requester_mobile'],
            'brand' => $validated['brand'],
            'model' => $validated['model'],
            'storage' => $validated['storage'],
            'color' => $validated['color'] ?? null,
            'condition_grade' => $validated['condition_grade'] ?? null,
            'registration_status' => $validated['registration_status'] ?? null,
            'battery_health' => $validated['battery_health'] ?? null,
            'battery_condition' => $validated['battery_condition'] ?? null,
            'max_price' => (int) $validated['max_price'],
            'usd_rate' => $usdSnapshot['rate'] ?? null,
            'usd_rate_date' => $usdSnapshot['rate_date'] ?? null,
            'usd_rate_source' => $usdSnapshot['source'] ?? null,
            'description' => isset($validated['description'])
                ? trim($validated['description'])
                : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'درخواست خرید با موفقیت ثبت شد.',
            'request' => [
                'id' => $id,
                'brand' => $validated['brand'],
                'model' => $validated['model'],
                'storage' => $validated['storage'],
                'color' => $validated['color'] ?? null,
                'max_price' => (int) $validated['max_price'],
            ],
        ], 201);
    }

    private function buildMarketPriceFeedback(
        string $model,
        string $storage,
        int $candidatePrice,
        int $demandReference,
        int $saleReference,
        bool $isLow,
        string $referenceScope = 'exact_storage'
    ): array {
        $money = fn (int $value): string =>
            number_format($value).' تومان';

        if ($isLow) {
            $headline = 'همکار جان، خودت هم می‌دونی این قیمت یکم زیادی خوش‌بینانه‌ست 😄';

            $parts = [];

            if ($demandReference > 0) {
                $parts[] = $referenceScope === 'model_other_storage'
                    ? 'حتی حافظه‌های دیگه همین مدل تو بازار حوالی '
                        .$money($demandReference)
                        .' می‌چرخن'
                    : 'تقاضای بازار برای این مشخصات حوالی '
                        .$money($demandReference)
                        .' می‌چرخه';
            }

            if ($saleReference > 0) {
                $parts[] = 'فروش مشابه هم نزدیک '
                    .$money($saleReference)
                    .' ثبت شده';
            }

            $evidence = $parts !== []
                ? implode(' و ', $parts).'.'
                : 'داده‌های فعلی بازار با این عدد فاصله زیادی دارند.';

            return [
                'type' => 'gross_low',
                'headline' => $headline,
                'short' => 'این عدد خیلی پایین‌تر از چیزی است که بازار برای این گوشی نشان می‌دهد.',
                'body' => $evidence
                    .' سقفی که زدی '
                    .$money($candidatePrice)
                    .' است؛ با این عدد برای '
                    .$model.' '.$storage
                    .' بعیده به نتیجه برسی. پس این درخواست رو ثبت نمی‌کنم؛ قیمت رو یک بار دیگه واقعی‌تر بزن، بازار حواسش هست 😉',
                'candidate_price' => $candidatePrice,
                'demand_reference_price' =>
                    $demandReference > 0 ? $demandReference : null,
                'sale_reference_price' =>
                    $saleReference > 0 ? $saleReference : null,
            ];
        }

        $headline = 'همکار جان، این یکی دیگه زیادی دست‌ودلبازانه‌ست 😄';

        $parts = [];

        if ($demandReference > 0) {
            $parts[] = $referenceScope === 'model_other_storage'
                ? 'حتی حافظه‌های دیگه همین مدل تو بازار حوالی '
                    .$money($demandReference)
                    .' می‌چرخن'
                : 'همکارها فعلاً حوالی '
                    .$money($demandReference)
                    .' برای این مدل خرید می‌زنند';
        }

        if ($saleReference > 0) {
            $parts[] = 'فروش مشابه هم نزدیک '
                .$money($saleReference)
                .' بوده';
        }

        $evidence = $parts !== []
            ? implode(' و ', $parts).'.'
            : 'داده‌های فعلی بازار با این عدد فاصله زیادی دارند.';

        return [
            'type' => 'gross_high',
            'headline' => $headline,
            'short' => 'این سقف خرید خیلی بالاتر از الگوی فعلی بازار است.',
            'body' => $evidence
                .' ولی شما '
                .$money($candidatePrice)
                .' وارد کردی. لازم نیست برای '
                .$model.' '.$storage
                .' این‌قدر روی میز پول بذاری. پس این درخواست رو ثبت نمی‌کنم؛ یه عدد منطقی‌تر بزن که هم معامله بشه، هم پولت تو جیبت بمونه 😉',
            'candidate_price' => $candidatePrice,
            'demand_reference_price' =>
                $demandReference > 0 ? $demandReference : null,
            'sale_reference_price' =>
                $saleReference > 0 ? $saleReference : null,
        ];
    }

    private function validationError(
        string $field,
        string $message
    ): JsonResponse {
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
            '۰' => '0',
            '۱' => '1',
            '۲' => '2',
            '۳' => '3',
            '۴' => '4',
            '۵' => '5',
            '۶' => '6',
            '۷' => '7',
            '۸' => '8',
            '۹' => '9',
            '٠' => '0',
            '١' => '1',
            '٢' => '2',
            '٣' => '3',
            '٤' => '4',
            '٥' => '5',
            '٦' => '6',
            '٧' => '7',
            '٨' => '8',
            '٩' => '9',
        ]);

        return preg_replace('/[\s\-()]/u', '', $normalized);
    }
}
