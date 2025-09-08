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
        Schema::create('car_availabilities', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_available')->default(true);
            $table->dateTime('unavailable_from')->nullable();
            $table->dateTime('unavailable_to')->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->foreignId('car_id')->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_availabilities');
    }
};
