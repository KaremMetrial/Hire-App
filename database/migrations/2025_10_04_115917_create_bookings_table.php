<?php

use App\Enums\BookingStatusEnum;
use App\Enums\PaymentStatusEnum;
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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('car_id')->constrained()->onDelete('cascade');
            $table->foreignId('rental_shop_id')->constrained()->onDelete('cascade');

            $table->dateTime('pickup_date');
            $table->dateTime('return_date');
            $table->enum('pickup_location_type', ['office', 'custom'])->default('office');
            $table->text('pickup_address')->nullable();
            $table->enum('return_location_type', ['office', 'custom'])->default('office');
            $table->text('return_address')->nullable();

            $table->decimal('rental_price', 10, 2);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('extra_services_total', 10, 2)->default(0);
            $table->decimal('insurance_total', 10, 2)->default(0);
            $table->decimal('mileage_fee', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2);
            $table->decimal('deposit_amount', 10, 2)->default(0);

            $table->string('status')->default(BookingStatusEnum::Pending);
            $table->string('payment_status')->default(PaymentStatusEnum::Unpaid);

            $table->integer('pickup_mileage')->nullable();
            $table->integer('return_mileage')->nullable();
            $table->integer('actual_mileage_used')->nullable();

            $table->text('customer_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('booking_number');
            $table->index(['pickup_date', 'return_date']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
