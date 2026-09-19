<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReliefRequestResource;
use App\Models\ReliefRequest;
use Illuminate\Http\JsonResponse;

class AdminReliefRequestController extends Controller
{
    public function index(): JsonResponse
    {
        $requests = ReliefRequest::query()
            ->with(['user', 'city'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'relief_requests' => ReliefRequestResource::collection($requests),
            'meta' => [
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'total' => $requests->total(),
            ],
        ]);
    }

    public function show(ReliefRequest $reliefRequest): JsonResponse
    {
        return response()->json([
            'relief_request' => new ReliefRequestResource($reliefRequest->load(['user', 'city'])),
        ]);
    }
}
