<?php

namespace App\Filament\Resources\Cities\Schemas;

use App\Models\City;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name_ar')
                    ->required(),
                TextInput::make('name_en')
                    ->required(),
                TextInput::make('code')
                    ->required()
                    ->unique(table: 'cities', column: 'code', ignoreRecord: true)
                    ->validationMessages([
                        'unique' => 'هذا الكود مستخدم من قبل لمحافظة أخرى.',
                    ])
                    ->placeholder(fn (): string => 'مثال: '.self::nextSuggestedCode())
                    ->helperText('لازم يكون فريداً. للمحافظات الأساسية الستة عشر تُستخدم SY001–SY016 - أي محافظة جديدة يجب أن تستخدم كوداً مختلفاً عن هذا النطاق.'),
                TextInput::make('latitude')
                    ->required()
                    ->numeric(),
                TextInput::make('longitude')
                    ->required()
                    ->numeric(),
            ]);
    }

    // Just a suggestion shown as placeholder text (never auto-filled, the
    // admin still types the real code) - the next number after the highest
    // existing "SYxxx"-shaped code, so a newly added city naturally gets a
    // code outside the protected SY001-SY016 range without needing to
    // remember that range by hand.
    private static function nextSuggestedCode(): string
    {
        $highest = City::query()
            ->pluck('code')
            ->map(fn (string $code): int => preg_match('/^SY(\d+)$/', $code, $m) ? (int) $m[1] : 0)
            ->max() ?? 16;

        return 'SY'.str_pad((string) ($highest + 1), 3, '0', STR_PAD_LEFT);
    }
}
