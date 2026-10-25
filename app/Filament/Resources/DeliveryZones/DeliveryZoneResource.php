<?php

namespace App\Filament\Resources\DeliveryZones;

use App\Filament\Resources\DeliveryZones\Pages\ManageDeliveryZones;
use App\Models\DeliveryZone;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DeliveryZoneResource extends Resource
{
    protected static ?string $model = DeliveryZone::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static \UnitEnum|string|null $navigationGroup = 'Pengaturan';

    protected static ?string $modelLabel = 'Zona Pengantaran';

    protected static ?string $pluralModelLabel = 'Zona & Ongkir Delivery';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Wilayah / Zona')
                    ->placeholder('Contoh: Makassar Kota & Panakkukang')
                    ->required()
                    ->maxLength(255),

                TextInput::make('fee')
                    ->label('Tarif Ongkos Kirim (Rupiah)')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),

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
                    ->label('Wilayah / Zona Pengantaran')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('fee')
                    ->label('Tarif Ongkir')
                    ->formatStateUsing(fn ($state) => 'Rp' . number_format($state, 0, ',', '.'))
                    ->weight('bold')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
            ])
            ->defaultSort('fee')
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
            'index' => ManageDeliveryZones::route('/'),
        ];
    }
}
