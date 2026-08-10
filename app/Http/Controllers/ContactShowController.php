<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ContactShowController extends Controller
{
    public function show(Contact $contact): Response
    {
        $announcedDevices = DB::table('devices')
            ->where('announced_by_id', $contact->id)
            ->orderByDesc('announced_at')
            ->orderByDesc('id')
            ->get([
                'id',
                'brand',
                'model',
                'storage',
                'color',
                'imei',
                'status',
                'announced_price',
                'announced_at',
            ]);

        $soldToShop = DB::table('purchases as p')
            ->join('devices as d', 'd.id', '=', 'p.device_id')
            ->where('p.seller_id', $contact->id)
            ->orderByDesc('p.purchase_date')
            ->orderByDesc('p.id')
            ->get([
                'p.id as purchase_id',
                'p.device_id',
                'p.purchase_price',
                'p.purchase_date',
                'p.notes',
                'd.brand',
                'd.model',
                'd.storage',
                'd.color',
                'd.imei',
                'd.status',
            ]);

        return Inertia::render('Contacts/Show', [
            'contact' => [
                'id' => $contact->id,
                'name' => $contact->name,
                'mobile' => $contact->mobile,
                'phone' => $contact->phone,
                'description' => $contact->description,
                'contact_type' => $contact->contact_type,
                'created_at' => $contact->created_at,

                'stats' => [
                    'announced_count' => $announcedDevices->count(),
                    'sold_to_shop_count' => $soldToShop->count(),
                ],
            ],

            'announcedDevices' => $announcedDevices,
            'soldToShop' => $soldToShop,
        ]);
    }
}
