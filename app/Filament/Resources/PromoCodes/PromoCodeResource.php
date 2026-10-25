<?php

namespace App\Filament\Resources\PromoCodes;

use App\Filament\Resources\PromoCodes\Pages\ManagePromoCodes;
use App\Models\PromoCode;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PromoCodeResource extends Resource
{
    protected static ?string $model = PromoCode::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static \UnitEnum|string|null $navigationGroup = 'Pengaturan';

    protected static ?string $modelLabel = 'Kode Promo';

    protected static ?string $pluralModelLabel = 'Voucher & Promo';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Kode Promo / Voucher')
                    ->placeholder('Contoh: KLIKMANIS')
                    ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50),

                Select::make('type')
                    ->label('Tipe Potongan')
                    ->options([
                        'percentage' => 'Persentase (%)',
                        'fixed' => 'Potongan Tetap (Nominal Rp)',
                    ])
                    ->default('percentage')
                    ->required(),

                TextInput::make('value')
                    ->label('Nilai Diskon (% atau Rp)')
                    ->numeric()
                    ->required(),

                TextInput::make('min_order_amount')
                    ->label('Minimal Pembelian (Rupiah)')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0),

                TextInput::make('max_discount')
                    ->label('Maksimal Potongan Diskon (Rupiah, Opsional)')
                    ->numeric()
                    ->prefix('Rp'),

                DatePicker::make('starts_at')
                    ->label('Mulai Berlaku'),

                DatePicker::make('expires_at')
                    ->label('Berakhir Pada'),

                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode Promo')
                    ->fontFamily('mono')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn ($state) => $state === 'percentage' ? 'info' : 'success'),

                TextColumn::make('value')
                    ->label('Diskon')
                    ->formatStateUsing(function (PromoCode $record) {
                        return $record->type === 'percentage'
                            ? "{$record->value}%"
                            : 'Rp' . number_format($record->value, 0, ',', '.');
                    })
                    ->weight('bold'),

                TextColumn::make('min_order_amount')
                    ->label('Min. Belanja')
                    ->formatStateUsing(fn ($state) => 'Rp' . number_format($state, 0, ',', '.')),

                TextColumn::make('expires_at')
                    ->label('Kadaluwarsa')
                    ->date('d M Y')
                    ->placeholder('Selamanya'),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
            ])
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
            'index' => ManagePromoCodes::route('/'),
        ];
    }
}
