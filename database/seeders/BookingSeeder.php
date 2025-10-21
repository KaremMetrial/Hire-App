<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\BookingReview;
use App\Models\BookingExtraService;
use App\Models\BookingInsurance;
use App\Models\BookingDocument;
use App\Models\BookingStatusLog;
use App\Models\User;
use App\Models\Car;
use App\Models\RentalShop;
use App\Models\ExtraService;
use App\Models\Insurance;
use App\Models\Document;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::take(10)->get();
        $cars = Car::where('is_active', true)->get();
        $this->command->info("Total active cars found: " . $cars->count());

        if ($cars->count() === 0) {
            $this->command->error("No active cars found in database. Cannot create bookings.");
            return;
        }
        $extraServices = ExtraService::where('is_active', true)->take(5)->get();
        $insurances = Insurance::where('is_active', true)->take(3)->get();
        $documents = Document::where('is_active', true)->take(3)->get();

        // Create various booking scenarios
        $bookingScenarios = [
            [
                'status' => 'completed',
                'payment_status' => 'paid',
                'days_back' => 30,
                'duration_days' => 3,
                'has_review' => true,
                'has_extra_services' => true,
                'has_insurance' => true,
            ],
            [
                'status' => 'active',
                'payment_status' => 'paid',
                'days_back' => 2,
                'duration_days' => 5,
                'has_review' => false,
                'has_extra_services' => true,
                'has_insurance' => true,
            ],
            [
                'status' => 'confirmed',
                'payment_status' => 'unpaid',
                'days_back' => 0,
                'duration_days' => 7,
                'has_review' => false,
                'has_extra_services' => false,
                'has_insurance' => true,
            ],
            [
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'days_back' => 0,
                'duration_days' => 2,
                'has_review' => false,
                'has_extra_services' => false,
                'has_insurance' => false,
            ],
            [
                'status' => 'cancelled',
                'payment_status' => 'refunded',
                'days_back' => 15,
                'duration_days' => 4,
                'has_review' => false,
                'has_extra_services' => false,
                'has_insurance' => false,
            ],
        ];

        foreach ($bookingScenarios as $index => $scenario) {
            $user = $users->random();
            $car = $cars->random();
            $rentalShop = $car->rentalShop;

            $pickupDate = Carbon::now()->subDays($scenario['days_back']);
            $returnDate = $pickupDate->copy()->addDays($scenario['duration_days']);

            // Calculate pricing
            $dailyPriceRecord = $car->prices()->where('duration_type', 'day')->first();
            if (!$dailyPriceRecord) {
                $this->command->warn("No daily price found for car ID: {$car->id}");
                continue;
            }
            $dailyPrice = $dailyPriceRecord->price;
            $rentalPrice = $dailyPrice * $scenario['duration_days'];
            $deliveryFee = rand(0, 1) ? 25.00 : 0.00;
            $extraServicesTotal = 0;
            $insuranceTotal = 0;

            // Create booking
            $booking = Booking::create([
                'booking_number' => 'BK-' . date('Ymd', $pickupDate->timestamp) . '-' . strtoupper(substr(uniqid(), -6)),
                'user_id' => $user->id,
                'car_id' => $car->id,
                'rental_shop_id' => $rentalShop->id,
                'pickup_date' => $pickupDate,
                'return_date' => $returnDate,
                'pickup_location_type' => 'office',
                'pickup_address' => $rentalShop->address->address ?? $rentalShop->name,
                'return_location_type' => 'office',
                'return_address' => $rentalShop->address->address ?? $rentalShop->name,
                'rental_price' => $rentalPrice,
                'delivery_fee' => $deliveryFee,
                'extra_services_total' => $extraServicesTotal,
                'insurance_total' => $insuranceTotal,
                'mileage_fee' => 0,
                'tax' => ($rentalPrice + $deliveryFee + $extraServicesTotal + $insuranceTotal) * 0.15,
                'discount' => rand(0, 1) ? rand(10, 50) : 0,
                'total_price' => 0, // Will be calculated below
                'deposit_amount' => $dailyPrice * 2,
                'status' => $scenario['status'],
                'payment_status' => $scenario['payment_status'],
                'pickup_mileage' => $car->kilometers,
                'return_mileage' => $scenario['status'] === 'completed' ? $car->kilometers + rand(100, 500) : null,
                'actual_mileage_used' => $scenario['status'] === 'completed' ? rand(100, 500) : null,
                'customer_notes' => $this->getRandomCustomerNote(),
                'admin_notes' => $this->getRandomAdminNote(),
                'confirmed_at' => in_array($scenario['status'], ['confirmed', 'active', 'completed']) ? $pickupDate->copy()->addHours(rand(1, 24)) : null,
                'cancelled_at' => $scenario['status'] === 'cancelled' ? $pickupDate->copy()->addHours(rand(1, 12)) : null,
                'completed_at' => $scenario['status'] === 'completed' ? $returnDate->copy()->addHours(rand(1, 6)) : null,
            ]);

            // Calculate total price
            $totalPrice = $booking->rental_price + $booking->delivery_fee + $booking->extra_services_total +
                         $booking->insurance_total + $booking->mileage_fee + $booking->tax - $booking->discount;
            $booking->total_price = $totalPrice;
            $booking->save();

            // Add extra services if specified
            if ($scenario['has_extra_services']) {
                $selectedServices = $extraServices->random(rand(1, 3));
                foreach ($selectedServices as $service) {
                    $quantity = rand(1, 2);
                    $price = rand(10, 30) * $quantity;

                    BookingExtraService::create([
                        'booking_id' => $booking->id,
                        'extra_service_id' => $service->id,
                        'price' => $price / $quantity,
                        'quantity' => $quantity,
                    ]);

                    $extraServicesTotal += $price;
                }

                // Update booking with extra services total
                $booking->extra_services_total = $extraServicesTotal;
                $booking->total_price = $booking->rental_price + $booking->delivery_fee + $extraServicesTotal +
                                     $booking->insurance_total + $booking->mileage_fee + $booking->tax - $booking->discount;
                $booking->save();
            }

            // Add insurance if specified
            if ($scenario['has_insurance']) {
                $selectedInsurance = $insurances->random();
                $insurancePrice = $selectedInsurance->price * $scenario['duration_days'];

                BookingInsurance::create([
                    'booking_id' => $booking->id,
                    'insurance_id' => $selectedInsurance->id,
                    'price' => $insurancePrice / $scenario['duration_days'],
                    'deposit_price' => $selectedInsurance->deposit_price,
                ]);

                $insuranceTotal = $insurancePrice;
                $booking->insurance_total = $insuranceTotal;
                $booking->total_price = $booking->rental_price + $booking->delivery_fee + $extraServicesTotal +
                                     $insuranceTotal + $booking->mileage_fee + $booking->tax - $booking->discount;
                $booking->save();
            }

            // Create payment records
            if ($scenario['payment_status'] === 'paid') {
                BookingPayment::create([
                    'booking_id' => $booking->id,
                    'payment_method' => ['cash', 'card', 'bank_transfer'][rand(0, 2)],
                    'amount' => $booking->total_price,
                    'payment_type' => 'rental',
                    'status' => 'completed',
                    'transaction_id' => 'TXN-' . strtoupper(substr(uniqid(), -8)),
                    'payment_date' => $pickupDate->copy()->subHours(rand(1, 24)),
                    'notes' => 'Payment processed successfully',
                ]);
            } elseif ($scenario['payment_status'] === 'unpaid') {
                BookingPayment::create([
                    'booking_id' => $booking->id,
                    'payment_method' => 'card',
                    'amount' => $booking->deposit_amount,
                    'payment_type' => 'deposit',
                    'status' => 'pending',
                    'transaction_id' => 'TXN-' . strtoupper(substr(uniqid(), -8)),
                    'payment_date' => null,
                    'notes' => 'Awaiting payment confirmation',
                ]);
            }

            // Add documents
            foreach ($documents->random(rand(1, 2)) as $document) {
                BookingDocument::create([
                    'booking_id' => $booking->id,
                    'document_id' => $document->id,
                    'file_path' => 'documents/bookings/' . $booking->booking_number . '/' . $document->id . '.pdf',
                    'document_value' => $document->input_type === 'text' ? 'Sample value for ' . $document->name : null,
                    'verified' => $scenario['status'] === 'completed' || $scenario['status'] === 'active',
                    'verified_at' => ($scenario['status'] === 'completed' || $scenario['status'] === 'active') ? $pickupDate->copy()->addHours(rand(1, 6)) : null,
                    'verified_by' => $rentalShop->vendors->first()->id ?? null,
                ]);
            }

            // Add status logs
            $this->createStatusLogs($booking);

            // Add review if booking is completed
            if ($scenario['has_review']) {
                BookingReview::create([
                    'booking_id' => $booking->id,
                    'user_id' => $user->id,
                    'rental_shop_id' => $rentalShop->id,
                    'car_id' => $car->id,
                    'rating' => rand(3, 5),
                    'cleanliness_rating' => rand(3, 5),
                    'service_rating' => rand(3, 5),
                    'value_rating' => rand(3, 5),
                    'comment' => $this->getRandomReviewComment(),
                    'is_approved' => true,
                ]);
            }
        }

        // Create additional random bookings
        for ($i = 0; $i < 15; $i++) {
            $user = $users->random();
            $car = $cars->random();
            $rentalShop = $car->rentalShop;

            $pickupDate = Carbon::now()->subDays(rand(1, 60));
            $durationDays = rand(1, 14);
            $returnDate = $pickupDate->copy()->addDays($durationDays);

            $dailyPrice = $car->prices()->where('duration_type', 'day')->first()->price;
            $rentalPrice = $dailyPrice * $durationDays;

            $status = ['pending', 'confirmed', 'active', 'completed', 'cancelled'][rand(0, 4)];
            $paymentStatus = $status === 'cancelled' ? 'refunded' : (rand(0, 1) ? 'paid' : 'unpaid');

            Booking::create([
                'booking_number' => 'BK-' . date('Ymd', $pickupDate->timestamp) . '-' . strtoupper(substr(uniqid(), -6)),
                'user_id' => $user->id,
                'car_id' => $car->id,
                'rental_shop_id' => $rentalShop->id,
                'pickup_date' => $pickupDate,
                'return_date' => $returnDate,
                'pickup_location_type' => 'office',
                'pickup_address' => $rentalShop->address->address ?? $rentalShop->name,
                'return_location_type' => 'office',
                'return_address' => $rentalShop->address->address ?? $rentalShop->name,
                'rental_price' => $rentalPrice,
                'delivery_fee' => rand(0, 1) ? 25.00 : 0.00,
                'extra_services_total' => 0,
                'insurance_total' => 0,
                'mileage_fee' => 0,
                'tax' => $rentalPrice * 0.15,
                'discount' => rand(0, 1) ? rand(10, 50) : 0,
                'total_price' => $rentalPrice * 1.15 - (rand(0, 1) ? rand(10, 50) : 0),
                'deposit_amount' => $dailyPrice * 2,
                'status' => $status,
                'payment_status' => $paymentStatus,
                'pickup_mileage' => $car->kilometers,
                'return_mileage' => $status === 'completed' ? $car->kilometers + rand(100, 500) : null,
                'actual_mileage_used' => $status === 'completed' ? rand(100, 500) : null,
                'customer_notes' => $this->getRandomCustomerNote(),
                'confirmed_at' => in_array($status, ['confirmed', 'active', 'completed']) ? $pickupDate->copy()->addHours(rand(1, 24)) : null,
                'cancelled_at' => $status === 'cancelled' ? $pickupDate->copy()->addHours(rand(1, 12)) : null,
                'completed_at' => $status === 'completed' ? $returnDate->copy()->addHours(rand(1, 6)) : null,
            ]);
        }
    }

    private function createStatusLogs($booking)
    {
        $logs = [
            ['status' => 'pending', 'created_at' => $booking->created_at],
        ];

        if ($booking->confirmed_at) {
            $logs[] = ['status' => 'confirmed', 'created_at' => $booking->confirmed_at];
        }

        if ($booking->status === 'active' && $booking->pickup_date) {
            $logs[] = ['status' => 'active', 'created_at' => $booking->pickup_date->copy()->addHours(1)];
        }

        if ($booking->status === 'completed' && $booking->completed_at) {
            $logs[] = ['status' => 'completed', 'created_at' => $booking->completed_at];
        }

        if ($booking->status === 'cancelled' && $booking->cancelled_at) {
            $logs[] = ['status' => 'cancelled', 'created_at' => $booking->cancelled_at];
        }

        foreach ($logs as $index => $log) {
            if ($index > 0) {
                BookingStatusLog::create([
                    'booking_id' => $booking->id,
                    'old_status' => $logs[$index - 1]['status'],
                    'new_status' => $log['status'],
                    'changed_by_type' => 'App\Models\User',
                    'changed_by_id' => $booking->user_id,
                    'notes' => "Status changed from {$logs[$index - 1]['status']} to {$log['status']}",
                    'created_at' => $log['created_at'],
                    'updated_at' => $log['created_at'],
                ]);
            }
        }
    }

    private function getRandomCustomerNote()
    {
        $notes = [
            'Please ensure the car is clean and has a full tank.',
            'Need GPS navigation system for the trip.',
            'Requesting early pickup if possible.',
            'Will be traveling with family, need child seat.',
            'Please provide detailed inspection report.',
            'Need car for business meetings.',
            'Planning a road trip, need reliable vehicle.',
            'First time renting, please provide guidance.',
        ];

        return $notes[array_rand($notes)];
    }

    private function getRandomAdminNote()
    {
        $notes = [
            'Customer verified, all documents in order.',
            'Special discount applied for loyal customer.',
            'VIP customer, provide premium service.',
            'Car serviced and ready for rental.',
            'Additional insurance recommended.',
            'Customer requested specific features.',
            'Follow up required after rental.',
            'Priority booking, ensure availability.',
        ];

        return $notes[array_rand($notes)];
    }

    private function getRandomReviewComment()
    {
        $comments = [
            'Excellent service! Car was clean and well-maintained.',
            'Great experience, staff was very helpful.',
            'Good value for money, would rent again.',
            'Car performed perfectly, no issues at all.',
            'Professional service, highly recommended.',
            'Smooth process from booking to return.',
            'Vehicle exceeded expectations, very satisfied.',
            'Outstanding customer service, will definitely return.',
        ];

        return $comments[array_rand($comments)];
    }
}
