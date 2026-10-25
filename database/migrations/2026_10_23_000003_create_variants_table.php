<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('name'); // e.g. "16 cm", "20 cm", "Original", "Cokelat"
            $table->string('sku')->unique();
            $table->unsignedBigInteger('price'); // All money = unsigned bigInteger rupiah
            $table->unsignedInteger('stock')->default(0);
            $table->timestamps();

            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('variants');
    }
};
