<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Heavy rain / thunderstorm → "flash_flood" disaster type
    |--------------------------------------------------------------------------
    | Open-Meteo weather_code values (WMO standard) that represent rain
    | severe enough to plausibly cause flash flooding.
    | 65 = heavy rain, 82 = violent rain showers, 95/96/99 = thunderstorm.
    */
    'heavy_rain_codes' => [65, 82, 95, 96, 99],

    /*
    | Codes considered a thunderstorm specifically — used to pick a higher
    | severity than plain heavy rain.
    */
    'thunderstorm_codes' => [95, 96, 99],

    /*
    | flash_flood only makes sense in governorates with valley/wadi terrain
    | that channels sudden runoff — heavy rain over flat desert (e.g.
    | Palmyra) or the Jazira steppe doesn't produce the same destructive
    | flash flooding, so heavy-rain checks are scoped to this list rather
    | than applied to all 16 governorates equally.
    */
    'flash_flood_prone_city_codes' => [
        'SY001', // Damascus — Wadi Barada
        'SY002', // Rif Dimashq
        'SY003', // Daraa — seasonal wadis
        'SY004', // Quneitra — Golan hill terrain
        'SY005', // As-Suwayda — Jabal al-Druze wadis
        'SY009', // Tartus — coastal mountains
        'SY010', // Latakia — coastal mountains
        'SY011', // Idlib
        'SY012', // Aleppo — flash floods reported north of Aleppo
    ],

    /*
    |--------------------------------------------------------------------------
    | Strong wind + low pressure + rain → "severe_storm" / "coastal_storm"
    |--------------------------------------------------------------------------
    | A genuine tropical hurricane cannot form near Syria's coast — it needs
    | sea-surface temperatures ≥26.5°C over a deep warm layer that the
    | Eastern Mediterranean doesn't sustain. The real, alertable hazard here
    | is a severe low-pressure storm system (an ordinary extratropical
    | storm, or in rare cases a "Medicane" near the coast), so this is
    | deliberately named/framed as a storm, not a hurricane.
    |
    | All three conditions (wind, pressure, rain) must be true at once — wind
    | alone is common; wind + a real low-pressure system without rain is
    | usually just a dry, transient wind event (e.g. a Khamsin-type wind),
    | not a damaging storm for Syria. Requiring rain confirms it's a genuine
    | wet storm system.
    |
    | Coastal governorates get their own, stricter thresholds — reused from
    | the tsunami-alert coastal list (config/earthquake.php) — because (a)
    | they naturally see stronger routine sea-breeze wind, so the same
    | inland wind threshold would over-alert, and (b) the pressure threshold
    | below is calibrated to the real historical range of Mediterranean
    | storms ("Medicanes": 978.6–995 hPa central pressure on record) rather
    | than an arbitrary number.
    |
    | Wind thresholds are anchored to the official Beaufort scale (7=near
    | gale 50-61km/h, 8=gale 62-74, 9=severe gale 75-88, 10=storm 89-102);
    | the inland pressure threshold (1000 hPa) is the standard meteorological
    | boundary between a "shallow" and a "moderate" low-pressure system.
    */
    'storm' => [
        'min_precipitation_mm' => 1.0, // within WMO's "light rain" band (≤2.5mm/h) — well above measurement noise/trace

        'inland' => [
            'wind_speed_threshold_kmh' => 60, // ~near gale/gale, Beaufort 7-8
            'wind_speed_critical_kmh' => 80, // severe gale, Beaufort 9
            'low_pressure_threshold_hpa' => 1000, // shallow/moderate-low boundary
        ],

        'coastal' => [
            'city_codes' => ['SY009', 'SY010'], // Tartus, Latakia
            'wind_speed_threshold_kmh' => 65, // solidly gale-force, Beaufort 8
            'wind_speed_critical_kmh' => 90, // storm force, Beaufort 10
            'low_pressure_threshold_hpa' => 995, // covers the full real Medicane range (978.6-995 hPa)
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Anti-spam window
    |--------------------------------------------------------------------------
    | Don't issue a new weather-triggered alert for the same city + disaster
    | type if one was already issued within this many hours.
    */
    'cooldown_hours' => 24,

    /*
    |--------------------------------------------------------------------------
    | River / dam flooding → "flood" disaster type
    |--------------------------------------------------------------------------
    | Unlike flash_flood (sudden runoff in wadis), this is about a river or
    | the dam controlling it being under real stress — either accepting
    | more water than it can safely handle, or having to release so much
    | water for so long that the channel downstream floods. Rain alone is a
    | weak signal for this (a desert governorate can get heavy rain with no
    | river anywhere near it), so this only runs for governorates that
    | actually sit on/near a real river or dam, and each group uses
    | whichever real data is actually trustworthy for it — see the note on
    | each group below. This is a deliberately simple geographic/hydrological
    | targeting model, not a certified engineering flood-forecasting system.
    */
    'flood' => [

        /*
        | Homs + Hama sit directly on the Orontes river, with the Rastan Dam
        | between them. 1500 m3/s is the dam's documented spillway design
        | discharge capacity (sourced from public references on the Rastan
        | Dam; treat as a reasonable engineering estimate, not a certified
        | figure). Two scenarios, checked in order:
        |   A) inflow already exceeds the dam's own capacity + a burst of
        |      intense LOCAL rain in the last few hours -> the dam itself is
        |      under acute stress. Orontes is a small, locally-fed basin, so
        |      local rain is a meaningful, fast-acting signal here.
        |   B) inflow is close to (but not over) capacity + sustained rain
        |      over 2 days -> the dam is coping by releasing large volumes
        |      for an extended period, which floods the channel downstream
        |      even though the dam itself isn't failing.
        */
        'rastan' => [
            'city_codes' => ['SY006', 'SY008'], // Homs, Hama
            'spillway_capacity_m3s' => 1500.0,
            'near_capacity_ratio' => 0.9,
            'intense_rain_window_hours' => 8,
            'intense_rain_threshold_mm' => 20.0,
            'sustained_rain_window_hours' => 48,
            'sustained_rain_threshold_mm' => 150.0,
        ],

        /*
        | The Euphrates' watershed is enormous and mostly outside Syria, so
        | local rain in Raqqa/Deir ez-Zor/Hasakah is a weak proxy for water
        | actually reaching the Tabqa Dam — no verified absolute discharge
        | capacity figure for it could be confirmed despite extensive
        | research, so instead of a fixed m3/s threshold, this compares the
        | river's OWN measured discharge against its OWN recent history at
        | the same location — self-calibrating regardless of the river's
        | absolute scale. Two scenarios:
        |   A) today's discharge is far above its own recent baseline AND
        |      has risen sharply over the last few days -> an actual flood
        |      wave is arriving (from upstream rain, snowmelt, or a release
        |      — the cause doesn't matter, the river's own behavior does).
        |   B) discharge is moderately above baseline AND has stayed there
        |      for more than one reading -> sustained elevated release,
        |      not a single noisy data point.
        */
        'euphrates' => [
            'city_codes' => ['SY013', 'SY014', 'SY015'], // Al-Hasakah, Raqqa, Deir ez-Zor
            'baseline_window_days' => 30,
            'rise_window_days' => 3,
            'critical_multiplier' => 3.0,
            'high_multiplier' => 2.0,
        ],

        /*
        | Governorates with a real river/dam nearby but without a detailed
        | capacity study behind them (Damascus/Rif Dimashq on the Barada,
        | Daraa/Quneitra on the Yarmouk, Tartus/Latakia's smaller coastal
        | rivers, Idlib, and Aleppo near the Euphrates' edge). Single check:
        | exceptional accumulated rain AND the river's own discharge well
        | above its recent baseline — both self-calibrating, no absolute
        | capacity number required.
        */
        'generic' => [
            'city_codes' => ['SY001', 'SY002', 'SY003', 'SY004', 'SY009', 'SY010', 'SY011', 'SY012'],
            'baseline_window_days' => 30,
            'discharge_multiplier' => 2.5,
            'sustained_rain_window_hours' => 48,
            'sustained_rain_threshold_mm' => 150.0,
        ],
    ],

];