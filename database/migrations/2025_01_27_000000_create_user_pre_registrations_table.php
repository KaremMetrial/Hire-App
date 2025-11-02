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
        Schema::create('user_pre_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('country_id');
            $table->string('phone')->unique();
            $table->string('email')->unique();
            $table->date('birthday');
            $table->string('face_license_id_photo');
            $table->string('back_license_id_photo');
            $table->string('avatar')->nullable();
            $table->string('session_token')->unique(); // For identifying the pre-registration session
            $table->timestamp('expires_at');
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
        Schema::dropIfExists('user_pre_registrations');
    }
};
