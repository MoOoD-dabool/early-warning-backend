<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\FeltReportResource;
use App\Models\FeltReport;
use Illuminate\Http\JsonResponse;

class AdminFeltReportController extends Controller
{
    public function index(): JsonResponse
    {
        $feltReports = FeltReport::query()
            ->with(['user', 'city', 'earthquakeEvent'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'felt_reports' => FeltReportResource::collection($feltReports),
            'meta' => [
                'current_page' => $feltReports->currentPage(),
                'last_page' => $feltReports->lastPage(),
                'total' => $feltReports->total(),
            ],
        ]);
    }

    public function show(FeltReport $feltReport): JsonResponse
    {
        return response()->json([
            'felt_report' => new FeltReportResource($feltReport->load(['user', 'city', 'earthquakeEvent'])),
        ]);
    }
}
