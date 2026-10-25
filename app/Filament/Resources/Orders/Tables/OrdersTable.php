<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\InternalOrderStatus;
use App\Models\Order;
use App\Services\OrderService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('No. Pesanan')
                    ->fontFamily('mono')
                    ->weight('bold')
                    ->copyable()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer_name')
                    ->label('Pelanggan')
                    ->description(fn (Order $record) => $record->customer_phone)
                    ->searchable(['customer_name', 'customer_phone'])
                    ->sortable(),

                TextColumn::make('fulfillment_date')
                    ->label('Jadwal Panggang')
                    ->date('d M Y')
                    ->description(fn (Order $record) => 'Slot ' . $record->fulfillment_slot . ' WITA')
                    ->sortable(),

                TextColumn::make('delivery_type')
                    ->label('Metode')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'delivery' ? 'Delivery' : 'Pick-up')
                    ->color(fn ($state) => $state === 'delivery' ? 'info' : 'primary'),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => 'Rp' . number_format($state, 0, ',', '.'))
                    ->weight('black')
                    ->sortable(),

                TextColumn::make('internal_status')
                    ->label('Status Operasional')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pesanan masuk' => 'warning',
                        'Pembayaran terverifikasi' => 'info',
                        'Masuk jadwal' => 'purple',
                        'Dipanggang' => 'orange',
                        'Dikemas' => 'cyan',
                        'Dikirim / Siap diambil' => 'primary',
                        'Selesai' => 'success',
                        'Dibatalkan' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('customer_status')
                    ->label('Status Pelanggan')
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Waktu Pemesanan')
                    ->dateTime('d M Y, H:i')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('internal_status')
                    ->label('Status Pesanan')
                    ->options(collect(InternalOrderStatus::cases())->mapWithKeys(fn ($case) => [$case->value => $case->value])),

                SelectFilter::make('delivery_type')
                    ->label('Metode Pengantaran')
                    ->options([
                        'delivery' => 'Delivery (Kurir)',
                        'pickup' => 'Pick-up (Ambil di Toko)',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),

                // 1. Explicit Manual Payment Verification Action (Requested in spec)
                Action::make('verifyManual')
                    ->label('Verifikasi Bayar')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->visible(fn (Order $record) => $record->internal_status === InternalOrderStatus::ORDER_PLACED->value)
                    ->requiresConfirmation()
                    ->modalHeading('Verifikasi Pembayaran Manual')
                    ->modalDescription('Tindakan ini akan menandai pesanan sebagai LUNAS/Pembayaran Terverifikasi secara manual. Pastikan Anda telah memeriksa mutasi rekening bank.')
                    ->modalSubmitActionLabel('Konfirmasi Lunas')
                    ->form([
                        TextInput::make('bank_ref')
                            ->label('Nomor Referensi Mutasi Bank')
                            ->placeholder('Contoh: TRF-BCA-847291')
                            ->required(),
                        TextInput::make('notes')
                            ->label('Catatan Verifikasi')
                            ->default('Pembayaran diverifikasi manual oleh Admin via mutasi rekening.')
                            ->required(),
                    ])
                    ->action(function (Order $record, array $data): void {
                        DB::transaction(function () use ($record, $data) {
                            $record->payments()->create([
                                'provider' => 'manual',
                                'method' => 'bank_transfer_manual',
                                'provider_transaction_id' => 'MANUAL-' . $data['bank_ref'],
                                'gross_amount' => $record->total_amount,
                                'status' => 'paid',
                                'paid_at' => now(),
                                'raw_notification' => [
                                    'verified_by' => auth()->user()?->name ?? 'Admin',
                                    'ref' => $data['bank_ref'],
                                    'notes' => $data['notes'],
                                ],
                            ]);

                            $record->updateInternalStatus(
                                InternalOrderStatus::PAYMENT_VERIFIED,
                                "Pembayaran diverifikasi manual: {$data['notes']} (Ref: {$data['bank_ref']})",
                                auth()->user()?->name ?? 'Admin'
                            );
                        });

                        Notification::make()
                            ->title('Pembayaran Terverifikasi')
                            ->body("Pesanan #{$record->order_number} berhasil diverifikasi lunas.")
                            ->success()
                            ->send();
                    }),

                // 2. Sequential Status Advancement Action
                Action::make('nextStep')
                    ->label(fn (Order $record): string => match ($record->internal_status) {
                        InternalOrderStatus::PAYMENT_VERIFIED->value => 'Masuk Jadwal',
                        InternalOrderStatus::SCHEDULED->value => 'Mulai Panggang',
                        InternalOrderStatus::BAKING->value => 'Kemas Pesanan',
                        InternalOrderStatus::PACKED->value => $record->delivery_type === 'delivery' ? 'Kirim ke Alamat' : 'Siap Diambil',
                        InternalOrderStatus::SHIPPED_OR_READY->value => 'Selesaikan Pesanan',
                        default => 'Lanjut Status',
                    })
                    ->icon(Heroicon::OutlinedArrowRightCircle)
                    ->color('primary')
                    ->visible(fn (Order $record): bool => in_array($record->internal_status, [
                        InternalOrderStatus::PAYMENT_VERIFIED->value,
                        InternalOrderStatus::SCHEDULED->value,
                        InternalOrderStatus::BAKING->value,
                        InternalOrderStatus::PACKED->value,
                        InternalOrderStatus::SHIPPED_OR_READY->value,
                    ]))
                    ->requiresConfirmation()
                    ->modalHeading(fn (Order $record) => "Lanjutkan Status Pesanan #{$record->order_number}")
                    ->modalDescription(function (Order $record): string {
                        $next = match ($record->internal_status) {
                            InternalOrderStatus::PAYMENT_VERIFIED->value => InternalOrderStatus::SCHEDULED->value,
                            InternalOrderStatus::SCHEDULED->value => InternalOrderStatus::BAKING->value,
                            InternalOrderStatus::BAKING->value => InternalOrderStatus::PACKED->value,
                            InternalOrderStatus::PACKED->value => InternalOrderStatus::SHIPPED_OR_READY->value,
                            InternalOrderStatus::SHIPPED_OR_READY->value => InternalOrderStatus::COMPLETED->value,
                            default => '',
                        };
                        return "Status pesanan akan diubah dari [{$record->internal_status}] menjadi [{$next}]. Perubahan ini akan langsung tampil di halaman lacak pelanggan.";
                    })
                    ->action(function (Order $record): void {
                        $nextEnum = match ($record->internal_status) {
                            InternalOrderStatus::PAYMENT_VERIFIED->value => InternalOrderStatus::SCHEDULED,
                            InternalOrderStatus::SCHEDULED->value => InternalOrderStatus::BAKING,
                            InternalOrderStatus::BAKING->value => InternalOrderStatus::PACKED,
                            InternalOrderStatus::PACKED->value => InternalOrderStatus::SHIPPED_OR_READY,
                            InternalOrderStatus::SHIPPED_OR_READY->value => InternalOrderStatus::COMPLETED,
                            default => null,
                        };

                        if ($nextEnum) {
                            $record->updateInternalStatus(
                                $nextEnum,
                                "Status dimajukan ke '{$nextEnum->value}' oleh " . (auth()->user()?->name ?? 'Admin'),
                                auth()->user()?->name ?? 'Admin'
                            );

                            Notification::make()
                                ->title('Status Berhasil Dimutasi')
                                ->body("Status pesanan #{$record->order_number} kini: {$nextEnum->value}")
                                ->success()
                                ->send();
                        }
                    }),

                // 3. Special Status Change with Confirmation & Mandatory Reason (Prevents unconfirmed jumping/backward)
                Action::make('customStatus')
                    ->label('Ubah Status...')
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->color('gray')
                    ->visible(fn (Order $record) => $record->internal_status !== InternalOrderStatus::CANCELLED->value)
                    ->form([
                        Select::make('new_status')
                            ->label('Status Baru')
                            ->options(function (Order $record) {
                                return collect(InternalOrderStatus::cases())
                                    ->reject(fn ($c) => $c->value === $record->internal_status || $c === InternalOrderStatus::CANCELLED)
                                    ->mapWithKeys(fn ($c) => [$c->value => $c->value]);
                            })
                            ->required(),
                        Textarea::make('reason')
                            ->label('Alasan Perubahan Status')
                            ->placeholder('Jelaskan mengapa status diubah di luar urutan normal...')
                            ->required(),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Perubahan Status Khusus')
                    ->action(function (Order $record, array $data): void {
                        $newStatus = $data['new_status'];
                        $reason = $data['reason'];

                        // If changing to PAYMENT_VERIFIED, ensure it requires manual payment verification
                        if ($newStatus === InternalOrderStatus::PAYMENT_VERIFIED->value) {
                            $record->payments()->create([
                                'provider' => 'manual',
                                'method' => 'admin_override',
                                'provider_transaction_id' => 'OVERRIDE-' . uniqid(),
                                'gross_amount' => $record->total_amount,
                                'status' => 'paid',
                                'paid_at' => now(),
                                'raw_notification' => ['reason' => $reason],
                            ]);
                        }

                        $record->updateInternalStatus(
                            $newStatus,
                            "Perubahan status khusus ke '{$newStatus}': {$reason}",
                            auth()->user()?->name ?? 'Admin'
                        );

                        Notification::make()
                            ->title('Status Berhasil Diubah')
                            ->body("Pesanan #{$record->order_number} diubah menjadi '{$newStatus}'.")
                            ->success()
                            ->send();
                    }),

                // 4. Cancel Order Action (Atomically releases slot & stock)
                Action::make('cancelOrder')
                    ->label('Batalkan')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->visible(fn (Order $record) => $record->internal_status !== InternalOrderStatus::CANCELLED->value && $record->internal_status !== InternalOrderStatus::COMPLETED->value)
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Pembatalan Pesanan')
                    ->modalDescription('Pesanan akan dibatalkan. Stok varian produk dan kuota slot akan otomatis dikembalikan ke sistem.')
                    ->form([
                        Textarea::make('cancel_reason')
                            ->label('Alasan Pembatalan')
                            ->placeholder('Contoh: Permintaan pembeli / kuota dapur terkendala')
                            ->required(),
                    ])
                    ->action(function (Order $record, array $data, OrderService $orderService): void {
                        $orderService->cancelAndRelease(
                            $record,
                            "Dibatalkan oleh Admin: {$data['cancel_reason']}"
                        );

                        Notification::make()
                            ->title('Pesanan Dibatalkan')
                            ->body("Pesanan #{$record->order_number} telah dibatalkan dan kuota/stok dikembalikan.")
                            ->danger()
                            ->send();
                    }),
            ]);
    }
}
