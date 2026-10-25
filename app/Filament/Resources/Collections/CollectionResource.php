<?php

namespace App\Filament\Resources\Collections;

use App\Filament\Resources\Collections\Pages\ManageCollections;
use App\Models\Collection;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CollectionResource extends Resource
{
    protected static ?string $model = Collection::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static \UnitEnum|string|null $navigationGroup = 'Katalog Produk';

    protected static ?string $modelLabel = 'Koleksi';

    protected static ?string $pluralModelLabel = 'Koleksi & Kategori';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Kategori / Koleksi')
                    ->required()
                    ->maxLength(255),

                TextInput::make('slug')
                    ->label('Slug URL')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Select::make('parent_id')
                    ->label('Induk Koleksi (Opsional)')
                    ->relationship('parent', 'name')
                    ->placeholder('-- Tidak ada (Kategori Utama) --'),

                TextInput::make('sort_order')
                    ->label('Urutan Tampilan')
                    ->numeric()
                    ->default(0),

                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Koleksi')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->color('gray'),

                TextColumn::make('parent.name')
                    ->label('Kategori Induk')
                    ->badge()
                    ->color('gray')
                    ->placeholder('-'),

                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->alignCenter()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCollections::route('/'),
        ];
    }
}
