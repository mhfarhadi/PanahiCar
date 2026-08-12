<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Services\ContactManagementService;
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

        $purchasedFromShop = DB::table('sales as s')
            ->join('devices as d', 'd.id', '=', 's.device_id')
            ->where('s.buyer_id', $contact->id)
            ->orderByDesc('s.sale_date')
            ->orderByDesc('s.id')
            ->get([
                's.id as sale_id',
                's.device_id',
                's.sale_type',
                's.sale_price',
                's.down_payment',
                's.monthly_profit_rate',
                's.installment_profit',
                's.contract_total',
                's.sale_date',
                's.notes',
                'd.brand',
                'd.model',
                'd.storage',
                'd.color',
                'd.imei',
                'd.status',
            ]);

        $notes = DB::table('entity_notes as n')
            ->leftJoin('users as u', 'u.id', '=', 'n.created_by')
            ->where('n.entity_type', 'contact')
            ->where('n.entity_id', $contact->id)
            ->orderByDesc('n.created_at')
            ->orderByDesc('n.id')
            ->get([
                'n.id',
                'n.body',
                'n.created_at',
                'u.name as author_name',
            ]);

        $paymentStats = DB::table('installments as i')
            ->join('sales as s', 's.id', '=', 'i.sale_id')
            ->where('s.buyer_id', $contact->id)
            ->where('i.status', 'paid')
            ->selectRaw('COUNT(*) as cleared_count')
            ->selectRaw('SUM(CASE WHEN i.paid_at > i.due_date THEN 1 ELSE 0 END) as delayed_count')
            ->selectRaw('COALESCE(ROUND(AVG(CASE WHEN i.paid_at > i.due_date THEN DATEDIFF(i.paid_at, i.due_date) END)), 0) as average_delay_days')
            ->first();

        return Inertia::render('Contacts/Show', [
            'contact' => [
                'id' => $contact->id,
                'name' => $contact->name,
                'mobile' => $contact->mobile,
                'phone' => $contact->phone,
                'description' => $contact->description,
                'avatar_path' => $contact->avatar_path,
                'contact_type' => $contact->contact_type,
                'archived_at' => $contact->archived_at,
                'can_manage' => ContactManagementService::canManage(
                    request()->user(),
                    $contact
                ),
                'has_history' => ContactManagementService::hasHistory($contact),
                'created_at' => $contact->created_at,

                'stats' => [
                    'announced_count' => $announcedDevices->count(),
                    'sold_to_shop_count' => $soldToShop->count(),
                    'purchased_from_shop_count' => $purchasedFromShop->count(),
                ],

                'payment_stats' => [
                    'cleared_count' => (int) ($paymentStats->cleared_count ?? 0),
                    'delayed_count' => (int) ($paymentStats->delayed_count ?? 0),
                    'average_delay_days' => (int) ($paymentStats->average_delay_days ?? 0),
                ],
            ],

            'announcedDevices' => $announcedDevices,
            'soldToShop' => $soldToShop,
            'purchasedFromShop' => $purchasedFromShop,
            'notes' => $notes,
        ]);
    }
}
