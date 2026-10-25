<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained('collections')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('portion_info')->nullable(); // e.g. "Porsi 6–8 orang"
            $table->text('ingredients')->nullable();     // e.g. "Tepung terigu, mentega wisman, stroberi segar..."
            $table->string('allergens')->nullable();      // e.g. "susu, telur, gluten"
            $table->string('shelf_life')->nullable();     // e.g. "Simpan 2 hari di kulkas"
            $table->unsignedSmallInteger('lead_time_days')->default(1); // minimal H-1, custom H-3
            $table->string('availability_type')->default('preorder');   // ready_today, preorder
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_fresh_bake')->default(true);
            $table->decimal('rating', 3, 1)->default(5.0);
            $table->timestamps();

            $table->index('collection_id');
            $table->index('is_fresh_bake');
            $table->index('is_featured');
            $table->index('availability_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
