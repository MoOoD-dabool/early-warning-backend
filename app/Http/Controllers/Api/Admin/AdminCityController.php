<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCityRequest;
use App\Http\Requests\Admin\UpdateCityRequest;
use App\Http\Resources\CityResource;
use App\Models\City;
use Illuminate\Http\JsonResponse;

class AdminCityController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'cities' => CityResource::collection(City::query()->orderBy('name_ar')->get()),
        ]);
    }

    public function store(StoreCityRequest $request): JsonResponse
    {
        $city = City::query()->create($request->validated());

        return response()->json(['city' => new CityResource($city)], 201);
    }

    public function update(UpdateCityRequest $request, City $city): JsonResponse
    {
        $city->update($request->validated());

        return response()->json(['city' => new CityResource($city)]);
    }

    public function destroy(City $city): JsonResponse
    {
        $city->delete();

        return response()->json(['message' => 'City deleted successfully.']);
    }
}