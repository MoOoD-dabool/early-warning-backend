<?php

namespace App\Filament\Resources\Reports\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'email')
                    ->label('Reported by')
                    ->disabled(),
                Textarea::make('message')
                    ->label('Report content')
                    ->disabled()
                    ->columnSpanFull(),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'in_review' => 'In review',
                        'resolved' => 'Resolved',
                        'rejected' => 'Rejected',
                    ])
                    ->required(),
                // Saving a reply here sends the same email the mobile API's
                // reply endpoint sends (see EditReport::afterSave()).
                Textarea::make('admin_reply')
                    ->label('Reply to user')
                    ->columnSpanFull(),
            ]);
    }
}
