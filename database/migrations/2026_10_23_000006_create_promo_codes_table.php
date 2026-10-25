<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('type'); // fixed, percentage
            $table->unsignedBigInteger('value'); // Rupiah or percentage (e.g. 10 for 10%)
            $table->unsignedBigInteger('min_order_amount')->default(0);
            $table->unsignedBigInteger('max_discount')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('code');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_codes');
    }
};
