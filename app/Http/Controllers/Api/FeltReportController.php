<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreFeltReportRequest;
use App\Http\Resources\EarthquakeEventResource;
use App\Http\Resources\FeltReportResource;
use App\Models\Alert;
use App\Models\EarthquakeEvent;
use App\Models\FeltReport;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FeltReportController extends Controller
{
    /**
     * The most recent real earthquake that could plausibly have been felt in
     * the authenticated user's own governorate, found via the Alert rows the
     * earthquake processor already created for every affected city — not
     * `earthquake_events.city_id` alone, since that only ever holds the
     * single nearest city while one real quake can affect several.
     */
    private function eligibleEarthquakeFor(int $cityId): ?EarthquakeEvent
    {
        $cutoff = Carbon::now('UTC')->subHours((int) config('mercalli.eligible_window_hours'));

        $alert = Alert::query()
            ->whereNotNull('earthquake_event_id')
            ->where('city_id', $cityId)
            ->where('issued_at', '>=', $cutoff)
            ->orderByDesc('issued_at')
            ->with('earthquakeEvent')
            ->first();

        return $alert?->earthquakeEvent;
    }

    /**
     * Tells the app whether there's currently an earthquake the logged-in
     * user can report feeling, plus the live report count and the bilingual
     * intensity-level options to show on the "Did You Feel It" screen.
     */
    public function currentEarthquake(Request $request): JsonResponse
    {
        $user = $request->user();

        $levels = collect(config('mercalli.levels'))
            ->map(fn ($level, $value) => [
                'value' => $value,
                'roman' => $level['roman'],
                'label' => [
                    'ar' => $level['label_ar'],
                    'en' => $level['label_en'],
                ],
            ])
            ->values();

        if (! $user->city_id) {
            return response()->json([
                'has_earthquake' => false,
                'reason' => 'no_city',
                'already_reported' => false,
                'report_count' => 0,
                'earthquake' => null,
                'levels' => $levels,
            ]);
        }

        $earthquakeEvent = $this->eligibleEarthquakeFor($user->city_id);

        if (! $earthquakeEvent) {
            return response()->json([
                'has_earthquake' => false,
                'reason' => 'no_recent_earthquake',
                'already_reported' => false,
                'report_count' => 0,
                'earthquake' => null,
                'levels' => $levels,
            ]);
        }

        return response()->json([
            'has_earthquake' => true,
            'reason' => null,
            'already_reported' => FeltReport::query()
                ->where('user_id', $user->id)
                ->where('earthquake_event_id', $earthquakeEvent->id)
                ->exists(),
            'report_count' => FeltReport::query()
                ->where('earthquake_event_id', $earthquakeEvent->id)
                ->count(),
            'earthquake' => new EarthquakeEventResource($earthquakeEvent->load('city')),
            'levels' => $levels,
        ]);
    }

    /**
     * Submits the authenticated user's felt-intensity report for whichever
     * earthquake `eligibleEarthquakeFor()` currently resolves to — the
     * client never chooses or sends the earthquake itself, so there's no way
     * to report against the wrong event.
     */
    public function store(StoreFeltReportRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->city_id) {
            throw ValidationException::withMessages([
                'profile' => __('messages.felt_reports.incomplete_profile'),
            ]);
        }

        $earthquakeEvent = $this->eligibleEarthquakeFor($user->city_id);

        if (! $earthquakeEvent) {
            throw ValidationException::withMessages([
                'earthquake' => __('messages.felt_reports.no_recent_earthquake'),
            ]);
        }

        $alreadyReported = FeltReport::query()
            ->where('user_id', $user->id)
            ->where('earthquake_event_id', $earthquakeEvent->id)
            ->exists();

        if ($alreadyReported) {
            throw ValidationException::withMessages([
                'earthquake' => __('messages.felt_reports.already_reported'),
            ]);
        }

        try {
            $feltReport = $user->feltReports()->create([
                'city_id' => $user->city_id,
                'earthquake_event_id' => $earthquakeEvent->id,
                'intensity_levels' => $request->validated('intensity_levels'),
            ]);
        } catch (QueryException $e) {
            // Two concurrent submissions for the same user+earthquake both
            // pass the exists() check above before either commits — the
            // unique(['user_id', 'earthquake_event_id']) constraint still
            // blocks the second insert, so turn that into the same clean
            // "already reported" response instead of a raw 500.
            if ($e->getCode() === '23000') {
                throw ValidationException::withMessages([
                    'earthquake' => __('messages.felt_reports.already_reported'),
                ]);
            }

            throw $e;
        }

        return response()->json([
            'message' => __('messages.felt_reports.submitted'),
            'felt_report' => new FeltReportResource($feltReport->load(['user', 'city', 'earthquakeEvent'])),
        ], 201);
    }
}
