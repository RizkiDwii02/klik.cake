<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Models\Variant;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $title = 'Varian Produk (Ukuran, Rasa, Harga & Stok)';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Varian')
                    ->placeholder('Contoh: 16 cm atau Cokelat Keju')
                    ->required()
                    ->maxLength(255),

                TextInput::make('size')
                    ->label('Ukuran (Opsional)')
                    ->placeholder('Contoh: 16 cm'),

                TextInput::make('flavor')
                    ->label('Rasa (Opsional)')
                    ->placeholder('Contoh: Vanila'),

                TextInput::make('sku')
                    ->label('SKU')
                    ->required()
                    ->maxLength(100),

                TextInput::make('price')
                    ->label('Harga (Rupiah)')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),

                TextInput::make('stock')
                    ->label('Stok Tersedia')
                    ->numeric()
                    ->default(0)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Varian')
                    ->weight('bold'),

                TextColumn::make('sku')
                    ->label('SKU')
                    ->fontFamily('mono')
                    ->color('gray'),

                TextColumn::make('price')
                    ->label('Harga')
                    ->formatStateUsing(fn ($state) => 'Rp' . number_format($state, 0, ',', '.'))
                    ->weight('bold')
                    ->sortable(),

                TextColumn::make('stock')
                    ->label('Stok')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state === 0 => 'danger',
                        $state <= 3 => 'warning',
                        default => 'success',
                    })
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()->label('Tambah Varian Baru'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([]);
    }
}
