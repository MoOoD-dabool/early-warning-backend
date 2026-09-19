<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAlertRequest;
use App\Http\Requests\Admin\UpdateAlertRequest;
use App\Http\Resources\AlertResource;
use App\Models\Alert;
use App\Services\AlertDispatchService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class AdminAlertController extends Controller
{
    public function __construct(private readonly AlertDispatchService $dispatchService)
    {
    }

    public function index(): JsonResponse
    {
        $alerts = Alert::query()
            ->with(['disasterType', 'city', 'earthquakeEvent'])
            ->orderByDesc('issued_at')
            ->paginate(20);

        return response()->json([
            'alerts' => AlertResource::collection($alerts),
            'meta' => [
                'current_page' => $alerts->currentPage(),
                'last_page' => $alerts->lastPage(),
                'total' => $alerts->total(),
            ],
        ]);
    }

    /**
     * Manually create and immediately broadcast an alert to every user in
     * the target city.
     */
    public function store(StoreAlertRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['issued_at'] = $data['issued_at'] ?? Carbon::now();

        $alert = Alert::query()->create($data);

        $recipients = $this->dispatchService->dispatch($alert);

        return response()->json([
            'message' => "Alert created and sent to {$recipients} user(s).",
            'alert' => new AlertResource($alert->load(['disasterType', 'city', 'earthquakeEvent'])),
        ], 201);
    }

    public function update(UpdateAlertRequest $request, Alert $alert): JsonResponse
    {
        $alert->update($request->validated());

        return response()->json([
            'alert' => new AlertResource($alert->fresh(['disasterType', 'city', 'earthquakeEvent'])),
        ]);
    }

    public function destroy(Alert $alert): JsonResponse
    {
        $alert->delete();

        return response()->json(['message' => 'Alert deleted successfully.']);
    }
}
