<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $inventoryCount = Device::where('status', 'in_stock')->count();

        return Inertia::render('Dashboard', [
            'inventoryCount' => $inventoryCount,
        ]);
    }
}
