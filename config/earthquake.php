<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Syria bounding box
    |--------------------------------------------------------------------------
    | The strict geographic box used to confirm an earthquake's epicenter is
    | actually inside Syria.
    */
    'syria_bounds' => [
        'min_latitude' => 32.0,
        'max_latitude' => 37.5,
        'min_longitude' => 35.5,
        'max_longitude' => 42.5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Impact zone (wider box)
    |--------------------------------------------------------------------------
    | A wider box (Syria + a buffer into Turkey/Lebanon/Iraq/Jordan) used to
    | decide whether an earthquake is even worth evaluating. A quake centered
    | just outside Syria's border can still be felt inside it.
    */
    'impact_bounds' => [
        'min_latitude' => 30.5,
        'max_latitude' => 39.0,
        'min_longitude' => 34.0,
        'max_longitude' => 44.0,
    ],

    /*
    |--------------------------------------------------------------------------
    | Magnitude thresholds
    |--------------------------------------------------------------------------
    | - Below `ignore_below`: dropped entirely, no log, no alert.
    | - Between `ignore_below` and `siren_min`: text-only alert (no siren).
    | - `siren_min` and above: alert with `trigger_siren = true`.
    */
    'magnitude_thresholds' => [
        'ignore_below' => 2.0,
        'siren_min' => 3.0,
    ],

    /*
    |--------------------------------------------------------------------------
    | Estimated impact radius (km) by magnitude
    |--------------------------------------------------------------------------
    | NOT a scientific ground-shaking/intensity model — real attenuation
    | depends on soil type, propagation direction, and much more than this
    | project attempts to model. This is a deliberately simple geographic
    | targeting heuristic: "which Syrian governorates are plausibly close
    | enough to this epicenter to be worth alerting", picked by rounding
    | down to the highest magnitude key at or below the event's magnitude.
    | Tune these numbers freely — they are a judgment call, not a fact.
    */
    'impact_radius_km' => [
        2.0 => 30,
        3.0 => 60,
        4.0 => 120,
        5.0 => 220,
        6.0 => 350,
        7.0 => 500,
    ],

    /*
    |--------------------------------------------------------------------------
    | Tsunami risk zone (offshore Mediterranean west of the Syrian coast)
    |--------------------------------------------------------------------------
    | Covers the sea area between the Syrian coast (east, ~36.0) and Cyprus
    | (west, ~32.2) — the real regional source of tsunami risk for Syria (the
    | Cyprus Arc / Latakia Ridge) — bounded north by the nearest Turkish coast
    | (Iskenderun/Samandağ, ~36.6) and south by the nearest north-Lebanese
    | coast (Tripoli/Akkar, ~34.3), with the southern edge extended slightly
    | further (to 33.8) to keep the ~551 AD Beirut-Tripoli tsunami source
    | (~34.0N, historically the region's strongest documented tsunami) inside
    | the box. A quake centered inland never enters this box.
    */
    'tsunami_zone' => [
        'min_latitude' => 33.8,
        'max_latitude' => 36.6,
        'min_longitude' => 32.2,
        'max_longitude' => 36.0,
    ],

    /*
    |--------------------------------------------------------------------------
    | Tsunami magnitude/depth thresholds
    |--------------------------------------------------------------------------
    | Based on USGS/NOAA guidance: below ~6.0 a tsunami is very unlikely;
    | 6.0–7.4 can produce small, noticeable sea-level changes near the
    | epicenter; 7.5+ can produce a destructive local tsunami. Earthquakes
    | deeper than max_depth_km rarely displace the seafloor enough to matter.
    */
    'tsunami_thresholds' => [
        'min_magnitude' => 6.0,
        'destructive_magnitude' => 7.5,
        'max_depth_km' => 100,
    ],

    /*
    | Coastal governorates alerted when tsunami risk conditions are met.
    */
    'tsunami_coastal_city_codes' => ['SY009', 'SY010'], // Tartus, Latakia

    /*
    |--------------------------------------------------------------------------
    | EMSC endpoints
    |--------------------------------------------------------------------------
    */
    'emsc' => [
        'websocket_url' => 'wss://www.seismicportal.eu/standing_order/websocket',
        'query_url' => 'https://www.seismicportal.eu/fdsnws/event/1/query',
    ],

];