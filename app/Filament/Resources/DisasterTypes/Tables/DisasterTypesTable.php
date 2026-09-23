<?php

namespace App\Filament\Resources\DisasterTypes\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DisasterTypesTable
{
    // These 7 keys are referenced directly by string throughout the real
    // alert logic (EmscEarthquakeProcessor, WeatherAlertService) - deleting
    // one wouldn't crash anything, it would just silently disable that
    // whole category of alerts nationwide, forever, with no error. Never
    // deletable from the panel. A future new type (added after these 7)
    // isn't wired into any real logic yet, so it's safe to allow deleting it.
    private const PROTECTED_KEYS = [
        'earthquake', 'flash_flood', 'flood', 'severe_storm',
        'coastal_storm', 'tsunami', 'national_event',
    ];

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->searchable(),
                TextColumn::make('name_ar')
                    ->searchable(),
                TextColumn::make('name_en')
                    ->searchable(),
                TextColumn::make('audio_file_ar')
                    ->searchable(),
                TextColumn::make('audio_file_en')
                    ->searchable(),
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
                    // Deletion cascades to every Alert (and their user_alerts)
                    // ever issued with this disaster type, nationwide - only
                    // ever offered for a type added after the original 7,
                    // only to a super_admin (a plain admin never sees this
                    // button at all), and only after typing its exact name.
                    ->hidden(function ($record): bool {
                        if (in_array($record->key, self::PROTECTED_KEYS, true)) {
                            return true;
                        }

                        return ! (bool) Filament::auth()->user()?->isSuperAdmin();
                    })
                    ->modalHeading(fn ($record): string => "حذف نوع كارثة: {$record->name_ar}")
                    ->modalDescription('هذا الإجراء نهائي ولا يمكن التراجع عنه، وسيحذف تلقائياً كل التنبيهات المرتبطة بهذا النوع في كل سوريا. اكتب اسم نوع الكارثة بالضبط للمتابعة.')
                    ->schema([
                        TextInput::make('confirm_name')
                            ->label('اكتب اسم نوع الكارثة للتأكيد')
                            ->required(),
                    ])
                    ->action(function (array $data, $record): void {
                        // Server-side re-check, not just a hidden button -
                        // protects against a plain admin somehow submitting
                        // this action directly.
                        if (in_array($record->key, self::PROTECTED_KEYS, true)
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
                            ->title('تم حذف نوع الكارثة')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
