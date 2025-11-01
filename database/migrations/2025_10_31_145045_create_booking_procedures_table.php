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
        Schema::create('booking_procedures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['pickup', 'return']);
            $table->enum('submitted_by', ['user', 'vendor']);
            $table->text('notes')->nullable();
            $table->boolean('confirmed_by_vendor')->default(false);
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'type', 'submitted_by']);
            $table->index(['user_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_procedures');
    }
};
