<?php

    use App\Enums\CarPriceDurationTypeEnum;
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        /**
         * Run the migrations.
         */
        public function up(): void
        {
            Schema::create('car_prices', function (Blueprint $table) {
                $table->id();
                $table->string('duration_type')->default(CarPriceDurationTypeEnum::DAY);
                $table->integer('price');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('car_prices');
        }
    };
