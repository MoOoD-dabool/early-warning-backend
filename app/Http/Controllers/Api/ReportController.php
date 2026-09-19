<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreReportRequest;
use App\Http\Resources\ReportResource;
use App\Mail\NewReportNotificationMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $reports = $request->user()
            ->reports()
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'reports' => ReportResource::collection($reports),
            'meta' => [
                'current_page' => $reports->currentPage(),
                'last_page' => $reports->lastPage(),
                'total' => $reports->total(),
            ],
        ]);
    }

    public function store(StoreReportRequest $request): JsonResponse
    {
        $report = $request->user()->reports()->create([
            'message' => $request->validated()['message'],
            'status' => 'pending',
        ]);

        $notificationEmail = config('reports.notification_email');

        if ($notificationEmail) {
            try {
                Mail::to($notificationEmail)->send(new NewReportNotificationMail($report->load('user')));
            } catch (\Throwable $e) {
                // Don't let a mail failure block the report submission itself.
                Log::error('Failed to send new-report notification email: '.$e->getMessage());
            }
        }

        return response()->json([
            'message' => __('messages.reports.submitted'),
            'report' => new ReportResource($report),
        ], 201);
    }

    public function show(Request $request, int $report): JsonResponse
    {
        $reportModel = $request->user()->reports()->findOrFail($report);

        return response()->json([
            'report' => new ReportResource($reportModel),
        ]);
    }
}