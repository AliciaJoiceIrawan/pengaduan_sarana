<?php

namespace App\Filament\Resources\Pengaduans\Tables;

use App\Models\Pengaduan;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class PengaduansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.nama')
                    ->searchable(),
                TextColumn::make('category.nis')
                    ->label('NIS'),
                TextColumn::make('category.nama')
                    ->searchable(),
                TextColumn::make('lokasi')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'Menunggu' => 'warning',
                        'Proses' => 'info',
                        default => 'success',
                    }),
                TextColumn::make('feedback')
                    ->limit(30),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'nama'),
                Filter::make('tanggal')
                    ->schema([DatePicker::make('tanggal')])
                    ->query(fn (Builder $query, array $data) => $query->when($data['tanggal'], fn ($q, $tgl) => $q->whereDate('created_at', $tgl))),
                SelectFilter::make('bulan')
                    ->options([1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'])
                    ->query(fn (Builder $query, array $data) => $query->when($data['value'], fn ($q, $bln) => $q->whereMonth('created_at', $bln))),
                Filter::make('nis')
                    ->schema([TextInput::make('nis')->label('NIS')])
                    ->query(fn (Builder $query, array $data) => $query->when($data['nis'], fn ($q, $nis) => $q->whereRelation('user', 'nis', $nis))),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('tanggapi')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->visible(fn () => auth()->user()->isAdmin())
                    ->fillForm(fn (Pengaduan $record) => ['status' => $record->status, 'feedback' => $record->feedback])
                    ->schema([
                        Select::make('status')
                            ->options(['Menunggu' => 'Menunggu', 'Proses' => 'Proses', 'Selesai' => 'Selesai'])
                            ->required(),
                        Textarea::make('feedback')->label('Umpan balik'),
                    ])
                    ->action(function (Pengaduan $record, array $data) {
                        $record->update($data);
                        Notification::make()->title('Tanggapan disimpan')->success()->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
