<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\DeliveryOptionTypeEnum;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('delivery_options', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default(DeliveryOptionTypeEnum::OFFICE);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->decimal('price', 10, 2)->nullable();
            $table->timestamps();


            $table->foreignId('car_id')->constrained()->cascadeOnDelete();

            $table->unique(['car_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_options');
    }
};
