<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WeatherReadingResource;
use App\Models\WeatherReading;
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