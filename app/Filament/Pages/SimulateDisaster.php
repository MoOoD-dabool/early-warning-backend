<?php

namespace App\Filament\Pages;

use App\Models\Alert;
use App\Models\City;
use App\Models\WeatherReading;
use App\Services\EmscEarthquakeProcessor;
use App\Services\WeatherAlertService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

/**
 * A realistic disaster simulator for super_admin: instead of hand-picking a
 * severity/siren like the Send Alert page does, this feeds real physical
 * values (magnitude/depth, or wind/pressure/rain) into the exact same
 * services the real EMSC listener and weather scheduler use
 * (EmscEarthquakeProcessor::handle(), WeatherAlertService::evaluate()) —
 * the real thresholds already in config/earthquake.php and
 * config/weather_alerts.php decide the outcome, exactly as they would for
 * a genuine event. This makes it a true "what would really happen" tool,
 * previously only reachable via SSH/Railway console (earthquake:simulate,
 * weather:simulate-alert), not from anywhere in the admin panel itself.
 *
 * "flood" (river/dam based) is deliberately NOT offered here — it depends
 * on live river-discharge data fetched from Open-Meteo at evaluation time,
 * not on any value this form could collect, so it can't be meaningfully
 * simulated this way (see WeatherAlertService::checkFlood()).
 *
 * Like Send Alert, this creates real Alert rows and sends real push
 * notifications to real users if the simulated values cross a real
 * threshold — restricted to super_admin for the same reason.
 */
class SimulateDisaster extends Page
{
    protected string $view = 'filament.pages.simulate-disaster';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    protected static ?string $title = 'محاكاة كارثة واقعية';

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        $admin = Filament::auth()->user();

        return (bool) $admin?->isSuperAdmin();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function mount(): void
    {
        $this->form->fill([
            'mode' => 'earthquake',
            'depth_km' => 10,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Radio::make('mode')
                    ->label('نوع المحاكاة')
                    ->options([
                        'earthquake' => 'زلزال (يشمل التسونامي تلقائياً إن انطبق شرطه)',
                        'weather' => 'طقس خطير (سيول أو عواصف)',
                    ])
                    ->required()
                    ->live(),

                Select::make('city_id')
                    ->label('المحافظة (موقع الحدث)')
                    ->options(City::query()->orderBy('name_ar')->pluck('name_ar', 'id'))
                    ->required()
                    ->searchable(),

                Section::make('بيانات الزلزال')
                    ->visible(fn ($get) => $get('mode') === 'earthquake')
                    ->columns(2)
                    ->components([
                        TextInput::make('magnitude')
                            ->label('القوة (Magnitude)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10)
                            ->step(0.1)
                            ->default(4.5)
                            ->required(fn ($get) => $get('mode') === 'earthquake'),
                        TextInput::make('depth_km')
                            ->label('العمق (كم)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(700)
                            ->required(fn ($get) => $get('mode') === 'earthquake'),
                    ]),

                Section::make('بيانات الطقس')
                    ->description('ملاحظة: تنبيهات "الفيضان" (السدود/الأنهار) تعتمد على بيانات تدفق نهري حقيقية تُجلب لحظياً من الإنترنت، وليس على القيم المدخلة هنا — لذلك لا يمكن محاكاتها من هذه الأداة. الحقول أدناه تحاكي السيول والعواصف فقط.')
                    ->visible(fn ($get) => $get('mode') === 'weather')
                    ->columns(2)
                    ->components([
                        Select::make('weather_code')
                            ->label('حالة الطقس (WMO)')
                            ->options([
                                0 => 'صافٍ / بدون هطول',
                                65 => 'مطر غزير (65)',
                                82 => 'زخات مطر عنيفة (82)',
                                95 => 'عاصفة رعدية (95)',
                                96 => 'عاصفة رعدية + برد خفيف (96)',
                                99 => 'عاصفة رعدية + برد شديد (99)',
                            ])
                            ->default(0)
                            ->required(fn ($get) => $get('mode') === 'weather')
                            ->helperText('السيول تحتاج أحد رموز المطر/العاصفة، وتنطبق فقط على المحافظات ذات تضاريس الأودية.'),
                        TextInput::make('wind_speed_kmh')
                            ->label('سرعة الرياح (كم/س)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(300)
                            ->default(0)
                            ->required(fn ($get) => $get('mode') === 'weather'),
                        TextInput::make('pressure_msl')
                            ->label('الضغط الجوي (hPa)')
                            ->numeric()
                            ->minValue(900)
                            ->maxValue(1050)
                            ->default(1013)
                            ->required(fn ($get) => $get('mode') === 'weather'),
                        TextInput::make('precipitation_mm')
                            ->label('الهطول (ملم/ساعة)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(500)
                            ->default(0)
                            ->required(fn ($get) => $get('mode') === 'weather')
                            ->helperText('العواصف تحتاج رياح + ضغط منخفض + هطول معاً (نفس شرط النظام الحقيقي).'),
                        TextInput::make('temperature_c')
                            ->label('الحرارة (°م) — تُسجَّل فقط، لا تدخل في شرط أي تنبيه')
                            ->numeric()
                            ->minValue(-50)
                            ->maxValue(60)
                            ->default(20)
                            ->required(fn ($get) => $get('mode') === 'weather'),
                        TextInput::make('humidity_percent')
                            ->label('الرطوبة (%) — تُسجَّل فقط، لا تدخل في شرط أي تنبيه')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(50)
                            ->required(fn ($get) => $get('mode') === 'weather'),
                    ]),
            ])
            ->statePath('data');
    }

    public function simulateAction(): Action
    {
        return Action::make('simulate')
            ->label('تشغيل المحاكاة الآن')
            ->color('danger')
            ->icon(Heroicon::OutlinedBeaker)
            ->requiresConfirmation()
            ->modalHeading('تأكيد تشغيل المحاكاة')
            ->modalDescription('إذا تجاوزت القيم عتبة الإنذار الحقيقية، سيصل تنبيه حقيقي (وربما صفارة) لمستخدمين حقيقيين في هذه المحافظة، تماماً كما لو كان الحدث حقيقياً.')
            ->modalSubmitActionLabel('نعم، شغّل المحاكاة')
            ->action(function () {
                $data = $this->form->getState();
                $city = City::query()->find($data['city_id']);

                if (! $city) {
                    Notification::make()->title('لم يتم إيجاد المحافظة')->danger()->send();

                    return;
                }

                $existingAlertIds = Alert::query()->pluck('id');

                if ($data['mode'] === 'earthquake') {
                    $properties = [
                        'unid' => 'ADMIN_SIM_'.now()->format('YmdHis').'_'.Str::random(4),
                        'mag' => (float) $data['magnitude'],
                        'lat' => (float) $city->latitude,
                        'lon' => (float) $city->longitude,
                        'depth' => (float) $data['depth_km'],
                        'time' => now()->toIso8601String(),
                        'flynn_region' => 'ADMIN SIMULATION - '.$city->name_en,
                    ];

                    app(EmscEarthquakeProcessor::class)->handle($properties);
                } else {
                    $reading = WeatherReading::query()->create([
                        'city_id' => $city->id,
                        'temperature_c' => (float) $data['temperature_c'],
                        'humidity_percent' => (int) $data['humidity_percent'],
                        'wind_speed_kmh' => (float) $data['wind_speed_kmh'],
                        'pressure_msl' => (float) $data['pressure_msl'],
                        'precipitation_mm' => (float) $data['precipitation_mm'],
                        'weather_code' => (int) $data['weather_code'],
                        'fetched_at' => now(),
                    ]);
                    $reading->setRelation('city', $city);

                    app(WeatherAlertService::class)->evaluate($reading);
                }

                $newAlerts = Alert::query()
                    ->whereNotIn('id', $existingAlertIds)
                    ->with(['city', 'disasterType'])
                    ->get();

                if ($newAlerts->isEmpty()) {
                    Notification::make()
                        ->title('لم يُنشأ أي تنبيه')
                        ->body('القيم المدخلة لم تتجاوز العتبة الحقيقية لأي نوع كارثة — تماماً كما سيتصرف النظام مع حدث حقيقي بهذه القوة.')
                        ->warning()
                        ->send();

                    return;
                }

                $summary = $newAlerts
                    ->map(fn (Alert $a) => sprintf(
                        '%s — %s (%s%s)',
                        $a->city?->name_ar,
                        $a->disasterType?->name_ar,
                        $a->severity,
                        $a->trigger_siren ? '، صفارة' : ''
                    ))
                    ->join('؛ ');

                Notification::make()
                    ->title(sprintf('تم إنشاء %d تنبيه حقيقي', $newAlerts->count()))
                    ->body($summary)
                    ->success()
                    ->send();
            });
    }
}
