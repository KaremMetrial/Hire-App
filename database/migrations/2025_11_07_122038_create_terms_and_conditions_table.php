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
        Schema::create('terms_and_conditions', function (Blueprint $table) {
            $table->id();
            $table->string('version', 10)->unique(); // e.g., '1.0', '1.1', '2.0'
            $table->boolean('is_active')->default(false); // Only one version can be active
            $table->boolean('is_required_agreement')->default(true); // Whether users must agree to this version
            $table->timestamp('effective_date')->nullable(); // When this version becomes effective
            $table->unsignedBigInteger('created_by')->nullable(); // Admin who created this version
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('admins')->onDelete('set null');
            $table->index(['is_active', 'effective_date']);
        });

        // Create translations table
        Schema::create('terms_and_conditions_translations', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('content'); // HTML content
            $table->string('locale')->index();
            $table->timestamps();

            $table->unsignedBigInteger('terms_and_conditions_id');
            $table->foreign('terms_and_conditions_id', 'tc_trans_tc_id_foreign')->references('id')->on('terms_and_conditions')->cascadeOnDelete();

            $table->unique(['terms_and_conditions_id', 'locale'], 'tc_trans_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terms_and_conditions_translations');
        Schema::dropIfExists('terms_and_conditions');
    }
};
