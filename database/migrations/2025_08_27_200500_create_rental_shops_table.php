<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\RentalShopStatusEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rental_shops', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->unique()->index();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(false);
            $table->string('status')->default(RentalShopStatusEnum::PENDING->value);
            $table->timestamp('actioned_at')->nullable();
            $table->string('rejected_reason')->nullable();
            $table->string('transport_license_photo')->nullable();
            $table->string('commerical_registration_photo')->nullable();
            $table->tinyInteger('rating')->default(0);
            $table->integer('count_rating')->default(0);
            $table->timestamps();

            $table->foreignId('actioned_by')->nullable()->constrained('admins')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_shops');
    }
};
