<?php

namespace App\Http\Controllers;

use App\Support\VehicleCatalogPayload;
use Inertia\Inertia;
use Inertia\Response;

class PublicContractController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Features/Contracts/Index', [
            'catalog' => VehicleCatalogPayload::make(),
        ]);
    }
}
