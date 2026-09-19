<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReliefRequestResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReliefRequestController extends Controller
{
    /**
     * Submits a relief request for the authenticated user, using their
     * already-registered city/street/building rather than taking new input.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->city_id || ! $user->street_name || ! $user->building_number) {
            throw ValidationException::withMessages([
                'profile' => __('messages.relief.incomplete_profile'),
            ]);
        }

        $reliefRequest = $user->reliefRequests()->create([
            'city_id' => $user->city_id,
            'street' => $user->street_name,
            'building_number' => $user->building_number,
        ]);

        return response()->json([
            'message' => __('messages.relief.submitted'),
            'relief_request' => new ReliefRequestResource($reliefRequest->load(['user', 'city'])),
        ], 201);
    }
}
