<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Models\OrderItem;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Item Pesanan';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('product_name')
                    ->label('Nama Produk')
                    ->required()
                    ->maxLength(255),
                TextInput::make('variant_name')
                    ->label('Nama Varian')
                    ->maxLength(255),
                TextInput::make('quantity')
                    ->label('Jumlah')
                    ->numeric()
                    ->required(),
                TextInput::make('price')
                    ->label('Harga Satuan')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),
                TextInput::make('greeting_card')
                    ->label('Ucapan Kartu (Maks 40 Karakter)')
                    ->maxLength(40),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_name')
            ->columns([
                TextColumn::make('product_name')
                    ->label('Produk')
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('variant_name')
                    ->label('Varian')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('quantity')
                    ->label('Qty')
                    ->alignCenter(),
                TextColumn::make('price')
                    ->label('Harga Satuan')
                    ->formatStateUsing(fn ($state) => 'Rp' . number_format($state, 0, ',', '.')),
                TextColumn::make('line_total')
                    ->label('Subtotal')
                    ->state(fn (OrderItem $record) => $record->price * $record->quantity)
                    ->formatStateUsing(fn ($state) => 'Rp' . number_format($state, 0, ',', '.'))
                    ->weight('bold'),
                TextColumn::make('greeting_card')
                    ->label('Ucapan Kartu')
                    ->placeholder('-')
                    ->italic(),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
