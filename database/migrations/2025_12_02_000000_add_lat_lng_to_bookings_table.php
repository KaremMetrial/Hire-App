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
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('pickup_latitude', 10, 8)->nullable()->after('pickup_address');
            $table->decimal('pickup_longitude', 11, 8)->nullable()->after('pickup_latitude');
            $table->decimal('return_latitude', 10, 8)->nullable()->after('return_address');
            $table->decimal('return_longitude', 11, 8)->nullable()->after('return_latitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['pickup_latitude', 'pickup_longitude', 'return_latitude', 'return_longitude']);
        });
    }
};
