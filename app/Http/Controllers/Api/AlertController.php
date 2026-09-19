<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AlertResource;
use App\Http\Resources\UserAlertResource;
use App\Models\Alert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    /**
     * Recent alerts issued for the authenticated user's own city.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $alerts = Alert::query()
            ->with(['disasterType', 'city', 'earthquakeEvent'])
            ->where('city_id', $user->city_id)
            ->orderByDesc('issued_at')
            ->paginate(20);

        return response()->json([
            'alerts' => AlertResource::collection($alerts),
            'meta' => [
                'current_page' => $alerts->currentPage(),
                'last_page' => $alerts->lastPage(),
                'total' => $alerts->total(),
            ],
        ]);
    }

    public function show(Alert $alert): JsonResponse
    {
        return response()->json([
            'alert' => new AlertResource($alert->load(['disasterType', 'city', 'earthquakeEvent'])),
        ]);
    }

    /**
     * The notification feed shown on the main screen: alerts this specific
     * user actually received (from user_alerts), most recent first.
     */
    public function myAlerts(Request $request): JsonResponse
    {
        $userAlerts = $request->user()
            ->userAlerts()
            ->with('alert.disasterType', 'alert.city')
            ->orderByDesc('received_at')
            ->paginate(20);

        return response()->json([
            'notifications' => UserAlertResource::collection($userAlerts),
            'meta' => [
                'current_page' => $userAlerts->currentPage(),
                'last_page' => $userAlerts->lastPage(),
                'total' => $userAlerts->total(),
            ],
        ]);
    }
}
