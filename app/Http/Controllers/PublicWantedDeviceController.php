<?php

namespace App\Http\Controllers;

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

    public function store(Request $request): JsonResponse
    {
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
                'nullable',
                'in:A+,A,B,C',
            ],
            'registration_status' => [
                'nullable',
                'in:registered,unregistered',
            ],
            'battery_health' => [
                'nullable',
                'integer',
                'min:0',
                'max:100',
            ],
            'battery_condition' => [
                'nullable',
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
