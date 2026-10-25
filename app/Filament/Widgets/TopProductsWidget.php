<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\DB;

class TopProductsWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Laporan Produk Terlaris (Best Seller)';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                OrderItem::query()
                    ->select(
                        DB::raw('MIN(id) as id'),
                        'product_name',
                        DB::raw('SUM(quantity) as total_sold'),
                        DB::raw('SUM(price * quantity) as total_revenue')
                    )
                    ->groupBy('product_name')
                    ->orderByDesc('total_sold')
            )
            ->columns([
                TextColumn::make('product_name')
                    ->label('Nama Produk')
                    ->weight('bold'),

                TextColumn::make('total_sold')
                    ->label('Total Terjual')
                    ->badge()
                    ->color('primary')
                    ->alignCenter(),

                TextColumn::make('total_revenue')
                    ->label('Akumulasi Omset')
                    ->formatStateUsing(fn ($state) => 'Rp' . number_format($state, 0, ',', '.'))
                    ->weight('black'),
            ])
            ->paginated(false);
    }
}
