<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('car_prices', function (Blueprint $table) {
            $table->decimal('discounted_price', 10, 2)->nullable()->after('price');
            $table->timestamp('discount_start_at')->nullable()->after('discounted_price');
            $table->timestamp('discount_end_at')->nullable()->after('discount_start_at');

            // Index to help active discount lookups
            $table->index(['is_active', 'discount_start_at', 'discount_end_at'], 'car_prices_active_discount_idx');
        });
    }

    public function down(): void
    {
        Schema::table('car_prices', function (Blueprint $table) {
            $table->dropIndex('car_prices_active_discount_idx');
            $table->dropColumn(['discounted_price', 'discount_start_at', 'discount_end_at']);
        });
    }
};
