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
        Schema::create('booking_accident_report_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_accident_report_id');
            $table->foreign('booking_accident_report_id', 'bar_images_report_id_foreign')->references('id')->on('booking_accident_reports')->onDelete('cascade');
            $table->string('image_path');
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index('booking_accident_report_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_accident_report_images');
    }
};
