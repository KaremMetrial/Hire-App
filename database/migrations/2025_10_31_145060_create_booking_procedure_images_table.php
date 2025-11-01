<?php

use App\Enums\CarImageTypeEnum;
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
        Schema::create('booking_procedure_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_procedure_id')->constrained()->onDelete('cascade');
            $table->string('image_path');
            $table->string('image_type')->default(CarImageTypeEnum::OTHER->value);
            $table->enum('uploaded_by', ['user', 'vendor']);
            $table->timestamps();

            $table->index(['booking_procedure_id', 'image_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_procedure_images');
    }
};
