<?php

namespace App\Filament\Resources\Pengaduans;

use App\Filament\Resources\Pengaduans\Pages\CreatePengaduan;
use App\Filament\Resources\Pengaduans\Pages\EditPengaduan;
use App\Filament\Resources\Pengaduans\Pages\ListPengaduans;
use App\Models\Pengaduan;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Illuminate\Database\Eloquent\Builder;

class PengaduanResource extends Resource
{
    protected static ?string $model = Pengaduan::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';
    protected static ?string $navigationLabel = 'Pengaduan Sarana';
    protected static ?string $modelLabel = 'Pengaduan';

    // Filter data: Siswa hanya melihat histori miliknya, Admin melihat keseluruhan
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (request()->is('student*')) {
            $query->where('user_id', auth()->id());
        }

        return $query;
    }

    // Form: Siswa mengisi aspirasi, Admin mengisi umpan balik & status
    public static function form(Schema $schema): Schema
    {
        $isAdminPanel = request()->is('admin*');

        return $schema
            ->components([
                Hidden::make('user_id')
                    ->default(fn () => auth()->id())
                    ->required(),

                Select::make('category_id')
                    ->relationship('category', 'nama')
                    ->label('Kategori Sarana Prasarana')
                    ->required()
                    ->native(false)
                    ->placeholder('Pilih kategori fasilitas...'),

                Textarea::make('deskripsi')
                    ->label('Form Aspirasi / Pengaduan Kerusakan')
                    ->placeholder('Tuliskan detail keluhan atau kerusakan sarana prasarana sekolah...')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),

                // BAGIAN KHUSUS ADMIN (Sesuai ERD Soal: Menunggu, Proses, Selesai)
                Select::make('status')
                    ->label('Status Penyelesaian')
                    ->options([
                        'Menunggu' => 'Menunggu',
                        'Proses' => 'Proses',
                        'Selesai' => 'Selesai',
                    ])
                    ->default('Menunggu')
                    ->required()
                    ->native(false)
                    ->visible(fn () => $isAdminPanel),

                TextInput::make('progres')
                    ->label('Progres Perbaikan')
                    ->placeholder('Contoh: Menunggu sparepart / Sedang dikerjakan teknisi')
                    ->visible(fn () => $isAdminPanel),

                Textarea::make('umpan_balik')
                    ->label('Umpan Balik / Tanggapan Admin')
                    ->placeholder('Tuliskan umpan balik atau tanggapan untuk siswa...')
                    ->rows(3)
                    ->columnSpanFull()
                    ->visible(fn () => $isAdminPanel),
            ]);
    }

    // Tabel: Menampilkan histori, status, progres, dan umpan balik
    public static function table(Table $table): Table
    {
        $isAdminPanel = request()->is('admin*');

        return $table
            ->columns([
                TextColumn::make('user.nama')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable()
                    ->visible(fn () => $isAdminPanel),

                TextColumn::make('user.kelas')
                    ->label('Kelas')
                    ->searchable()
                    ->visible(fn () => $isAdminPanel),

                TextColumn::make('category.nama')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('deskripsi')
                    ->label('Aspirasi / Pengaduan')
                    ->limit(40)
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status Penyelesaian')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Menunggu' => 'gray',
                        'Proses' => 'warning',
                        'Selesai' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('progres')
                    ->label('Progres Perbaikan')
                    ->placeholder('Menunggu penanganan'),

                TextColumn::make('umpan_balik')
                    ->label('Umpan Balik Admin')
                    ->placeholder('Belum ada umpan balik'),

                TextColumn::make('created_at')
                    ->label('Tanggal Pengaduan')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                ViewAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => $isAdminPanel),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPengaduans::route('/'),
            'create' => CreatePengaduan::route('/create'),
            'edit' => EditPengaduan::route('/edit/{record}'),
        ];
    }
}