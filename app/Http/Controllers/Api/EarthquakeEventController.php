<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EarthquakeEventResource;
use App\Models\EarthquakeEvent;
use Illuminate\Http\JsonResponse;

class EarthquakeEventController extends Controller
{
    public function index(): JsonResponse
    {
        $events = EarthquakeEvent::query()
            ->with('city')
            ->orderByDesc('occurred_at')
            ->paginate(20);

        return response()->json([
            'earthquake_events' => EarthquakeEventResource::collection($events),
            'meta' => [
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
                'total' => $events->total(),
            ],
        ]);
    }

    public function show(EarthquakeEvent $earthquakeEvent): JsonResponse
    {
        return response()->json([
            'earthquake_event' => new EarthquakeEventResource($earthquakeEvent->load('city')),
        ]);
    }
}
