<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CityResource;
use App\Http\Resources\WeatherReadingResource;
use App\Models\City;
use App\Models\WeatherReading;
use App\Services\OpenMeteoWeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WeatherController extends Controller
{
    /**
     * Latest weather reading for every Syrian governorate.
     */
    public function index(): JsonResponse
    {
        $readings = $this->latestPerCityQuery()->get();

        return response()->json([
            'weather' => WeatherReadingResource::collection($readings),
        ]);
    }

    /**
     * Latest weather reading for the authenticated user's own city.
     */
    public function mine(Request $request): JsonResponse
    {
        $user = $request->user();

        $reading = $this->latestPerCityQuery()
            ->where('city_id', $user->city_id)
            ->first();

        if (! $reading) {
            return response()->json([
                'message' => __('messages.weather.no_data'),
            ], 404);
        }

        return response()->json([
            'weather' => new WeatherReadingResource($reading),
        ]);
    }

    /**
     * A 7-day forecast (today + the next 6 days) for one governorate,
     * fetched live from Open-Meteo on every call — unlike the current-weather
     * readings above, forecasts are not stored anywhere, since they'd go
     * stale the moment the day changes and a fresh call is just as cheap.
     */
    public function forecast(Request $request, string $cityCode, OpenMeteoWeatherService $openMeteo): JsonResponse
    {
        $city = City::query()->where('code', $cityCode)->first();

        if (! $city) {
            return response()->json([
                'message' => __('messages.weather.city_not_found'),
            ], 404);
        }

        $forecast = $openMeteo->fetchDailyForecast((float) $city->latitude, (float) $city->longitude);

        if ($forecast === null) {
            return response()->json([
                'message' => __('messages.weather.forecast_unavailable'),
            ], 503);
        }

        return response()->json([
            'city' => new CityResource($city),
            'forecast' => $forecast,
        ]);
    }

    /**
     * One row per city: the reading with the highest id (= most recent,
     * since readings are insert-only) for each city_id.
     */
    private function latestPerCityQuery()
    {
        return WeatherReading::query()
            ->with('city')
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('weather_readings')
                    ->groupBy('city_id');
            });
    }
}