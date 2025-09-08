<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('car_mileages', function (Blueprint $table) {
            $table->id();
            $table->integer('limit_km_per_day');
            $table->integer('limit_km_per_hour');
            $table->double('extra_fee');
            $table->timestamps();

            $table->foreignId('car_id')->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_mileages');
    }
};
