<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AlertResource;
use App\Http\Resources\EarthquakeEventResource;
use App\Models\Alert;
use App\Models\EarthquakeEvent;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class HomeSummaryController extends Controller
{
    /**
     * Nationwide "today" counts + the current active warning (if any) for
     * the mobile app's home screen. "Today" is defined in Damascus local
     * time, not the server's UTC clock, since that's what "today" means to
     * a user in Syria.
     */
    public function index(): JsonResponse
    {
        $todayStart = Carbon::now('Asia/Damascus')->startOfDay()->setTimezone('UTC');
        $todayEnd = $todayStart->copy()->addDay();

        // One real seismic event can trigger an Alert for every affected
        // governorate, so "today's earthquakes" counts distinct events
        // (earthquake_events), not the alerts they produced.
        $todayEarthquakes = EarthquakeEvent::query()
            ->whereBetween('occurred_at', [$todayStart, $todayEnd])
            ->count();

        $weatherWarnings = Alert::query()
            ->whereHas('disasterType', fn ($q) => $q->whereIn('key', ['severe_storm', 'coastal_storm', 'flash_flood']))
            ->whereBetween('issued_at', [$todayStart, $todayEnd])
            ->count();

        $floods = Alert::query()
            ->whereHas('disasterType', fn ($q) => $q->where('key', 'flood'))
            ->whereBetween('issued_at', [$todayStart, $todayEnd])
            ->count();

        $activeWarning = Alert::query()
            ->with(['disasterType', 'city', 'earthquakeEvent'])
            ->where('issued_at', '>=', Carbon::now('UTC')->subDay())
            ->where(function ($q) {
                $q->whereIn('severity', ['high', 'critical'])
                    ->orWhere('trigger_siren', true);
            })
            ->orderByDesc('issued_at')
            ->first();

        // Nationwide, no time window — the home screen's "last recorded
        // earthquake" card is a reference card, not a "today" stat, so it
        // stays hidden client-side only when the table is completely empty.
        $latestEarthquake = EarthquakeEvent::query()
            ->with('city')
            ->orderByDesc('occurred_at')
            ->first();

        return response()->json([
            'stats' => [
                'today_earthquakes' => $todayEarthquakes,
                'weather_warnings' => $weatherWarnings,
                'floods' => $floods,
            ],
            'active_warning' => $activeWarning ? new AlertResource($activeWarning) : null,
            'latest_earthquake' => $latestEarthquake ? new EarthquakeEventResource($latestEarthquake) : null,
        ]);
    }
}
