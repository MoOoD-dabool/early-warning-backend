<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Alert;
use App\Models\City;
use App\Models\DisasterType;
use App\Models\EarthquakeEvent;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Turns a single EMSC earthquake message (whether it came from the
 * real-time WebSocket feed or a manual/API source) into an EarthquakeEvent
 * and, when it qualifies, an Alert dispatched to the nearest Syrian
 * governorate's users — or, for a large enough offshore quake near the
 * Cyprus Arc / Latakia Ridge, a tsunami alert sent to both coastal
 * governorates (Tartus and Latakia) instead.
 *
 * Expects the raw `properties` object from an EMSC message, e.g.:
 * ['unid' => '...', 'mag' => 4.2, 'lat' => 35.1, 'lon' => 36.7,
 *  'depth' => 10.0, 'time' => '2026-01-01T12:00:00.0Z', 'flynn_region' => '...']
 */
class EmscEarthquakeProcessor
{
    public function __construct(private readonly AlertDispatchService $dispatchService)
    {
    }

    public function handle(array $properties): void
    {
        $unid = (string) ($properties['unid'] ?? '');
        $time = $properties['time'] ?? null;
        $lat = isset($properties['lat']) ? (float) $properties['lat'] : null;
        $lon = isset($properties['lon']) ? (float) $properties['lon'] : null;
        $magnitude = isset($properties['mag']) ? (float) $properties['mag'] : null;

        if ($unid === '' || $time === null || $lat === null || $lon === null || $magnitude === null) {
            Log::warning('EMSC: skipped malformed event payload.', $properties);

            return;
        }

        // Step 1: is it even in the wider "could be felt in Syria" box?
        $impact = config('earthquake.impact_bounds');
        if (! $this->isInsideBounds($lat, $lon, $impact)) {
            return;
        }

        // Step 2: below the noise floor? drop entirely, no log, no alert.
        $thresholds = config('earthquake.magnitude_thresholds');
        if ($magnitude < $thresholds['ignore_below']) {
            return;
        }

        $depth = isset($properties['depth']) ? (float) $properties['depth'] : 0.0;
        $region = (string) ($properties['flynn_region'] ?? '');

        $affectedCities = $this->affectedCities($lat, $lon, $magnitude, $depth);
        if ($affectedCities->isEmpty()) {
            return;
        }
        $nearestCity = $affectedCities->first()['city'];

        $event = EarthquakeEvent::query()->firstOrNew(['event_id' => $unid]);
        $event->fill([
            'magnitude' => $magnitude,
            'depth_km' => $depth,
            'location_name' => $region !== '' ? $region : 'EMSC',
            'city_id' => $nearestCity->id,
            'occurred_at' => Carbon::parse($time),
        ]);
        if (! $event->exists) {
            $event->processed = false;
        }
        $event->save();

        // Only alert once per event (ignore EMSC's "update" pings for one
        // we already turned into an alert).
        if ($event->processed) {
            return;
        }

        if ($this->isTsunamiRisk($lat, $lon, $magnitude, $depth)) {
            $this->createAndDispatchTsunamiAlerts($event, $magnitude, $region);
        } else {
            $this->createAndDispatchAlerts($event, $affectedCities, $magnitude, $depth, $region);
        }

        $event->update(['processed' => true]);
    }

    /**
     * True only when all three conditions hold at once: the epicenter is
     * inside the offshore tsunami-risk zone, the magnitude is large enough,
     * and the quake is shallow enough to actually displace the seafloor.
     */
    private function isTsunamiRisk(float $lat, float $lon, float $magnitude, float $depth): bool
    {
        $zone = config('earthquake.tsunami_zone');
        $thresholds = config('earthquake.tsunami_thresholds');

        return $this->isInsideBounds($lat, $lon, $zone)
            && $magnitude >= $thresholds['min_magnitude']
            && $depth <= $thresholds['max_depth_km'];
    }

    /**
     * Creates and dispatches one tsunami alert per coastal governorate
     * (Tartus and Latakia) instead of the usual single-nearest-city
     * earthquake alert, since a tsunami threatens the whole coast.
     */
    private function createAndDispatchTsunamiAlerts(EarthquakeEvent $event, float $magnitude, string $region): void
    {
        $thresholds = config('earthquake.tsunami_thresholds');
        $severity = $magnitude >= $thresholds['destructive_magnitude'] ? 'critical' : 'medium';
        $disasterType = DisasterType::query()->where('key', 'tsunami')->first();
        $regionSuffix = $region !== '' ? " ({$region})" : '';

        $coastalCityCodes = config('earthquake.tsunami_coastal_city_codes');
        $coastalCities = City::query()->whereIn('code', $coastalCityCodes)->get();

        foreach ($coastalCities as $city) {
            $alert = Alert::query()->create([
                'disaster_type_id' => $disasterType?->id,
                'city_id' => $city->id,
                'earthquake_event_id' => $event->id,
                'severity' => $severity,
                'trigger_siren' => true,
                'message_ar' => "تم رصد زلزال بحري بقوة {$magnitude} قد يسبب تسونامي يؤثر على {$city->name_ar}{$regionSuffix}. يرجى الابتعاد فوراً عن الساحل والتوجه لأعلى منطقة ممكنة.",
                'message_en' => "A magnitude {$magnitude} offshore earthquake that may cause a tsunami affecting {$city->name_en}{$regionSuffix} was detected. Please move away from the coast immediately to the highest ground possible.",
                'issued_at' => $event->occurred_at,
            ]);

            $this->dispatchService->dispatch($alert);
        }
    }

    /**
     * Creates and dispatches one alert per governorate within the
     * estimated impact radius, nearest first. Severity tapers down with
     * distance from the epicenter (see severityForDistance()) so the
     * nearest city gets the full magnitude-based severity while the
     * farthest ones within range get a milder alert instead of an
     * identical one — a siren only fires for a city whose own tapered
     * severity is still high/critical, not just because the epicenter
     * magnitude cleared the siren threshold.
     */
    private function createAndDispatchAlerts(
        EarthquakeEvent $event,
        Collection $affectedCities,
        float $magnitude,
        float $depth,
        string $region,
    ): void {
        $thresholds = config('earthquake.magnitude_thresholds');
        $baseSeverity = $this->severityFromMagnitude($magnitude);
        $disasterType = DisasterType::query()->where('key', 'earthquake')->first();
        $regionSuffix = $region !== '' ? " ({$region})" : '';
        $radiusKm = $this->estimatedImpactRadiusKm($magnitude, $depth);

        foreach ($affectedCities as $row) {
            $city = $row['city'];
            $severity = $this->severityForDistance($baseSeverity, $row['distance'], $radiusKm);
            $triggerSiren = $magnitude >= $thresholds['siren_min']
                && in_array($severity, ['high', 'critical'], true);

            $alert = Alert::query()->create([
                'disaster_type_id' => $disasterType?->id,
                'city_id' => $city->id,
                'earthquake_event_id' => $event->id,
                'severity' => $severity,
                'trigger_siren' => $triggerSiren,
                'message_ar' => "تم رصد زلزال بقوة {$magnitude} قرب {$city->name_ar}{$regionSuffix}.",
                'message_en' => "Magnitude {$magnitude} earthquake detected near {$city->name_en}{$regionSuffix}.",
                'issued_at' => $event->occurred_at,
            ]);

            $this->dispatchService->dispatch($alert);
        }
    }

    private function isInsideBounds(float $lat, float $lon, array $bounds): bool
    {
        return $lat >= $bounds['min_latitude']
            && $lat <= $bounds['max_latitude']
            && $lon >= $bounds['min_longitude']
            && $lon <= $bounds['max_longitude'];
    }

    /**
     * Every Syrian governorate whose center falls within the earthquake's
     * estimated impact radius, nearest first — each entry is
     * ['city' => City, 'distance' => km from epicenter]. Always returns at
     * least the single nearest city, even if none technically fall inside
     * the radius, so a real event near the estimate's edge never silently
     * produces zero alerts.
     */
    private function affectedCities(float $lat, float $lon, float $magnitude, float $depth): Collection
    {
        $radiusKm = $this->estimatedImpactRadiusKm($magnitude, $depth);

        $citiesByDistance = City::query()->get()
            ->map(fn (City $city) => [
                'city' => $city,
                'distance' => $this->haversineKm($lat, $lon, (float) $city->latitude, (float) $city->longitude),
            ])
            ->sortBy('distance')
            ->values();

        $withinRadius = $citiesByDistance->filter(fn (array $row) => $row['distance'] <= $radiusKm)->values();

        return $withinRadius->isNotEmpty() ? $withinRadius : $citiesByDistance->take(1);
    }

    /**
     * A deliberately simple magnitude → radius lookup (see
     * config/earthquake.php for the full rationale and caveats), with a
     * mild reduction for very deep earthquakes: energy from a deep quake
     * spreads across a wider area before it reaches the surface, so the
     * shaking felt at any single point tends to be weaker. Floored at half
     * the base radius so depth alone never zeroes out the impact area.
     */
    private function estimatedImpactRadiusKm(float $magnitude, float $depth): float
    {
        $bands = config('earthquake.impact_radius_km');
        ksort($bands);

        $radiusKm = (float) reset($bands);
        foreach ($bands as $magnitudeFloor => $radius) {
            if ($magnitude >= $magnitudeFloor) {
                $radiusKm = (float) $radius;
            }
        }

        if ($depth > 100) {
            $depthFactor = max(0.5, 1 - (($depth - 100) / 300));
            $radiusKm *= $depthFactor;
        }

        return $radiusKm;
    }

    /**
     * Steps the base (epicenter) severity down the closer a city is to the
     * edge of the impact radius, instead of giving every affected city an
     * identical alert regardless of how far it is from the epicenter.
     */
    private function severityForDistance(string $baseSeverity, float $distanceKm, float $radiusKm): string
    {
        $levels = ['low', 'medium', 'high', 'critical'];
        $index = array_search($baseSeverity, $levels, true);

        $ratio = $radiusKm > 0 ? $distanceKm / $radiusKm : 0;
        $stepsDown = match (true) {
            $ratio <= 1 / 3 => 0,
            $ratio <= 2 / 3 => 1,
            default => 2,
        };

        return $levels[max(0, $index - $stepsDown)];
    }

    private function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadiusKm = 6371.0;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusKm * $c;
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