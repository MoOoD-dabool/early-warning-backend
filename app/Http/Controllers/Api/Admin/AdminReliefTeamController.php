<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreReliefTeamRequest;
use App\Http\Requests\Admin\UpdateReliefTeamRequest;
use App\Http\Resources\ReliefTeamResource;
use App\Models\ReliefTeam;
use Illuminate\Http\JsonResponse;

class AdminReliefTeamController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'relief_teams' => ReliefTeamResource::collection(
                ReliefTeam::query()->with(['city', 'disasterType'])->orderBy('id')->get()
            ),
        ]);
    }

    public function store(StoreReliefTeamRequest $request): JsonResponse
    {
        $team = ReliefTeam::query()->create($request->validated());

        return response()->json(['relief_team' => new ReliefTeamResource($team->load(['city', 'disasterType']))], 201);
    }

    public function update(UpdateReliefTeamRequest $request, ReliefTeam $reliefTeam): JsonResponse
    {
        $reliefTeam->update($request->validated());

        return response()->json(['relief_team' => new ReliefTeamResource($reliefTeam->fresh(['city', 'disasterType']))]);
    }

    public function destroy(ReliefTeam $reliefTeam): JsonResponse
    {
        $reliefTeam->delete();

        return response()->json(['message' => 'Relief team deleted successfully.']);
    }
}
