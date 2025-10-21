<?php

use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentTransactionStatusEnum;
use App\Enums\PaymentTypeEnum;
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
        Schema::create('booking_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->string('payment_method')->default(PaymentMethodEnum::Online);
            $table->decimal('amount', 10, 2);
            $table->string('payment_type')->default(PaymentTypeEnum::Rental);
            $table->string('status')->default(PaymentTransactionStatusEnum::Pending);
            $table->string('transaction_id')->nullable();
            $table->timestamp('payment_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_payments');
    }
};
