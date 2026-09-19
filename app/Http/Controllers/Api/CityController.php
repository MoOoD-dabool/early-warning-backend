<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CityResource;
use App\Models\City;
use Illuminate\Http\JsonResponse;

class CityController extends Controller
{
    /**
     * Public list, used to populate the city dropdown on the register screen.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'cities' => CityResource::collection(City::query()->orderBy('name_ar')->get()),
        ]);
    }
}