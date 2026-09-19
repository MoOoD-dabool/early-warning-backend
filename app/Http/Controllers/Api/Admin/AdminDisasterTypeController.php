<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDisasterTypeRequest;
use App\Http\Requests\Admin\UpdateDisasterTypeRequest;
use App\Http\Resources\DisasterTypeResource;
use App\Models\DisasterType;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class AdminDisasterTypeController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'disaster_types' => DisasterTypeResource::collection(DisasterType::query()->orderBy('name_ar')->get()),
        ]);
    }

    public function store(StoreDisasterTypeRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('audio_file_ar')) {
            $data['audio_file_ar'] = $request->file('audio_file_ar')->store('disaster-audio', 'public');
        }
        if ($request->hasFile('audio_file_en')) {
            $data['audio_file_en'] = $request->file('audio_file_en')->store('disaster-audio', 'public');
        }

        $disasterType = DisasterType::query()->create($data);

        return response()->json(['disaster_type' => new DisasterTypeResource($disasterType)], 201);
    }

    public function update(UpdateDisasterTypeRequest $request, DisasterType $disasterType): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('audio_file_ar')) {
            if ($disasterType->audio_file_ar) {
                Storage::disk('public')->delete($disasterType->audio_file_ar);
            }
            $data['audio_file_ar'] = $request->file('audio_file_ar')->store('disaster-audio', 'public');
        }
        if ($request->hasFile('audio_file_en')) {
            if ($disasterType->audio_file_en) {
                Storage::disk('public')->delete($disasterType->audio_file_en);
            }
            $data['audio_file_en'] = $request->file('audio_file_en')->store('disaster-audio', 'public');
        }

        $disasterType->update($data);

        return response()->json(['disaster_type' => new DisasterTypeResource($disasterType)]);
    }

    public function destroy(DisasterType $disasterType): JsonResponse
    {
        $disasterType->delete();

        return response()->json(['message' => 'Disaster type deleted successfully.']);
    }
}