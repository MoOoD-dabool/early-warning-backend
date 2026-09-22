<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    /**
     * Bilingual labels for the fixed 4-value status set used by the Filament
     * admin panel's ReportForm/ReportsTable — kept here (not a config file)
     * since it's small, fixed, and only ever read by this one Resource.
     */
    private const STATUS_LABELS = [
        'pending' => ['ar' => 'قيد الانتظار', 'en' => 'Pending'],
        'in_review' => ['ar' => 'قيد المراجعة', 'en' => 'In Review'],
        'resolved' => ['ar' => 'تم الحل', 'en' => 'Resolved'],
        'rejected' => ['ar' => 'مرفوض', 'en' => 'Rejected'],
    ];

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'message' => $this->message,
            'status' => $this->status,
            'status_label' => self::STATUS_LABELS[$this->status] ?? [
                'ar' => $this->status,
                'en' => $this->status,
            ],
            'admin_reply' => $this->admin_reply,
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
