<?php

namespace App\Filament\Resources\Reports\Pages;

use App\Filament\Resources\Reports\ReportResource;
use App\Mail\ReportReplyMail;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EditReport extends EditRecord
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * Mirrors AdminReportController::reply() from the mobile API: saving a
     * non-empty admin_reply here emails the user the same way replying via
     * the API would. A mail failure doesn't undo the save, same as there.
     */
    protected function afterSave(): void
    {
        if (! $this->record->wasChanged('admin_reply') || blank($this->record->admin_reply)) {
            return;
        }

        $record = $this->record->fresh('user');
        if ($record->user === null) {
            // The reporting user's account was deleted since they filed this
            // report - there's no one left to email.
            return;
        }

        try {
            Mail::to($record->user->email)->send(new ReportReplyMail($record));
        } catch (\Throwable $e) {
            Log::error('Failed to send report-reply email from admin panel: '.$e->getMessage());

            // The reply itself IS saved (and the user sees it inside the app);
            // only the courtesy email failed. Say so instead of staying silent.
            Notification::make()
                ->warning()
                ->title('تم حفظ الرد لكن تعذّر إرسال البريد الإلكتروني')
                ->body('الرد ظاهر للمستخدم داخل التطبيق. لم يصله بريد إلكتروني (راجع إعداد البريد).')
                ->persistent()
                ->send();
        }
    }
}
