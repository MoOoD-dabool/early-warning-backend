<?php

namespace App\Filament\Resources\Cities\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CitiesTable
{
    // The 16 real Syrian governorates this app is built around (Flutter's
    // own hardcoded city list depends on this exact code/order too) - never
    // deletable from the panel, no matter what. Any real fix to one of
    // these goes through the backend directly (tinker/migration), not a
    // panel button.
    private const PROTECTED_CODES = [
        'SY001', 'SY002', 'SY003', 'SY004', 'SY005', 'SY006', 'SY007', 'SY008',
        'SY009', 'SY010', 'SY011', 'SY012', 'SY013', 'SY014', 'SY015', 'SY016',
    ];

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name_ar')
                    ->searchable(),
                TextColumn::make('name_en')
                    ->searchable(),
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('latitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('longitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    // Deletion cascades to users/alerts/earthquake_events/
                    // weather_readings/relief_teams/relief_requests/felt_reports
                    // for this city - only ever offered for a city added
                    // after the original 16, only to a super_admin (a plain
                    // admin never sees this button at all), and only after
                    // typing its exact name, so it can't happen by a stray
                    // click.
                    ->hidden(function ($record): bool {
                        if (in_array($record->code, self::PROTECTED_CODES, true)) {
                            return true;
                        }

                        return ! (bool) Filament::auth()->user()?->isSuperAdmin();
                    })
                    ->modalHeading(fn ($record): string => "حذف محافظة: {$record->name_ar}")
                    ->modalDescription('هذا الإجراء نهائي ولا يمكن التراجع عنه، وسيحذف تلقائياً كل المستخدمين والتنبيهات وسجلات الزلازل والطقس المرتبطة بهذه المحافظة. اكتب اسم المحافظة بالضبط للمتابعة.')
                    ->schema([
                        TextInput::make('confirm_name')
                            ->label('اكتب اسم المحافظة للتأكيد')
                            ->required(),
                    ])
                    ->action(function (array $data, $record): void {
                        // Server-side re-check, not just a hidden button -
                        // protects against a plain admin somehow submitting
                        // this action directly.
                        if (in_array($record->code, self::PROTECTED_CODES, true)
                            || ! (bool) Filament::auth()->user()?->isSuperAdmin()) {
                            Notification::make()
                                ->title('غير مصرح لك بحذف هذا العنصر')
                                ->danger()
                                ->send();

                            return;
                        }

                        if (trim($data['confirm_name']) !== $record->name_ar) {
                            Notification::make()
                                ->title('الاسم المكتوب غير مطابق - لم يتم الحذف')
                                ->danger()
                                ->send();

                            return;
                        }

                        $record->delete();

                        Notification::make()
                            ->title('تم حذف المحافظة')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
