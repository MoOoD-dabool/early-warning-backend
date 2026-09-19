<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReplyReportRequest;
use App\Http\Resources\ReportResource;
use App\Mail\ReportReplyMail;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AdminReportController extends Controller
{
    public function index(): JsonResponse
    {
        $reports = Report::query()
            ->with('user')
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

    public function show(Report $report): JsonResponse
    {
        return response()->json([
            'report' => new ReportResource($report->load('user')),
        ]);
    }

    public function reply(ReplyReportRequest $request, Report $report): JsonResponse
    {
        $report->update($request->validated());
        $report = $report->fresh('user');

        try {
            Mail::to($report->user->email)->send(new ReportReplyMail($report));
        } catch (\Throwable $e) {
            // Don't let a mail failure block the reply from being saved.
            Log::error('Failed to send report-reply email: '.$e->getMessage());
        }

        return response()->json([
            'message' => 'Reply sent successfully.',
            'report' => new ReportResource($report),
        ]);
    }
}