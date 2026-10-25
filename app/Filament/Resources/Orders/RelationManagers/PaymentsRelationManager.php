<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'Riwayat Pembayaran';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('provider_transaction_id')
            ->columns([
                TextColumn::make('provider')
                    ->label('Penyedia')
                    ->badge()
                    ->color('info'),
                TextColumn::make('method')
                    ->label('Saluran / Metode')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('provider_transaction_id')
                    ->label('ID Transaksi')
                    ->fontFamily('mono')
                    ->copyable()
                    ->searchable(),
                TextColumn::make('gross_amount')
                    ->label('Nominal')
                    ->formatStateUsing(fn ($state) => 'Rp' . number_format($state, 0, ',', '.'))
                    ->weight('bold'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'expired', 'failed', 'cancelled' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('paid_at')
                    ->label('Waktu Terverifikasi')
                    ->dateTime('d M Y, H:i:s')
                    ->placeholder('Belum dibayar'),
                TextColumn::make('created_at')
                    ->label('Waktu Tagihan')
                    ->dateTime('d M Y, H:i:s')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
