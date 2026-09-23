<?php

namespace App\Filament\Resources\DisasterTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class DisasterTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->required()
                    ->unique(table: 'disaster_types', column: 'key', ignoreRecord: true)
                    ->validationMessages([
                        'unique' => 'هذا المفتاح مستخدم من قبل لنوع كارثة آخر.',
                    ])
                    ->helperText('معرّف برمجي ثابت، بالإنجليزية بدون مسافات (مثال: wildfire). المفاتيح السبعة الأساسية محجوزة: earthquake, flash_flood, flood, severe_storm, coastal_storm, tsunami, national_event.'),
                TextInput::make('name_ar')
                    ->required(),
                TextInput::make('name_en')
                    ->required(),
                Textarea::make('instructions_before_ar')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('instructions_before_en')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('instructions_during_ar')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('instructions_during_en')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('instructions_after_ar')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('instructions_after_en')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('audio_file_ar'),
                TextInput::make('audio_file_en'),
            ]);
    }
}
