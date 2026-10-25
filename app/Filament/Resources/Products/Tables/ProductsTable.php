<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Produk')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('collection.name')
                    ->label('Koleksi')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('lead_time_days')
                    ->label('Lead Time')
                    ->formatStateUsing(fn ($state) => "H-{$state}")
                    ->alignCenter(),

                TextColumn::make('availability_type')
                    ->label('Ketersediaan')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'preorder' ? 'Pre-Order' : 'Ready')
                    ->color(fn ($state) => $state === 'preorder' ? 'warning' : 'success'),

                TextColumn::make('variants_count')
                    ->label('Varian')
                    ->counts('variants')
                    ->alignCenter(),

                IconColumn::make('is_fresh_bake')
                    ->label('Fresh Bake')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('collection_id')
                    ->label('Koleksi')
                    ->relationship('collection', 'name'),

                TernaryFilter::make('is_fresh_bake')
                    ->label('Fresh Bake'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
