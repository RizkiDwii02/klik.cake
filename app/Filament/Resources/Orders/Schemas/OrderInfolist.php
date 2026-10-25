<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('order_number')
                    ->label('No. Pesanan')
                    ->weight('bold')
                    ->copyable(),
                TextEntry::make('customer_name')
                    ->label('Nama Pemesan'),
                TextEntry::make('customer_phone')
                    ->label('WhatsApp'),
                TextEntry::make('customer_email')
                    ->label('Email'),
                TextEntry::make('internal_status')
                    ->label('Status Operasional')
                    ->badge(),
                TextEntry::make('customer_status')
                    ->label('Status Pelanggan')
                    ->badge(),
                TextEntry::make('fulfillment_date')
                    ->label('Jadwal Fresh Bake')
                    ->date('l, d F Y'),
                TextEntry::make('fulfillment_slot')
                    ->label('Slot Waktu')
                    ->badge(),
                TextEntry::make('delivery_type')
                    ->label('Metode')
                    ->badge(),
                TextEntry::make('delivery_address')
                    ->label('Alamat Pengantaran'),
                TextEntry::make('total_amount')
                    ->label('Total Pembayaran')
                    ->formatStateUsing(fn ($state) => 'Rp' . number_format($state, 0, ',', '.'))
                    ->weight('bold'),
                TextEntry::make('customer_notes')
                    ->label('Catatan Khusus'),
            ]);
    }
}
