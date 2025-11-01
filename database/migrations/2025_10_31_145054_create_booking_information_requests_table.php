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
        Schema::create('booking_information_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->string('requested_field'); // e.g., 'license_number', 'license_expiry_date', 'face_license_id_photo', etc.
            $table->boolean('is_required')->default(true);
            $table->enum('status', ['pending', 'submitted', 'approved', 'rejected'])->default('pending');
            $table->text('notes')->nullable(); // Additional notes from vendor
            $table->text('submitted_value')->nullable(); // The value submitted by user
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'status']);
            $table->index('requested_field');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_information_requests');
    }
};
