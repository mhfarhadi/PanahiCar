<?php

namespace App\Http\Controllers;

use App\Support\VehicleOptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class PublicWantedMarketController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('q', ''));
        $brand = trim((string) $request->query('brand', ''));
        $hasOrigin = Schema::hasColumn('wanted_device_requests', 'origin');

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

        $requests = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(18)
            ->withQueryString()
            ->through(function ($row) use ($hasOrigin) {
                $origin = $hasOrigin ? ($row->origin ?? 'organic') : 'organic';
                $isOrganic = $origin === 'organic';

                return [
                    'id' => $row->id,
                    'requester_name' => $isOrganic ? $row->requester_name : 'نمونه‌ی بازار',
                    'brand' => $row->brand,
                    'model' => $row->model,
                    'model_year' => $row->storage,
                    'color' => $row->color,
                    'body_condition' => $row->condition_grade,
                    'body_condition_label' => VehicleOptions::label(
                        VehicleOptions::bodyConditions(),
                        $row->condition_grade
                    ),
                    'max_price' => (int) $row->max_price,
                    'description' => $isOrganic ? $row->description : null,
                    'origin' => $origin,
                    'can_reveal_contact' => $isOrganic,
                    'created_at' => $row->created_at,
                ];
            });

        $total = DB::table('wanted_device_requests')->count();
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
            ],
            'brands' => $brands,
            'summary' => [
                'total' => $total,
                'recent' => $recent,
            ],
        ]);
    }

    public function contact(int $requestId): JsonResponse
    {
        $hasOrigin = Schema::hasColumn('wanted_device_requests', 'origin');

        $columns = ['id', 'requester_name', 'requester_mobile'];

        if ($hasOrigin) {
            $columns[] = 'origin';
        }

        $row = DB::table('wanted_device_requests')
            ->where('id', $requestId)
            ->first($columns);

        if (! $row) {
            abort(404);
        }

        $origin = $hasOrigin ? ($row->origin ?? 'organic') : 'organic';

        if ($origin !== 'organic') {
            return response()->json([
                'message' => 'این مورد نمونه‌ی بازار است و شماره تماس واقعی ندارد.',
            ], 422);
        }

        return response()->json([
            'contact' => [
                'requester_name' => $row->requester_name,
                'mobile' => $row->requester_mobile,
            ],
        ]);
    }
}
