<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')
                ->constrained('cars')
                ->cascadeOnDelete();
            $table->text('text');
            $table->timestamps();

            $table->index('car_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_rules');
    }
};
