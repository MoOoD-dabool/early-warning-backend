<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Alert;
use App\Models\DisasterType;
use App\Models\WeatherReading;
use Carbon\Carbon;

/**
 * Turns a raw weather reading into a real disaster alert when it crosses a
 * dangerous threshold — heavy rain/thunderstorm becomes a "flash_flood"
 * alert (only in governorates with wadi terrain), strong wind + a genuine
 * low-pressure system + real rain becomes a "severe_storm" alert inland or
 * a "coastal_storm" alert on the coast (deliberately not called "hurricane"
 * — see config/weather_alerts.php for why), and river/dam stress becomes a
 * "flood" alert (only in river/dam-adjacent governorates — see
 * config/weather_alerts.php for the full rationale of each). Reuses the
 * exact same Alert + dispatch pipeline as earthquakes, so the Flutter app
 * never needs to know the difference.
 */
class WeatherAlertService
{
    public function __construct(
        private readonly AlertDispatchService $dispatchService,
        private readonly OpenMeteoWeatherService $weatherService,
        private readonly OpenMeteoFloodService $floodService,
    ) {
    }

    public function evaluate(WeatherReading $reading): void
    {
        $this->checkHeavyRain($reading);
        $this->checkStrongWind($reading);
        $this->checkFlood($reading);
    }

    private function checkHeavyRain(WeatherReading $reading): void
    {
        if (! in_array($reading->city->code, config('weather_alerts.flash_flood_prone_city_codes'), true)) {
            return;
        }

        $heavyRainCodes = config('weather_alerts.heavy_rain_codes');

        if ($reading->weather_code === null || ! in_array($reading->weather_code, $heavyRainCodes, true)) {
            return;
        }

        if ($this->alreadyAlertedRecently($reading->city_id, 'flash_flood')) {
            return;
        }

        $isThunderstorm = in_array($reading->weather_code, config('weather_alerts.thunderstorm_codes'), true);
        $severity = $isThunderstorm ? 'high' : 'medium';

        $this->createAlert(
            reading: $reading,
            disasterTypeKey: 'flash_flood',
            severity: $severity,
            messageAr: "تم رصد {$this->rainDescriptionAr($isThunderstorm)} في {$reading->city->name_ar}، ما قد يتسبب بسيول. يرجى توخي الحذر وتجنب مجاري الأودية.",
            messageEn: "{$this->rainDescriptionEn($isThunderstorm)} detected in {$reading->city->name_en}, which may cause flash floods. Please be cautious and avoid valley beds.",
        );
    }

    private function checkStrongWind(WeatherReading $reading): void
    {
        $stormConfig = config('weather_alerts.storm');
        $isCoastal = in_array($reading->city->code, $stormConfig['coastal']['city_codes'], true);
        $profile = $isCoastal ? $stormConfig['coastal'] : $stormConfig['inland'];
        $disasterTypeKey = $isCoastal ? 'coastal_storm' : 'severe_storm';

        if ($reading->wind_speed_kmh < $profile['wind_speed_threshold_kmh']) {
            return;
        }

        if ($reading->pressure_msl === null || $reading->pressure_msl > $profile['low_pressure_threshold_hpa']) {
            return;
        }

        if ($reading->precipitation_mm === null || $reading->precipitation_mm < $stormConfig['min_precipitation_mm']) {
            return;
        }

        if ($this->alreadyAlertedRecently($reading->city_id, $disasterTypeKey)) {
            return;
        }

        $severity = $reading->wind_speed_kmh >= $profile['wind_speed_critical_kmh'] ? 'critical' : 'high';

        if ($isCoastal) {
            $this->createAlert(
                reading: $reading,
                disasterTypeKey: $disasterTypeKey,
                severity: $severity,
                messageAr: "تم رصد عاصفة ساحلية شديدة (رياح {$reading->wind_speed_kmh} كم/س، مطر {$reading->precipitation_mm} ملم/س) مصحوبة بمنخفض جوي قرب {$reading->city->name_ar}. يرجى الابتعاد فوراً عن الشاطئ والمرفأ والبقاء بالداخل.",
                messageEn: "A severe coastal storm was detected (wind {$reading->wind_speed_kmh} km/h, rain {$reading->precipitation_mm} mm/h) with a low-pressure system near {$reading->city->name_en}. Please move away from the beach and harbor immediately and stay indoors.",
            );

            return;
        }

        $this->createAlert(
            reading: $reading,
            disasterTypeKey: $disasterTypeKey,
            severity: $severity,
            messageAr: "تم رصد عاصفة شديدة (رياح {$reading->wind_speed_kmh} كم/س، مطر {$reading->precipitation_mm} ملم/س) مصحوبة بمنخفض جوي في {$reading->city->name_ar}. يرجى البقاء بالداخل وتأمين الأغراض الخارجية.",
            messageEn: "A severe storm was detected (wind {$reading->wind_speed_kmh} km/h, rain {$reading->precipitation_mm} mm/h) with a low-pressure system in {$reading->city->name_en}. Please stay indoors and secure outdoor items.",
        );
    }

    /**
     * Dispatches to whichever river/dam group this city belongs to (see
     * config/weather_alerts.php for why each group uses different logic).
     * A city not listed in any group has no known nearby river/dam and is
     * skipped entirely — rain alone is not a reliable flood signal.
     */
    private function checkFlood(WeatherReading $reading): void
    {
        $cityCode = $reading->city->code;
        $floodConfig = config('weather_alerts.flood');

        if (in_array($cityCode, $floodConfig['rastan']['city_codes'], true)) {
            $this->checkRastanFlood($reading, $floodConfig['rastan']);
        } elseif (in_array($cityCode, $floodConfig['euphrates']['city_codes'], true)) {
            $this->checkEuphratesFlood($reading, $floodConfig['euphrates']);
        } elseif (in_array($cityCode, $floodConfig['generic']['city_codes'], true)) {
            $this->checkGenericRiverFlood($reading, $floodConfig['generic']);
        }
    }

    /**
     * Homs/Hama, on the Orontes with the Rastan Dam between them — the one
     * group with a real (if not fully certified) engineering capacity
     * figure to compare against. See config/weather_alerts.php for the
     * two-scenario rationale.
     */
    private function checkRastanFlood(WeatherReading $reading, array $cfg): void
    {
        if ($this->alreadyAlertedRecently($reading->city_id, 'flood')) {
            return;
        }

        $discharge = $this->floodService->fetchDischarge((float) $reading->city->latitude, (float) $reading->city->longitude, 1);
        if ($discharge === null || empty($discharge['discharge'])) {
            return;
        }
        $currentDischarge = (float) end($discharge['discharge']);

        $hourly = $this->weatherService->fetchHourlyPrecipitation(
            (float) $reading->city->latitude,
            (float) $reading->city->longitude,
            2,
        );
        if ($hourly === null) {
            return;
        }

        $capacity = $cfg['spillway_capacity_m3s'];
        $rainIntense = $this->sumRecentHours($hourly, $cfg['intense_rain_window_hours']);
        $rainSustained = $this->sumRecentHours($hourly, $cfg['sustained_rain_window_hours']);

        if ($currentDischarge > $capacity && $rainIntense >= $cfg['intense_rain_threshold_mm']) {
            $this->createAlert(
                reading: $reading,
                disasterTypeKey: 'flood',
                severity: 'critical',
                messageAr: "تجاوز تدفق نهر العاصي الطاقة التصريفية لسد الرستن (سُجل {$currentDischarge} م³/ث) مع أمطار غزيرة مستمرة قرب {$reading->city->name_ar}. خطر فيضان مرتفع — يرجى الابتعاد فوراً عن ضفاف النهر.",
                messageEn: "Orontes River flow has exceeded the Rastan Dam's discharge capacity ({$currentDischarge} m³/s recorded) amid ongoing heavy rain near {$reading->city->name_en}. High flood risk — please move away from the riverbanks immediately.",
            );

            return;
        }

        if ($currentDischarge >= $capacity * $cfg['near_capacity_ratio'] && $rainSustained >= $cfg['sustained_rain_threshold_mm']) {
            $this->createAlert(
                reading: $reading,
                disasterTypeKey: 'flood',
                severity: 'high',
                messageAr: "سد الرستن يصرّف كميات كبيرة من المياه بسبب أمطار غزيرة مستمرة قرب {$reading->city->name_ar}. احتمال فيضان تدريجي بمجرى نهر العاصي خلال الأيام القادمة.",
                messageEn: "The Rastan Dam is releasing large volumes of water due to sustained heavy rain near {$reading->city->name_en}. Gradual flooding along the Orontes River channel is possible over the coming days.",
            );
        }
    }

    /**
     * Hasakah/Raqqa/Deir ez-Zor, on the Euphrates — no verified absolute
     * capacity figure exists for the Tabqa Dam, so this compares the
     * river's own measured discharge against its own recent history at
     * the same location instead of a fixed number.
     */
    private function checkEuphratesFlood(WeatherReading $reading, array $cfg): void
    {
        if ($this->alreadyAlertedRecently($reading->city_id, 'flood')) {
            return;
        }

        $series = $this->floodService->fetchDischarge(
            (float) $reading->city->latitude,
            (float) $reading->city->longitude,
            $cfg['baseline_window_days'],
        );

        $rise = $cfg['rise_window_days'];
        if ($series === null || count($series['discharge']) < $rise + 5) {
            return;
        }

        $values = $series['discharge'];
        $count = count($values);
        $today = $values[$count - 1];
        $daysAgo = $values[$count - 1 - $rise];
        $baseline = $this->median(array_slice($values, 0, $count - $rise));

        if ($baseline <= 0) {
            return;
        }

        $ratio = $today / $baseline;
        $risingSharply = $daysAgo > 0 && ($today / $daysAgo) >= 2.0;

        if ($ratio >= $cfg['critical_multiplier'] && $risingSharply) {
            $this->createAlert(
                reading: $reading,
                disasterTypeKey: 'flood',
                severity: 'critical',
                messageAr: "ارتفع تدفق نهر الفرات قرب {$reading->city->name_ar} بشكل حاد وغير معتاد (تصريف حالي {$today} م³/ث مقابل معدل طبيعي {$baseline} م³/ث). خطر فيضان مرتفع — يرجى الابتعاد فوراً عن ضفاف النهر.",
                messageEn: "Euphrates River flow near {$reading->city->name_en} has risen sharply and unusually (current discharge {$today} m³/s vs. a normal baseline of {$baseline} m³/s). High flood risk — please move away from the riverbanks immediately.",
            );

            return;
        }

        if ($ratio >= $cfg['high_multiplier']) {
            $yesterday = $values[$count - 2];
            if ($yesterday >= $baseline * $cfg['high_multiplier']) {
                $this->createAlert(
                    reading: $reading,
                    disasterTypeKey: 'flood',
                    severity: 'high',
                    messageAr: "تدفق نهر الفرات قرب {$reading->city->name_ar} أعلى من معدله الطبيعي بشكل مستمر (تصريف حالي {$today} م³/ث). احتمال فيضان بمجرى النهر — يرجى توخي الحذر قرب الضفاف.",
                    messageEn: "Euphrates River flow near {$reading->city->name_en} has remained well above its normal baseline (current discharge {$today} m³/s). Possible river-channel flooding — please be cautious near the banks.",
                );
            }
        }
    }

    /**
     * Governorates with a real river/dam nearby but no detailed capacity
     * study — exceptional accumulated rain plus the river's own discharge
     * well above its recent baseline, both self-calibrating.
     */
    private function checkGenericRiverFlood(WeatherReading $reading, array $cfg): void
    {
        if ($this->alreadyAlertedRecently($reading->city_id, 'flood')) {
            return;
        }

        $hourly = $this->weatherService->fetchHourlyPrecipitation(
            (float) $reading->city->latitude,
            (float) $reading->city->longitude,
            2,
        );
        if ($hourly === null) {
            return;
        }

        $rainSustained = $this->sumRecentHours($hourly, $cfg['sustained_rain_window_hours']);
        if ($rainSustained < $cfg['sustained_rain_threshold_mm']) {
            return;
        }

        $series = $this->floodService->fetchDischarge(
            (float) $reading->city->latitude,
            (float) $reading->city->longitude,
            $cfg['baseline_window_days'],
        );
        if ($series === null || count($series['discharge']) < 5) {
            return;
        }

        $values = $series['discharge'];
        $count = count($values);
        $today = $values[$count - 1];
        $baseline = $this->median(array_slice($values, 0, $count - 1));

        if ($baseline <= 0 || $today < $baseline * $cfg['discharge_multiplier']) {
            return;
        }

        $this->createAlert(
            reading: $reading,
            disasterTypeKey: 'flood',
            severity: 'high',
            messageAr: "هطول أمطار استثنائي مع ارتفاع واضح بمنسوب المجرى المائي قرب {$reading->city->name_ar}. احتمال فيضان — يرجى تجنب الاقتراب من الأنهار ومجاري المياه.",
            messageEn: "Exceptional rainfall combined with a clear rise in the local waterway near {$reading->city->name_en}. Possible flooding — please avoid rivers and waterways.",
        );
    }

    /**
     * Sums the last `$hours` hourly precipitation values (mm) from a
     * fetchHourlyPrecipitation() result.
     */
    private function sumRecentHours(array $hourly, int $hours): float
    {
        $recent = array_slice($hourly['precipitation_mm'], -$hours);

        return array_sum($recent);
    }

    private function median(array $values): float
    {
        sort($values);
        $count = count($values);

        if ($count === 0) {
            return 0.0;
        }

        $middle = intdiv($count, 2);

        if ($count % 2 === 0) {
            return ($values[$middle - 1] + $values[$middle]) / 2;
        }

        return $values[$middle];
    }

    private function rainDescriptionAr(bool $isThunderstorm): string
    {
        return $isThunderstorm ? 'عاصفة رعدية' : 'أمطار غزيرة';
    }

    private function rainDescriptionEn(bool $isThunderstorm): string
    {
        return $isThunderstorm ? 'A thunderstorm' : 'Heavy rain';
    }

    /**
     * Avoids spamming the same city with repeated alerts while the same
     * weather system is still ongoing (we fetch weather periodically, so
     * without this every fetch during a storm would fire a new alert).
     */
    private function alreadyAlertedRecently(int $cityId, string $disasterTypeKey): bool
    {
        $disasterType = DisasterType::query()->where('key', $disasterTypeKey)->first();

        if (! $disasterType) {
            return false;
        }

        $cooldownHours = config('weather_alerts.cooldown_hours');

        return Alert::query()
            ->where('city_id', $cityId)
            ->where('disaster_type_id', $disasterType->id)
            ->where('issued_at', '>=', Carbon::now()->subHours($cooldownHours))
            ->exists();
    }

    private function createAlert(WeatherReading $reading, string $disasterTypeKey, string $severity, string $messageAr, string $messageEn): void
    {
        $disasterType = DisasterType::query()->where('key', $disasterTypeKey)->first();

        if (! $disasterType) {
            return;
        }

        $alert = Alert::query()->create([
            'disaster_type_id' => $disasterType->id,
            'city_id' => $reading->city_id,
            'earthquake_event_id' => null,
            'severity' => $severity,
            'trigger_siren' => in_array($severity, ['high', 'critical'], true),
            'message_ar' => $messageAr,
            'message_en' => $messageEn,
            'issued_at' => Carbon::now(),
        ]);

        $this->dispatchService->dispatch($alert);
    }
}