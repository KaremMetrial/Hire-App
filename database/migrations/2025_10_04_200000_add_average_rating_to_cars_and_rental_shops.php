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
        Schema::table('cars', function (Blueprint $table) {
            $table->decimal('average_rating', 3, 2)->default(0)->after('is_active');
            $table->index('average_rating');
        });

        Schema::table('rental_shops', function (Blueprint $table) {
            $table->decimal('average_rating', 3, 2)->default(0)->after('rating');
            $table->index('average_rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropIndex(['average_rating']);
            $table->dropColumn('average_rating');
        });

        Schema::table('rental_shops', function (Blueprint $table) {
            $table->dropIndex(['average_rating']);
            $table->dropColumn('average_rating');
        });
    }
};
