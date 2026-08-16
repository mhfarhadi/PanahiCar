<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PublicWantedMarketController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('q', ''));
        $brand = trim((string) $request->query('brand', ''));
        $origin = trim((string) $request->query('origin', ''));

        $query = DB::table('wanted_device_requests');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('storage', 'like', "%{$search}%")
                    ->orWhere('color', 'like', "%{$search}%")
                    ->orWhere('requester_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($brand !== '') {
            $query->where('brand', $brand);
        }

        if ($origin === 'organic') {
            $query->where('origin', 'organic');
        } elseif ($origin === 'bootstrap_market') {
            $query->where('origin', 'bootstrap_market');
        }

        $requests = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(18)
            ->withQueryString()
            ->through(function ($request) {
                $isOrganic = $request->origin === 'organic';

                return [
                    'id' => $request->id,
                    'requester_name' => $isOrganic
                        ? $request->requester_name
                        : 'نمونه‌ی بازار',
                    'brand' => $request->brand,
                    'model' => $request->model,
                    'storage' => $request->storage,
                    'color' => $request->color,
                    'condition_grade' => $request->condition_grade,
                    'registration_status' => $request->registration_status,
                    'battery_health' => $request->battery_health,
                    'battery_condition' => $request->battery_condition,
                    'max_price' => (int) $request->max_price,
                    'description' => $isOrganic
                        ? $request->description
                        : null,
                    'origin' => $request->origin,
                    'market_reference_source' => $isOrganic
                        ? null
                        : $request->market_reference_source,
                    'is_provisional' => ! $isOrganic,
                    'can_reveal_contact' => $isOrganic,
                    'created_at' => $request->created_at,
                ];
            });

        $total = DB::table('wanted_device_requests')->count();
        $organic = DB::table('wanted_device_requests')
            ->where('origin', 'organic')
            ->count();
        $bootstrap = DB::table('wanted_device_requests')
            ->where('origin', 'bootstrap_market')
            ->count();
        $recent = DB::table('wanted_device_requests')
            ->where('created_at', '>=', now()->subDay())
            ->count();

        $brands = DB::table('wanted_device_requests')
            ->select('brand')
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand')
            ->values();

        return Inertia::render('Features/WantedMarket/Index', [
            'requests' => $requests,
            'filters' => [
                'q' => $search,
                'brand' => $brand,
                'origin' => in_array(
                    $origin,
                    ['organic', 'bootstrap_market'],
                    true
                ) ? $origin : '',
            ],
            'brands' => $brands,
            'summary' => [
                'total' => $total,
                'organic' => $organic,
                'bootstrap' => $bootstrap,
                'recent' => $recent,
            ],
        ]);
    }

    public function contact(int $requestId): JsonResponse
    {
        $request = DB::table('wanted_device_requests')
            ->where('id', $requestId)
            ->first([
                'id',
                'requester_name',
                'requester_mobile',
                'origin',
            ]);

        if (! $request) {
            abort(404);
        }

        if ($request->origin !== 'organic') {
            return response()->json([
                'message' => 'این مورد نمونه‌ی بازار است و شماره تماس واقعی ندارد.',
            ], 422);
        }

        return response()->json([
            'contact' => [
                'requester_name' => $request->requester_name,
                'mobile' => $request->requester_mobile,
            ],
        ]);
    }
}
