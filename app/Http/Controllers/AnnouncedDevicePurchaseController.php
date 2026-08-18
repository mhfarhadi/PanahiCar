<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Purchase;
use App\Services\CurrencyRateService;
use App\Services\EntityNoteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AnnouncedDevicePurchaseController extends Controller
{
    public function create(Device $device): Response
    {
        abort_unless($device->status === 'announced', 404);

        $announcer = DB::table('contacts')
            ->where('id', $device->announced_by_id)
            ->first();

        return Inertia::render('AnnouncedDevices/Purchase', [
            'device' => [
                'id' => $device->id,
                'brand' => $device->brand,
                'model' => $device->model,
                'model_year' => $device->model_year,
                'mileage' => $device->mileage,
                'color' => $device->color,
                'transmission' => $device->transmission,
                'vin' => $device->vin,
                'announced_price' => $device->announced_price,
                'announcer_name' => $announcer?->name,
                'announcer_mobile' => $announcer?->mobile,
            ],
            'optionLabels' => [
                'transmissions' => \App\Support\VehicleOptions::transmissions(),
            ],
        ]);
    }

    public function store(Request $request, Device $device, CurrencyRateService $currencyRateService): RedirectResponse
    {
        abort_unless($device->status === 'announced', 404);

        $validated = $request->validate([
            'purchase_price' => ['required', 'integer', 'min:0'],
            'purchase_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $currencySnapshot = $currencyRateService->snapshotForDate(
            'USD',
            $validated['purchase_date']
        );

        DB::transaction(function () use ($request, $device, $validated, $currencySnapshot) {
            $purchase = new Purchase();
            $purchase->device_id = $device->id;
            $purchase->seller_id = $device->announced_by_id;
            $purchase->purchase_price = $validated['purchase_price'];
            $purchase->purchase_date = $validated['purchase_date'];
            $purchase->usd_rate = $currencySnapshot['rate'] ?? null;
            $purchase->usd_rate_date = $currencySnapshot['rate_date'] ?? null;
            $purchase->usd_rate_source = $currencySnapshot['source'] ?? null;
            $purchase->notes = $validated['notes'] ?? null;
            $purchase->created_by = $request->user()->id;
            $purchase->save();

            EntityNoteService::add(
                'purchase',
                $purchase->id,
                $validated['notes'] ?? null,
                $request->user()->id
            );

            $device->status = 'in_stock';
            $device->save();
        });

        return redirect()
            ->route('devices.index')
            ->with('success', 'خودرو با موفقیت خریداری و به موجودی اضافه شد.');
    }
}
