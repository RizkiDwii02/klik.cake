<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Collection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Produk')
                    ->required()
                    ->maxLength(255),

                TextInput::make('slug')
                    ->label('Slug URL')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Select::make('collection_id')
                    ->label('Koleksi / Kategori')
                    ->relationship('collection', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('availability_type')
                    ->label('Tipe Ketersediaan')
                    ->options([
                        'preorder' => 'Pre-order (Fresh Bake)',
                        'ready' => 'Siap Hari Ini',
                    ])
                    ->default('preorder')
                    ->required(),

                TextInput::make('lead_time_days')
                    ->label('Lead Time Pemesanan (Hari)')
                    ->numeric()
                    ->default(1)
                    ->helperText('Contoh: 1 untuk H-1, 3 untuk Cake Custom H-3')
                    ->required(),

                TextInput::make('portion_info')
                    ->label('Porsi & Ukuran')
                    ->placeholder('Contoh: Untuk 6–8 orang')
                    ->maxLength(255),

                TextInput::make('allergens')
                    ->label('Informasi Alergen')
                    ->placeholder('Contoh: Susu, telur, gluten')
                    ->maxLength(255),

                TextInput::make('shelf_life')
                    ->label('Daya Simpan')
                    ->placeholder('Contoh: Simpan 2 hari di kulkas')
                    ->maxLength(255),

                TextInput::make('ingredients')
                    ->label('Bahan Utama')
                    ->placeholder('Contoh: Tepung terigu, butter Australia, cream')
                    ->maxLength(255),

                Textarea::make('description')
                    ->label('Deskripsi Lengkap')
                    ->rows(4),

                Toggle::make('is_fresh_bake')
                    ->label('Badge Fresh Bake')
                    ->default(true),

                Toggle::make('is_featured')
                    ->label('Produk Unggulan (Featured)')
                    ->default(false),
            ]);
    }
}
