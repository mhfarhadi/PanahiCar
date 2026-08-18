<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceImage;
use App\Models\Purchase;
use App\Services\CurrencyRateService;
use App\Services\EntityNoteService;
use App\Support\VehicleOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DeviceController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $this->normalizeDigits(trim((string) $request->query('search')));

        $devices = DB::table('devices as d')
            ->leftJoin('purchases as p', 'p.device_id', '=', 'd.id')
            ->leftJoin('contacts as c', 'c.id', '=', 'p.seller_id')
            ->where('d.status', 'in_stock')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('d.brand', 'like', "%{$search}%")
                        ->orWhere('d.model', 'like', "%{$search}%")
                        ->orWhere('d.color', 'like', "%{$search}%")
                        ->orWhere('d.vin', 'like', "%{$search}%")
                        ->orWhere('c.name', 'like', "%{$search}%")
                        ->orWhere('c.mobile', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('d.id')
            ->select([
                'd.id',
                'd.brand',
                'd.model',
                'd.model_year',
                'd.mileage',
                'd.color',
                'd.transmission',
                'd.fuel_type',
                'd.body_condition',
                'd.vin',
                'd.description',
                'p.purchase_price',
                'p.purchase_date',
                'c.name as seller_name',
            ])
            ->get()
            ->map(function ($device) {
                $device->suggested_sale_price = $device->purchase_price
                    ? (int) round($device->purchase_price * 1.10)
                    : null;

                $device->cover_image = DB::table('device_images')
                    ->where('device_id', $device->id)
                    ->orderByDesc('is_cover')
                    ->orderBy('sort_order')
                    ->value('image_path');

                return $device;
            });

        return Inertia::render('Devices/Index', [
            'devices' => $devices,
            'filters' => [
                'search' => $search,
                'mode' => request()->query('mode'),
            ],
            'optionLabels' => $this->optionLabels(),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Devices/Create', [
            'catalog' => $this->catalog(),
            'contacts' => DB::table('contacts')
                ->whereNull('archived_at')
                ->orderBy('name')
                ->get(['id', 'name', 'mobile']),
            'createdContactId' => request()->integer('created_contact') ?: null,
            'optionLabels' => $this->optionLabels(),
        ]);
    }

    public function edit(Device $device): Response
    {
        abort_unless($device->status === 'in_stock', 404);

        return Inertia::render('Devices/Edit', [
            'device' => $device->only([
                'id', 'brand', 'model', 'model_year', 'mileage', 'color',
                'transmission', 'fuel_type', 'insurance_months', 'body_condition', 'vin',
            ]),
            'catalog' => $this->catalog(),
            'optionLabels' => $this->optionLabels(),
        ]);
    }

    public function update(Request $request, Device $device): RedirectResponse
    {
        abort_unless($device->status === 'in_stock', 404);

        $validated = $this->validateVehicle($request, $device->id);

        $device->fill($validated);
        $device->save();

        return redirect()
            ->route('devices.show', $device)
            ->with('success', 'مشخصات خودرو با موفقیت ویرایش شد.');
    }

    public function store(Request $request, CurrencyRateService $currencyRateService): RedirectResponse
    {
        $validated = $request->validate([
            ...$this->vehicleRules(),
            'description' => ['nullable', 'string'],
            'seller_id' => ['required', 'integer', 'exists:contacts,id'],
            'purchase_price' => ['required', 'integer', 'min:0'],
            'purchase_date' => ['required', 'date'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'max:5120'],
        ]);

        $validated['mileage'] = (int) $this->normalizeDigits((string) $validated['mileage']);

        $currencySnapshot = $currencyRateService->snapshotForDate(
            'USD',
            $validated['purchase_date']
        );

        DB::transaction(function () use ($request, $validated, $currencySnapshot) {
            $device = Device::create([
                ...collect($validated)->only([
                    'brand', 'model', 'model_year', 'mileage', 'color',
                    'transmission', 'fuel_type', 'insurance_months', 'body_condition', 'vin', 'description',
                ])->all(),
                'status' => 'in_stock',
                'created_by' => $request->user()->id,
            ]);

            EntityNoteService::add(
                'device',
                $device->id,
                $validated['description'] ?? null,
                $request->user()->id
            );

            Purchase::create([
                'device_id' => $device->id,
                'seller_id' => $validated['seller_id'],
                'purchase_price' => $validated['purchase_price'],
                'purchase_date' => $validated['purchase_date'],
                'usd_rate' => $currencySnapshot['rate'] ?? null,
                'usd_rate_date' => $currencySnapshot['rate_date'] ?? null,
                'usd_rate_source' => $currencySnapshot['source'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            foreach ($request->file('images', []) as $index => $image) {
                DeviceImage::create([
                    'device_id' => $device->id,
                    'image_path' => $image->store('devices', 'public'),
                    'is_cover' => $index === 0,
                    'sort_order' => $index,
                ]);
            }
        });

        return redirect()
            ->route('dashboard')
            ->with('success', 'خودرو با موفقیت ثبت شد.');
    }

    private function catalog(): array
    {
        return [
            'brands' => DB::table('brands')->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'models' => DB::table('device_models')->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'colors' => DB::table('color_options')->where('is_active', true)->orderBy('sort_order')->get(),
            'modelColors' => DB::table('device_model_color_option')->get(),
        ];
    }

    private function optionLabels(): array
    {
        return [
            'transmissions' => VehicleOptions::transmissions(),
            'fuelTypes' => VehicleOptions::fuelTypes(),
            'bodyConditions' => VehicleOptions::bodyConditions(),
        ];
    }

    private function validateVehicle(Request $request, ?int $deviceId = null): array
    {
        $validated = $request->validate($this->vehicleRules($deviceId));
        $validated['mileage'] = (int) $this->normalizeDigits((string) $validated['mileage']);

        return $validated;
    }

    private function vehicleRules(?int $deviceId = null): array
    {
        $vinRule = ['nullable', 'string', 'max:30'];

        if ($deviceId) {
            $vinRule[] = 'unique:devices,vin,'.$deviceId;
        } else {
            $vinRule[] = 'unique:devices,vin';
        }

        return [
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:150'],
            'model_year' => ['required', 'integer', 'min:1350', 'max:1450'],
            'mileage' => ['required', 'integer', 'min:0'],
            'color' => ['nullable', 'string', 'max:100'],
            'transmission' => ['required', 'in:manual,automatic'],
            'fuel_type' => ['required', 'in:petrol,dual_fuel,diesel,hybrid'],
            'insurance_months' => ['nullable', 'integer', 'min:0', 'max:24'],
            'body_condition' => ['required', 'in:pristine,one_spot,two_spot,multi_spot,paintless_dent,fender_paint,partial_paint,full_paint'],
            'vin' => $vinRule,
        ];
    }

    private function normalizeDigits(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return strtr($value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
    }
}
