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
        Schema::create('vendor_pre_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->unique();
            $table->string('national_id_photo');
            $table->string('rental_shop_name');
            $table->string('rental_shop_phone')->unique();
            $table->string('rental_shop_image');
            $table->string('transport_license_photo');
            $table->string('commerical_registration_photo');
            $table->json('rental_shop_address')->nullable(); // Store address data as JSON
            $table->string('session_token')->unique(); // For identifying the pre-registration session
            $table->timestamp('expires_at');
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['phone', 'expires_at']);
            $table->index(['email', 'expires_at']);
            $table->index('session_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_pre_registrations');
    }
};
