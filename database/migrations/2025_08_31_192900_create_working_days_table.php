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
        Schema::create('working_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_shop_id')
                ->constrained('rental_shops')
                ->cascadeOnDelete();

            // Using a tinyInteger is more standard and efficient for days of the week.
            // ISO-8601 standard: 1 = Monday, 7 = Sunday.
            $table->unsignedTinyInteger('day_of_week');

            $table->time('open_time');
            $table->time('close_time');
            $table->timestamps();

            // Ensure a rental shop can only have one entry per day of the week.
            $table->unique(['rental_shop_id', 'day_of_week']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('working_days');
    }
};
