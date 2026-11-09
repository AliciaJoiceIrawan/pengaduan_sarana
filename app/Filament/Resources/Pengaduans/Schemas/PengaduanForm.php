<?php

namespace App\Filament\Resources\Pengaduans\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PengaduanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->default(auth()->id()),
                Select::make('category_id')
                    ->relationship('category', 'nama')
                    ->required(),
                TextInput::make('lokasi')
                    ->required(),
                Textarea::make('deskripsi')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
