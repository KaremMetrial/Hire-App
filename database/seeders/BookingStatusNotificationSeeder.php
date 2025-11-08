<?php

namespace Database\Seeders;

use App\Enums\BookingStatusEnum;
use App\Events\BookingStatusChanged;
use App\Models\Booking;
use App\Models\User;
use App\Models\Car;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BookingStatusNotificationSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating booking status notification demo data...');

        // Get a user and car for demo
        $user = User::first();
        $car = Car::where('is_active', true)->first();

        if (!$user || !$car) {
            $this->command->error('No user or active car found. Please run other seeders first.');
            return;
        }

        // Create a demo booking
        $booking = Booking::create([
            'booking_number' => 'NOTIF-DEMO-' . strtoupper(substr(uniqid(), -6)),
            'user_id' => $user->id,
            'car_id' => $car->id,
            'rental_shop_id' => $car->rental_shop_id,
            'pickup_date' => Carbon::now()->addDays(1),
            'return_date' => Carbon::now()->addDays(4),
            'pickup_location_type' => 'office',
            'pickup_address' => $car->rentalShop->address->address ?? $car->rentalShop->name,
            'return_location_type' => 'office',
            'return_address' => $car->rentalShop->address->address ?? $car->rentalShop->name,
            'rental_price' => 300.00,
            'delivery_fee' => 0.00,
            'extra_services_total' => 0.00,
            'insurance_total' => 0.00,
            'mileage_fee' => 0.00,
            'tax' => 45.00,
            'discount' => 0.00,
            'total_price' => 345.00,
            'deposit_amount' => 100.00,
            'status' => BookingStatusEnum::Pending->value,
            'payment_status' => 'unpaid',
            'pickup_mileage' => $car->kilometers,
            'customer_notes' => 'Demo booking for notification testing',
        ]);

        $this->command->info("Created demo booking: {$booking->booking_number}");

        // Simulate status changes to trigger notifications
        $statusTransitions = [
            ['from' => BookingStatusEnum::Pending, 'to' => BookingStatusEnum::Confirmed, 'delay' => 1],
            ['from' => BookingStatusEnum::Confirmed, 'to' => BookingStatusEnum::Active, 'delay' => 2],
            ['from' => BookingStatusEnum::Active, 'to' => BookingStatusEnum::Completed, 'delay' => 3],
        ];

        foreach ($statusTransitions as $transition) {
            // Update booking status
            $booking->status = $transition['to']->value;
            $booking->save();

            // Fire the event to trigger notifications
            BookingStatusChanged::dispatch(
                $booking,
                $transition['from']->value,
                $transition['to']->value,
                ['notify_vendor' => true] // Notify both user and vendor
            );

            $this->command->info("Triggered notification: {$transition['from']->value} -> {$transition['to']->value}");

            // Small delay between transitions
            sleep(1);
        }

        // Additional status changes for other scenarios
        $additionalTransitions = [
            ['from' => BookingStatusEnum::Pending, 'to' => BookingStatusEnum::Rejected],
            ['from' => BookingStatusEnum::Pending, 'to' => BookingStatusEnum::Cancelled],
            ['from' => BookingStatusEnum::Active, 'to' => BookingStatusEnum::AccidentReported],
            ['from' => BookingStatusEnum::Active, 'to' => BookingStatusEnum::InfoRequested],
        ];

        foreach ($additionalTransitions as $index => $transition) {
            // Create a new booking for each additional scenario
            $newBooking = Booking::create([
                'booking_number' => 'NOTIF-' . strtoupper(substr(uniqid(), -6)),
                'user_id' => $user->id,
                'car_id' => $car->id,
                'rental_shop_id' => $car->rental_shop_id,
                'pickup_date' => Carbon::now()->addDays(5 + $index),
                'return_date' => Carbon::now()->addDays(8 + $index),
                'pickup_location_type' => 'office',
                'pickup_address' => $car->rentalShop->address->address ?? $car->rentalShop->name,
                'return_location_type' => 'office',
                'return_address' => $car->rentalShop->address->address ?? $car->rentalShop->name,
                'rental_price' => 200.00,
                'delivery_fee' => 0.00,
                'extra_services_total' => 0.00,
                'insurance_total' => 0.00,
                'mileage_fee' => 0.00,
                'tax' => 30.00,
                'discount' => 0.00,
                'total_price' => 230.00,
                'deposit_amount' => 50.00,
                'status' => $transition['to']->value,
                'payment_status' => 'unpaid',
                'pickup_mileage' => $car->kilometers,
                'customer_notes' => "Demo booking for {$transition['to']->value} notification",
            ]);

            // Fire the event
            BookingStatusChanged::dispatch(
                $newBooking,
                $transition['from']->value,
                $transition['to']->value,
                ['notify_vendor' => true]
            );

            $this->command->info("Created additional booking and triggered notification: {$transition['from']->value} -> {$transition['to']->value}");
        }

        $this->command->info('Booking status notification demo data created successfully!');
        $this->command->info('Check your mail and database notifications for the demo bookings.');
    }
}
