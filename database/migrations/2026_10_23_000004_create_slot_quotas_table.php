<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slot_quotas', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('time_slot'); // '10:00', '13:00', '16:00'
            $table->unsignedInteger('capacity')->default(5);
            $table->unsignedInteger('booked')->default(0);
            $table->timestamps();

            $table->unique(['date', 'time_slot']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slot_quotas');
    }
};
