<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DisasterTypeResource;
use App\Models\DisasterType;
use Illuminate\Http\JsonResponse;

class DisasterTypeController extends Controller
{
    /**
     * Feeds the "Precautions" screens (before/during/after instructions).
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'disaster_types' => DisasterTypeResource::collection(DisasterType::query()->orderBy('name_ar')->get()),
        ]);
    }

    public function show(DisasterType $disasterType): JsonResponse
    {
        return response()->json([
            'disaster_type' => new DisasterTypeResource($disasterType),
        ]);
    }
}