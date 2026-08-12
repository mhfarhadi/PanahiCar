<?php

namespace App\Http\Controllers;

use App\Models\Device;
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
                'storage' => $device->storage,
                'color' => $device->color,
                'part_number' => $device->part_number,
                'manufacturing_country' => $device->manufacturing_country,
                'sim_type' => $device->sim_type,
                'battery_health' => $device->battery_health,
                'battery_condition' => $device->battery_condition,
                'condition_grade' => $device->condition_grade,
                'imei' => $device->imei,
                'registration_status' => $device->registration_status,
                'description' => $device->description,

                'purchase_id' => $purchase?->purchase_id,
                'purchase_price' => $purchase?->purchase_price,
                'purchase_date' => $purchase?->purchase_date,
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
        ]);
    }
}
