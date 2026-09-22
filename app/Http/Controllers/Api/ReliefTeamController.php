<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReliefTeamResource;
use App\Models\ReliefTeam;
use Illuminate\Http\JsonResponse;

class ReliefTeamController extends Controller
{
    public function index(): JsonResponse
    {
        $teams = ReliefTeam::query()
            ->with(['city', 'disasterType'])
            ->whereIn('status', config('relief_teams.user_visible_statuses'))
            ->orderBy('id')
            ->get();

        return response()->json([
            'relief_teams' => ReliefTeamResource::collection($teams),
        ]);
    }
}
