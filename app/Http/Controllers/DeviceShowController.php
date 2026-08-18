<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Support\VehicleOptions;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DeviceShowController extends Controller
{
    public function show(Device $device): Response
    {
        abort_unless($device->status === 'in_stock', 404);

        $purchase = DB::table('purchases as p')
            ->leftJoin('contacts as c', 'c.id', '=', 'p.seller_id')
            ->where('p.device_id', $device->id)
            ->orderByDesc('p.id')
            ->select([
                'p.id as purchase_id',
                'p.purchase_price',
                'p.purchase_date',
                'p.usd_rate',
                'p.usd_rate_date',
                'p.usd_rate_source',
                'p.notes',
                'c.name as seller_name',
                'c.mobile as seller_mobile',
            ])
            ->first();

        $deviceNotes = DB::table('entity_notes as n')
            ->leftJoin('users as u', 'u.id', '=', 'n.created_by')
            ->where('n.entity_type', 'device')
            ->where('n.entity_id', $device->id)
            ->orderByDesc('n.created_at')
            ->orderByDesc('n.id')
            ->get([
                'n.id',
                'n.body',
                'n.created_at',
                'u.name as author_name',
            ]);

        $purchaseNotes = collect();

        if ($purchase?->purchase_id) {
            $purchaseNotes = DB::table('entity_notes as n')
                ->leftJoin('users as u', 'u.id', '=', 'n.created_by')
                ->where('n.entity_type', 'purchase')
                ->where('n.entity_id', $purchase->purchase_id)
                ->orderByDesc('n.created_at')
                ->orderByDesc('n.id')
                ->get([
                    'n.id',
                    'n.body',
                    'n.created_at',
                    'u.name as author_name',
                ]);
        }

        $images = DB::table('device_images')
            ->where('device_id', $device->id)
            ->orderByDesc('is_cover')
            ->orderBy('sort_order')
            ->get([
                'id',
                'image_path',
                'is_cover',
                'sort_order',
            ]);

        return Inertia::render('Devices/Show', [
            'device' => [
                'id' => $device->id,
                'brand' => $device->brand,
                'model' => $device->model,
                'model_year' => $device->model_year,
                'mileage' => $device->mileage,
                'color' => $device->color,
                'transmission' => $device->transmission,
                'fuel_type' => $device->fuel_type,
                'insurance_months' => $device->insurance_months,
                'body_condition' => $device->body_condition,
                'vin' => $device->vin,
                'description' => $device->description,

                'purchase_id' => $purchase?->purchase_id,
                'purchase_price' => $purchase?->purchase_price,
                'purchase_date' => $purchase?->purchase_date,
                'purchase_usd_rate' => $purchase?->usd_rate,
                'purchase_usd_rate_date' => $purchase?->usd_rate_date,
                'purchase_usd_rate_source' => $purchase?->usd_rate_source,
                'purchase_notes' => $purchase?->notes,
                'seller_name' => $purchase?->seller_name,
                'seller_mobile' => $purchase?->seller_mobile,

                'suggested_sale_price' => $purchase?->purchase_price
                    ? (int) round($purchase->purchase_price * 1.10)
                    : null,

                'images' => $images,
            ],
            'deviceNotes' => $deviceNotes,
            'purchaseNotes' => $purchaseNotes,
            'optionLabels' => [
                'transmissions' => VehicleOptions::transmissions(),
                'fuelTypes' => VehicleOptions::fuelTypes(),
                'bodyConditions' => VehicleOptions::bodyConditions(),
            ],
        ]);
    }
}
