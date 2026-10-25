<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('variants')->nullOnDelete();
            $table->string('product_name');
            $table->string('variant_name');
            $table->unsignedBigInteger('price');
            $table->unsignedInteger('quantity')->default(1);
            $table->string('greeting_card', 40)->nullable(); // ucapan maks 40 karakter sesuai PDF
            $table->date('fulfillment_date');
            $table->string('fulfillment_slot'); // 10:00, 13:00, 16:00
            $table->timestamps();

            $table->index('order_id');
            $table->index('variant_id');
            $table->index(['fulfillment_date', 'fulfillment_slot']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
