<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Refresh weather data for all 16 Syrian governorates every hour
// (on the hour) — also re-evaluates severe-weather thresholds each
// time it runs.
Schedule::command('weather:fetch')->hourly();