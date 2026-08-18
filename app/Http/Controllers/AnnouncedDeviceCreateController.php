<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceImage;
use App\Services\EntityNoteService;
use App\Support\VehicleOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AnnouncedDeviceCreateController extends Controller
{
    public function create(Request $request): Response
    {
        return Inertia::render('AnnouncedDevices/Create', [
            'catalog' => $this->catalog(),
            'contacts' => DB::table('contacts')
                ->whereNull('archived_at')
                ->where('contact_type', 'colleague')
                ->orderBy('name')
                ->get(['id', 'name', 'mobile']),
            'createdContactId' => request()->integer('created_contact') ?: null,
            'optionLabels' => $this->optionLabels(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            ...$this->vehicleRules(),
            'description' => ['nullable', 'string'],
            'announcer_id' => ['required', 'integer', 'exists:contacts,id'],
            'announced_price' => ['nullable', 'integer', 'min:0'],
            'announced_at' => ['required', 'date'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'max:5120'],
        ]);

        $validated['mileage'] = (int) $this->normalizeDigits((string) $validated['mileage']);

        DB::transaction(function () use ($request, $validated) {
            $device = Device::create([
                ...collect($validated)->only([
                    'brand', 'model', 'model_year', 'mileage', 'color',
                    'transmission', 'fuel_type', 'insurance_months', 'body_condition', 'vin', 'description',
                ])->all(),
                'status' => 'announced',
                'announced_by_id' => $validated['announcer_id'],
                'announced_price' => $validated['announced_price'] ?? null,
                'announced_at' => $validated['announced_at'],
                'created_by' => $request->user()->id,
            ]);

            EntityNoteService::add(
                'device',
                $device->id,
                $validated['description'] ?? null,
                $request->user()->id
            );

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
            ->route('announced-devices.index')
            ->with('success', 'خودرو اعلامی با موفقیت ثبت شد.');
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

    private function vehicleRules(): array
    {
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
            'vin' => ['nullable', 'string', 'max:30', 'unique:devices,vin'],
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
