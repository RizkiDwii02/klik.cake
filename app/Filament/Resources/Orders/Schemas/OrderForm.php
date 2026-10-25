<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_number')
                    ->label('Nomor Pesanan')
                    ->disabled(),
                TextInput::make('customer_name')
                    ->label('Nama Pelanggan')
                    ->required(),
                TextInput::make('customer_phone')
                    ->label('Nomor WhatsApp')
                    ->required(),
                TextInput::make('customer_email')
                    ->label('Email'),
                Select::make('delivery_type')
                    ->label('Metode Pengantaran')
                    ->options([
                        'delivery' => 'Delivery (Kurir)',
                        'pickup' => 'Pick-up (Ambil di Toko)',
                    ])
                    ->required(),
                Textarea::make('delivery_address')
                    ->label('Alamat Pengantaran')
                    ->rows(3),
                DatePicker::make('fulfillment_date')
                    ->label('Tanggal Fresh Bake')
                    ->required(),
                TextInput::make('fulfillment_slot')
                    ->label('Slot Waktu (WITA)')
                    ->required(),
                Textarea::make('customer_notes')
                    ->label('Catatan Pesanan')
                    ->rows(2),
            ]);
    }
}
