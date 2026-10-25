<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            $table->string('delivery_type')->default('delivery'); // delivery, pickup
            $table->text('delivery_address')->nullable();
            $table->foreignId('delivery_zone_id')->nullable()->constrained('delivery_zones')->nullOnDelete();
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('shipping_fee')->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('total_amount');
            $table->string('internal_status')->default('Pesanan masuk');
            $table->string('customer_status')->default('Diterima');
            $table->date('fulfillment_date');
            $table->string('fulfillment_slot'); // 10:00, 13:00, 16:00
            $table->text('customer_notes')->nullable();
            $table->foreignId('promo_code_id')->nullable()->constrained('promo_codes')->nullOnDelete();
            $table->timestamp('expires_at')->nullable(); // Default 1 jam untuk batas waktu bayar
            $table->timestamps();

            $table->index('order_number');
            $table->index('customer_phone');
            $table->index('internal_status');
            $table->index('customer_status');
            $table->index(['fulfillment_date', 'fulfillment_slot']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
