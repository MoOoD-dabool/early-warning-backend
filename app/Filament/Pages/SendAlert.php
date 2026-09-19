<?php

namespace App\Filament\Pages;

use App\Models\Alert;
use App\Models\City;
use App\Models\DisasterType;
use App\Services\AlertDispatchService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SendAlert extends Page
{
    protected string $view = 'filament.pages.send-alert';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static ?string $title = 'Send Alert';

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    // Restricted to super_admin: this sends real push notifications (with a
    // siren) to every device in the target city/cities, so it needs to be
    // limited to the smallest possible set of trusted accounts - same
    // reasoning already applied to AdminResource and ActivityResource.
    public static function canAccess(): bool
    {
        $admin = Filament::auth()->user();

        return (bool) $admin?->isSuperAdmin();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    // Fixed bilingual prefixes so it's always obvious to the end user, from
    // the notification/message text alone, that this alert was manually
    // issued by an administrator and which of the three situations it's
    // for - never left to the admin to remember to type themselves.
    private const CATEGORY_PREFIXES = [
        'simulation' => [
            'ar' => '🧪 اختبار محاكاة من الإدارة: ',
            'en' => '🧪 Admin simulation test: ',
        ],
        'uncaught_disaster' => [
            'ar' => '⚠️ تنبيه إداري: ',
            'en' => '⚠️ Admin alert: ',
        ],
        'national_event' => [
            'ar' => '🚨 تنبيه وطني: ',
            'en' => '🚨 National alert: ',
        ],
    ];

    public function mount(): void
    {
        $this->form->fill([
            'category' => 'uncaught_disaster',
            'scope' => 'city',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Radio::make('category')
                    ->label('نوع التنبيه')
                    ->options([
                        'simulation' => 'محاكاة اختبارية',
                        'uncaught_disaster' => 'كارثة لم يرصدها النظام',
                        'national_event' => 'حدث وطني عام',
                    ])
                    ->required(),
                Select::make('disaster_type_id')
                    ->label('نوع الكارثة')
                    ->options(DisasterType::query()->orderBy('name_ar')->pluck('name_ar', 'id'))
                    ->required()
                    ->searchable(),
                Radio::make('scope')
                    ->label('النطاق')
                    ->options([
                        'city' => 'محافظة محددة',
                        'all' => 'كل سوريا',
                    ])
                    ->required()
                    ->live(),
                Select::make('city_id')
                    ->label('المحافظة')
                    ->options(City::query()->orderBy('name_ar')->pluck('name_ar', 'id'))
                    ->required()
                    ->searchable()
                    ->visible(fn ($get) => $get('scope') === 'city'),
                Textarea::make('message_ar')
                    ->label('نص التنبيه (عربي)')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),
                Textarea::make('message_en')
                    ->label('نص التنبيه (إنجليزي) - اختياري')
                    ->helperText('إذا تُرك فارغاً، سيُستخدم النص العربي بدلاً منه.')
                    ->rows(4)
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function sendAction(): Action
    {
        return Action::make('send')
            ->label('إرسال التنبيه الآن')
            ->color('danger')
            ->icon(Heroicon::OutlinedMegaphone)
            ->requiresConfirmation()
            ->modalHeading('تأكيد إرسال التنبيه')
            ->modalDescription('هذا التنبيه سيصل فوراً لمستخدمين حقيقيين مع تفعيل الصفارة. تأكد من صحة المعلومات قبل الإرسال.')
            ->modalSubmitActionLabel('نعم، أرسل الآن')
            ->action(function () {
                $data = $this->form->getState();

                $cityIds = $data['scope'] === 'all'
                    ? City::query()->pluck('id')->all()
                    : [$data['city_id']];

                $prefix = self::CATEGORY_PREFIXES[$data['category']];
                $messageAr = $prefix['ar'].$data['message_ar'];
                $messageEn = $prefix['en'].(filled($data['message_en']) ? $data['message_en'] : $data['message_ar']);

                $dispatchService = app(AlertDispatchService::class);
                $notifiedUsers = 0;
                $failedPushes = 0;

                foreach ($cityIds as $cityId) {
                    $alert = Alert::create([
                        'disaster_type_id' => $data['disaster_type_id'],
                        'city_id' => $cityId,
                        'severity' => 'critical',
                        'trigger_siren' => true,
                        'message_ar' => $messageAr,
                        'message_en' => $messageEn,
                        'issued_at' => now(),
                    ]);

                    $notifiedUsers += $dispatchService->dispatch($alert);
                    $failedPushes += $dispatchService->lastPushFailures;
                }

                if ($failedPushes > 0) {
                    Notification::make()
                        ->title('تم حفظ التنبيه، لكن تعذّر إرسال الإشعار الفوري لبعض الأجهزة')
                        ->body(sprintf('تم إنشاء %d تنبيه وظهر داخل التطبيق لـ %d مستخدم، لكن فشل الإشعار الفوري لـ %d جهاز (غالباً انقطاع اتصال). لا تُعد الإرسال حتى لا يتكرر التنبيه داخل التطبيق.', count($cityIds), $notifiedUsers, $failedPushes))
                        ->warning()
                        ->persistent()
                        ->send();
                } else {
                    Notification::make()
                        ->title('تم إرسال التنبيه')
                        ->body(sprintf('تم إنشاء %d تنبيه ووصل إشعار حقيقي لـ %d مستخدم.', count($cityIds), $notifiedUsers))
                        ->success()
                        ->send();
                }

                $this->form->fill([
                    'category' => 'uncaught_disaster',
                    'scope' => 'city',
                ]);
            });
    }
}
