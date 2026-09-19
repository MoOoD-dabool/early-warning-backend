<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEarthquakeEventRequest;
use App\Http\Resources\EarthquakeEventResource;
use App\Models\Alert;
use App\Models\EarthquakeEvent;
use App\Services\AlertDispatchService;
use Illuminate\Http\JsonResponse;

class AdminEarthquakeEventController extends Controller
{
    public function __construct(private readonly AlertDispatchService $dispatchService)
    {
    }

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

    /**
     * Logs an earthquake event, optionally creating (and dispatching) an
     * alert for it in the same request when the city is known.
     */
    public function store(StoreEarthquakeEventRequest $request): JsonResponse
    {
        $data = $request->validated();
        $autoCreateAlert = (bool) ($data['auto_create_alert'] ?? false);
        $disasterTypeId = $data['disaster_type_id'] ?? null;
        unset($data['auto_create_alert'], $data['disaster_type_id']);

        $data['processed'] = false;

        $event = EarthquakeEvent::query()->create($data);

        $recipients = 0;

        if ($autoCreateAlert && $event->city_id) {
            $alert = Alert::query()->create([
                'disaster_type_id' => $disasterTypeId,
                'city_id' => $event->city_id,
                'earthquake_event_id' => $event->id,
                'severity' => $this->severityFromMagnitude((float) $event->magnitude),
                'message_ar' => "تم رصد زلزال بقوة {$event->magnitude} قرب {$event->location_name}.",
                'message_en' => "Earthquake of magnitude {$event->magnitude} detected near {$event->location_name}.",
                'issued_at' => $event->occurred_at,
            ]);

            $recipients = $this->dispatchService->dispatch($alert);
            $event->update(['processed' => true]);
        }

        return response()->json([
            'message' => $autoCreateAlert
                ? "Event logged and alert sent to {$recipients} user(s)."
                : 'Event logged successfully.',
            'earthquake_event' => new EarthquakeEventResource($event->fresh('city')),
        ], 201);
    }

    public function show(EarthquakeEvent $earthquakeEvent): JsonResponse
    {
        return response()->json([
            'earthquake_event' => new EarthquakeEventResource($earthquakeEvent->load('city')),
        ]);
    }

    private function severityFromMagnitude(float $magnitude): string
    {
        return match (true) {
            $magnitude >= 7.0 => 'critical',
            $magnitude >= 5.5 => 'high',
            $magnitude >= 4.0 => 'medium',
            default => 'low',
        };
    }
}