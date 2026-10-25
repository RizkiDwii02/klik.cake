<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('provider')->default('midtrans');
            $table->string('method')->nullable(); // qris, gopay, shopeepay, bank_transfer
            $table->string('provider_transaction_id')->nullable()->unique();
            $table->unsignedBigInteger('gross_amount');
            $table->string('status')->default('pending'); // pending, paid, expired, failed, cancelled
            $table->json('raw_notification')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
