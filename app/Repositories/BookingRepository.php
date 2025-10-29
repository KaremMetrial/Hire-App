<?php

namespace App\Repositories;

use App\Enums\BookingStatusEnum;
use App\Enums\CarPriceDurationTypeEnum;
use App\Models\Booking;
use App\Models\BookingDocument;
use App\Models\BookingExtraService;
use App\Models\BookingInsurance;
use App\Models\BookingPayment;
use App\Models\BookingStatusLog;
use App\Models\Car;
use App\Models\CarPrice;
use App\Repositories\Interfaces\BookingRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BookingRepository implements BookingRepositoryInterface
{
    public function calculatePrice($data)
    {
        $car = Car::with(['prices', 'mileages', 'services', 'insurances'])->findOrFail($data['car_id']);

        $pickupDate = Carbon::parse($data['pickup_date']);
        $returnDate = Carbon::parse($data['return_date']);

        $rentalDays = $pickupDate->diffInDays($returnDate);
        $rentalHours = $pickupDate->diffInHours($returnDate) - ($rentalDays * 24);

        // Use selected price_id if provided, otherwise calculate automatically
        if (isset($data['price_id'])) {
            $rentalPrice = $this->calculateRentalPriceFromId($data['price_id'], $rentalDays, $rentalHours);
        } else {
            $rentalPrice = $this->calculateRentalPrice($car, $rentalDays, $rentalHours);
        }

        $deliveryFee = $this->calculateDeliveryFee($car, $data);

        $extraServicesTotal = $this->calculateExtraServicesTotal($car, $data['extra_services'] ?? [], $rentalDays);
        $insuranceTotal = $this->calculateInsuranceTotal($car, $data['insurance_id'] ?? null, $rentalDays);

        $taxRate = config('booking.tax_rate', 0.10);
        $tax = ($rentalPrice + $extraServicesTotal + $insuranceTotal) * $taxRate;

        $subtotal = $rentalPrice + $deliveryFee + $extraServicesTotal + $insuranceTotal;
        $totalPrice = $subtotal + $tax;

        return [
            'rental_days' => $rentalDays,
            'rental_hours' => $rentalHours,
            'rental_price' => round($rentalPrice, 2),
            'delivery_fee' => round($deliveryFee, 2),
            'extra_services_total' => round($extraServicesTotal, 2),
            'insurance_total' => round($insuranceTotal, 2),
            'tax_rate' => $taxRate * 100,
            'tax_amount' => round($tax, 2),
            'subtotal' => round($subtotal, 2),
            'total_price' => round($totalPrice, 2),
            'currency' => 'JOD',
            'price_id_used' => $data['price_id'] ?? null,
            'pricing_method' => isset($data['price_id']) ? 'selected_price' : 'auto_calculated',
        ];
    }

    public function findById(int $id): ?Booking
    {
        return Booking::find($id);
    }

    public function findUserBooking(int $bookingId, int $userId): ?Booking
    {
        return Booking::where('id', $bookingId)
            ->where('user_id', $userId)
            ->first();
    }

    public function findVendorBooking(int $bookingId, int $vendorId): ?Booking
    {
        $rentalShopIds = $this->getVendorRentalShopIds($vendorId);

        return Booking::whereIn('rental_shop_id', $rentalShopIds)
            ->where('id', $bookingId)
            ->first();
    }

    public function getUserBookings(int $userId, ?array $statuses = null, ?int $perPage = 15): LengthAwarePaginator
    {
        $query = Booking::where('user_id', $userId)
            ->with(['car.carModel', 'car.images', 'rentalShop']);

        if ($statuses) {
            $query->whereIn('status', $statuses);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getVendorBookings(int $vendorId, ?string $status = null, ?int $perPage = 15): LengthAwarePaginator
    {
        $rentalShopIds = $this->getVendorRentalShopIds($vendorId);

        $query = Booking::whereIn('rental_shop_id', $rentalShopIds)
            ->with(['user', 'car.carModel', 'car.images']);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getAllBookings(?string $status = null, ?string $dateFrom = null, ?string $dateTo = null, ?int $perPage = 15): LengthAwarePaginator
    {
        $query = Booking::with(['user', 'car.carModel', 'rentalShop.vendor']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($dateFrom) {
            $query->whereDate('pickup_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('pickup_date', '<=', $dateTo);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getUpcomingBookings(int $vendorId, int $days = 7): Collection
    {
        $rentalShopIds = $this->getVendorRentalShopIds($vendorId);

        return Booking::whereIn('rental_shop_id', $rentalShopIds)
            ->whereIn('status', [BookingStatusEnum::Confirmed->value, BookingStatusEnum::Pending->value, BookingStatusEnum::InfoRequested->value])
            ->whereBetween('pickup_date', [now(), now()->addDays($days)])
            ->with(['user', 'car.carModel'])
            ->orderBy('pickup_date')
            ->get();
    }

    public function getVendorBookingsByDateRange(int $vendorId, string $startDate, string $endDate): Collection
    {
        $rentalShopIds = $this->getVendorRentalShopIds($vendorId);

        return Booking::whereIn('rental_shop_id', $rentalShopIds)
            ->whereBetween('pickup_date', [$startDate, $endDate])
            ->with(['user', 'car.carModel'])
            ->orderBy('pickup_date')
            ->get();
    }

    public function getProblematicBookings(): Collection
    {
        return Booking::where(function ($query) {
            $query->where('status', BookingStatusEnum::Pending->value)
                ->where('created_at', '<', now()->subDays(3))
                ->orWhere('status', BookingStatusEnum::Active->value)
                ->where('return_date', '<', now()->subDay())
                ->orWhere('payment_status', 'unpaid')
                ->where('created_at', '<', now()->subHours(2));
        })
            ->with(['user', 'car.carModel', 'rentalShop.vendor'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getBookingAnalytics(string $period): array
    {
        $startDate = match ($period) {
            'day' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $bookings = Booking::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total_price) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'period' => $period,
            'start_date' => $startDate->toDateString(),
            'data' => $bookings,
            'total_bookings' => $bookings->sum('count'),
            'total_revenue' => $bookings->sum('revenue'),
        ];
    }

    public function getRevenueReport(string $startDate, string $endDate): array
    {
        $bookings = Booking::whereBetween('pickup_date', [$startDate, $endDate])
            ->where('status', BookingStatusEnum::Completed->value)
            ->selectRaw('
                DATE(pickup_date) as date,
                COUNT(*) as bookings_count,
                SUM(total_price) as total_revenue,
                SUM(rental_price) as rental_revenue,
                SUM(extra_services_total) as services_revenue,
                SUM(insurance_total) as insurance_revenue,
                SUM(mileage_fee) as mileage_revenue,
                SUM(tax) as tax_revenue
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'summary' => [
                'total_bookings' => $bookings->sum('bookings_count'),
                'total_revenue' => $bookings->sum('total_revenue'),
                'average_booking_value' => $bookings->avg('total_revenue'),
            ],
            'breakdown' => [
                'rental_revenue' => $bookings->sum('rental_revenue'),
                'services_revenue' => $bookings->sum('services_revenue'),
                'insurance_revenue' => $bookings->sum('insurance_revenue'),
                'mileage_revenue' => $bookings->sum('mileage_revenue'),
                'tax_revenue' => $bookings->sum('tax_revenue'),
            ],
            'daily_data' => $bookings,
        ];
    }

    public function getVendorPerformanceMetrics(): array
    {
        $vendors = DB::table('vendors')
            ->select([
                'vendors.id',
                'vendors.name',
                DB::raw('COUNT(bookings.id) as total_bookings'),
                DB::raw('SUM(CASE WHEN bookings.status = "completed" THEN bookings.total_price ELSE 0 END) as revenue'),
                DB::raw('AVG(CASE WHEN bookings.status = "completed" THEN bookings.total_price ELSE NULL END) as avg_booking_value'),
                DB::raw('COUNT(CASE WHEN bookings.status = "completed" THEN 1 END) as completed_bookings'),
                DB::raw('COUNT(CASE WHEN bookings.status = "cancelled" THEN 1 END) as cancelled_bookings'),
            ])
            ->leftJoin('rental_shop_vendor', 'vendors.id', '=', 'rental_shop_vendor.vendor_id')
            ->leftJoin('rental_shops', 'rental_shop_vendor.rental_shop_id', '=', 'rental_shops.id')
            ->leftJoin('bookings', 'rental_shops.id', '=', 'bookings.rental_shop_id')
            ->groupBy('vendors.id', 'vendors.name')
            ->orderBy('revenue', 'desc')
            ->get();

        return $vendors->map(function ($vendor) {
            $completionRate = $vendor->total_bookings > 0
                ? ($vendor->completed_bookings / $vendor->total_bookings) * 100
                : 0;

            return [
                'vendor_id' => $vendor->id,
                'vendor_name' => $vendor->name,
                'total_bookings' => $vendor->total_bookings,
                'completed_bookings' => $vendor->completed_bookings,
                'cancelled_bookings' => $vendor->cancelled_bookings,
                'completion_rate' => round($completionRate, 2),
                'total_revenue' => $vendor->revenue,
                'average_booking_value' => $vendor->avg_booking_value,
            ];
        })->toArray();
    }

    public function getCarUtilizationReport(string $startDate, string $endDate): array
    {
        $cars = Car::with(['bookings' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('pickup_date', [$startDate, $endDate])
                ->whereIn('status', [BookingStatusEnum::Confirmed->value, BookingStatusEnum::Active->value, BookingStatusEnum::Completed->value]);
        }])
            ->get();

        $utilizationData = $cars->map(function ($car) use ($startDate, $endDate) {
            $totalDays = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
            $bookedDays = 0;

            foreach ($car->bookings as $booking) {
                $bookingStart = Carbon::parse($booking->pickup_date);
                $bookingEnd = Carbon::parse($booking->return_date);

                // Calculate overlap with the period
                $periodStart = Carbon::parse($startDate);
                $periodEnd = Carbon::parse($endDate);

                $overlapStart = max($bookingStart, $periodStart);
                $overlapEnd = min($bookingEnd, $periodEnd);

                if ($overlapEnd > $overlapStart) {
                    $bookedDays += $overlapStart->diffInDays($overlapEnd) + 1;
                }
            }

            $utilizationRate = ($bookedDays / $totalDays) * 100;

            return [
                'car_id' => $car->id,
                'car_name' => $car->carModel->name,
                'total_days' => $totalDays,
                'booked_days' => $bookedDays,
                'utilization_rate' => round($utilizationRate, 2),
                'total_bookings' => $car->bookings->count(),
                'revenue' => $car->bookings->sum('total_price'),
            ];
        });

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'summary' => [
                'total_cars' => $cars->count(),
                'average_utilization' => $utilizationData->avg('utilization_rate'),
                'total_revenue' => $utilizationData->sum('revenue'),
            ],
            'car_data' => $utilizationData,
        ];
    }

    public function getCustomerInsights(): array
    {
        $customers = DB::table('users')
            ->select([
                'users.id',
                'users.name',
                'users.email',
                DB::raw('COUNT(bookings.id) as total_bookings'),
                DB::raw('SUM(CASE WHEN bookings.status = "completed" THEN bookings.total_price ELSE 0 END) as total_spent'),
                DB::raw('AVG(CASE WHEN bookings.status = "completed" THEN bookings.total_price ELSE NULL END) as avg_booking_value'),
                DB::raw('MAX(bookings.created_at) as last_booking_date'),
            ])
            ->leftJoin('bookings', 'users.id', '=', 'bookings.user_id')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderBy('total_spent', 'desc')
            ->limit(100)
            ->get();

        return [
            'total_customers' => $customers->count(),
            'top_customers' => $customers->take(10),
            'average_bookings_per_customer' => $customers->avg('total_bookings'),
            'average_spending_per_customer' => $customers->avg('total_spent'),
        ];
    }

    public function getBookingTrends(int $days): array
    {
        $startDate = now()->subDays($days);

        $trends = Booking::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as bookings')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'period_days' => $days,
            'start_date' => $startDate->toDateString(),
            'end_date' => now()->toDateString(),
            'trend_data' => $trends,
            'total_bookings' => $trends->sum('bookings'),
            'average_daily_bookings' => $trends->avg('bookings'),
        ];
    }

    public function updateBookingStatus(int $bookingId, string $status, ?string $reason = null): Booking
    {
        $booking = Booking::findOrFail($bookingId);
        $oldStatus = $booking->status;

        $booking->update([
            'status' => $status,
        ]);

        if ($reason) {
            $booking->update([
                $status === BookingStatusEnum::Cancelled->value ? 'cancellation_reason' : 'rejection_reason' => $reason,
            ]);
        }

        // Log the status change
        BookingStatusLog::create([
            'booking_id' => $bookingId,
            'old_status' => $oldStatus,
            'new_status' => $status,
            'changed_by_type' => 'admin',
            'notes' => $reason,
        ]);

        return $booking->fresh();
    }

    public function forceCancelBooking(int $bookingId, string $reason, int $adminId): Booking
    {
        $booking = Booking::findOrFail($bookingId);

        $booking->update([
            'status' => BookingStatusEnum::Cancelled->value,
            'cancellation_reason' => $reason,
            'cancelled_at' => now(),
        ]);

        BookingStatusLog::create([
            'booking_id' => $bookingId,
            'old_status' => $booking->getOriginal('status'),
            'new_status' => BookingStatusEnum::Cancelled->value,
            'changed_by_type' => 'admin',
            'changed_by_id' => $adminId,
            'notes' => "Force cancelled by admin: {$reason}",
        ]);

        return $booking->fresh();
    }

    public function bulkUpdateBookingStatus(array $bookingIds, string $status, ?string $reason = null, string $changedByType = 'admin', int $changedById = null): array
    {
        $results = [
            'success' => [],
            'failed' => [],
        ];

        foreach ($bookingIds as $bookingId) {
            try {
                $booking = Booking::findOrFail($bookingId);
                $oldStatus = $booking->status;

                $booking->update(['status' => $status]);

                BookingStatusLog::create([
                    'booking_id' => $bookingId,
                    'old_status' => $oldStatus,
                    'new_status' => $status,
                    'changed_by_type' => $changedByType,
                    'changed_by_id' => $changedById,
                    'notes' => $reason,
                ]);

                $results['success'][] = $bookingId;
            } catch (\Exception $e) {
                $results['failed'][] = [
                    'booking_id' => $bookingId,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    public function exportBookings(array $filters, string $format): string
    {
        $query = Booking::with(['user', 'car.carModel', 'rentalShop']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('pickup_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('pickup_date', '<=', $filters['date_to']);
        }

        if (isset($filters['vendor_id'])) {
            $rentalShopIds = $this->getVendorRentalShopIds($filters['vendor_id']);
            $query->whereIn('rental_shop_id', $rentalShopIds);
        }

        $bookings = $query->get();

        $filename = "bookings_export_" . now()->format('Y-m-d_H-i-s') . ".{$format}";
        $path = "exports/{$filename}";

        // For simplicity, we'll create a CSV export
        $csvContent = $this->generateCsvContent($bookings);
        Storage::disk('public')->put($path, $csvContent);

        return Storage::url($path);
    }

    // Private helper methods
    private function calculateExtraServicesTotal(Car $car, array $extraServices, int $days): float
    {
        $total = 0;

        foreach ($extraServices as $service) {
            $carExtraService = $car->services()
                ->where('extra_service_id', $service['id'])
                ->first();

            if ($carExtraService && $carExtraService->pivot) {
                $total += $carExtraService->pivot->price * $service['quantity'] * $days;
            }
        }

        return $total;
    }

    private function calculateDeliveryFee(Car $car, array $data): float
    {
        $deliveryOptions = $car->deliveryOptions->where('is_active', true);

        if ($data['pickup_location_type'] === 'custom') {
            $customDelivery = $deliveryOptions->where('type', 'custom_delivery')->first();

            return $customDelivery ? $customDelivery->price : 50.00;
        }

        return 0.00;
    }

    /**
     * Calculate cancellation fee based on configuration
     */
    public function calculateCancellationFee($booking): float
    {
        $hoursUntilPickup = now()->diffInHours($booking->pickup_date);
        $fees = config('booking.cancellation_fees');

        if ($hoursUntilPickup < 24) {
            return $booking->total_price * $fees['less_than_24_hours'];
        } elseif ($hoursUntilPickup < 72) {
            return $booking->total_price * $fees['less_than_72_hours'];
        }

        return $booking->total_price * $fees['default'];
    }

    /**
     * Calculate mileage fee based on configuration
     */
    public function calculateMileageFee($car, int $actualMileage): float
    {
        $mileageConfig = config('booking.mileage');
        $includedMileage = $car->mileages->daily_mileage_limit ?? $mileageConfig['daily_limit'];
        $extraMileage = max(0, $actualMileage - $includedMileage);
        $mileageRate = $car->mileages->extra_mileage_rate ?? $mileageConfig['extra_rate'];

        return $extraMileage * $mileageRate;
    }

    private function calculateRentalPriceFromId(int $priceId, int $days, int $hours): float
    {
        $carPrice = CarPrice::where('id', $priceId)
            ->where('is_active', true)
            ->firstOrFail();
        return match ($carPrice->duration_type->value) {
            CarPriceDurationTypeEnum::HOUR->value => $carPrice->price * (($days * 24) + $hours),
            CarPriceDurationTypeEnum::DAY->value => $carPrice->price * max($days, 1),
            CarPriceDurationTypeEnum::WEEK->value => $carPrice->price * ceil(max($days, 1) / 7),
            CarPriceDurationTypeEnum::MONTH->value => $carPrice->price * ceil(max($days, 1) / 30),
            default => throw new \Exception('Invalid price duration type'),
        };
    }

    private function calculateRentalPrice(Car $car, int $days, int $hours): float
    {
        $carPrices = $car->prices->where('is_active', true);

        if ($days >= 30) {
            $monthlyPrice = $carPrices->where('duration_type', CarPriceDurationTypeEnum::MONTH->value)->first();
            if ($monthlyPrice) {
                $months = ceil($days / 30);

                return $monthlyPrice->price * $months;
            }
        }

        if ($days >= 7) {
            $weeklyPrice = $carPrices->where('duration_type', CarPriceDurationTypeEnum::WEEK->value)->first();
            if ($weeklyPrice) {
                $weeks = ceil($days / 7);

                return $weeklyPrice->price * $weeks;
            }
        }

        $dailyPrice = $carPrices->where('duration_type', CarPriceDurationTypeEnum::DAY->value)->first();
        if ($dailyPrice && $days > 0) {
            return $dailyPrice->price * $days;
        }

        $hourlyPrice = $carPrices->where('duration_type', CarPriceDurationTypeEnum::HOUR->value)->first();
        if ($hourlyPrice) {
            $totalHours = ($days * 24) + $hours;

            return $hourlyPrice->price * $totalHours;
        }

        throw new \Exception('No valid pricing found for this car');
    }

    private function calculateInsuranceTotal(Car $car, ?int $insuranceId, int $days): float
    {
        if (!$insuranceId) {
            return 0;
        }

        $insurance = $car->insurances()->where('insurances.id', $insuranceId)->first();

        if ($insurance) {
            return $insurance->price * $days;
        }

        return 0;
    }

    public function createBooking(array $data, int $userId): Booking
    {
        return DB::transaction(function () use ($data, $userId) {
            // Use reservation service if token is provided
            if (isset($data['reservation_token'])) {
                try {
                    $reservationService = app(\App\Services\BookingReservationService::class);
                    $booking = $reservationService->confirmReservation($data['reservation_token'], $data);
                } catch (\Exception $e) {
                    // Fallback to original method if reservation fails
                    $priceDetails = $this->calculatePrice($data);
                    $car = Car::with(['services', 'insurances'])->findOrFail($data['car_id']);
                    $booking = $this->createBookingRecord($data, $userId, $car, $priceDetails);
                    $this->createBookingRelations($booking, $car, $data, $priceDetails);
                    $this->logBookingCreation($booking, $userId);
                }
            } else {
                // Fallback to original method (not recommended for production)
                $priceDetails = $this->calculatePrice($data);
                $car = Car::with(['services', 'insurances'])->findOrFail($data['car_id']);
                $booking = $this->createBookingRecord($data, $userId, $car, $priceDetails);
                $this->createBookingRelations($booking, $car, $data, $priceDetails);
                $this->logBookingCreation($booking, $userId);
            }

            return $booking->load(['extraServices', 'insurances', 'payments', 'documents']);
        });
    }


    /**
     * Create the main booking record
     */
    private function createBookingRecord(array $data, int $userId, Car $car, array $priceDetails): Booking
    {
        $bookingData = [
            'booking_number' => 'BK' . strtoupper(Str::random(8)) . time(),
            'user_id' => $userId,
            'car_id' => $data['car_id'],
            'rental_shop_id' => $car->rental_shop_id,
            'pickup_date' => $data['pickup_date'],
            'return_date' => $data['return_date'],
            'pickup_location_type' => $data['pickup_location_type'],
            'return_location_type' => $data['return_location_type'],
            'pickup_address' => $data['pickup_address'] ?? null,
            'return_address' => $data['return_address'] ?? null,
            'rental_price' => $priceDetails['rental_price'],
            'delivery_fee' => $priceDetails['delivery_fee'],
            'extra_services_total' => $priceDetails['extra_services_total'],
            'insurance_total' => $priceDetails['insurance_total'],
            'tax' => $priceDetails['tax_amount'],
            'total_price' => $priceDetails['total_price'],
            'status' => BookingStatusEnum::Pending->value,
            'payment_status' => 'unpaid',
            'customer_notes' => $data['customer_notes'] ?? null,
        ];
        return Booking::create($bookingData);
    }

    /**
     * Create all related records for the booking
     */
    private function createBookingRelations(Booking $booking, Car $car, array $data, array $priceDetails): void
    {
        $this->createBookingPayment($booking, $priceDetails);
        $this->createBookingExtraServices($booking, $car, $data['extra_services'] ?? []);
        $this->createBookingInsurance($booking, $car, $data['insurance_id'] ?? null, $priceDetails);
        $this->createBookingDocuments($booking, $data['documents'] ?? []);
    }

    /**
     * Create booking payment record
     */
    private function createBookingPayment(Booking $booking, array $priceDetails): void
    {
        BookingPayment::create([
            'booking_id' => $booking->id,
            'payment_method' => 'online',
            'amount' => $priceDetails['total_price'],
            'payment_type' => 'rental',
            'status' => 'pending',
        ]);
    }

    /**
     * Create extra services records for the booking
     */
    private function createBookingExtraServices(Booking $booking, Car $car, array $extraServices): void
    {
        foreach ($extraServices as $service) {
            $carExtraService = $car->services()
                ->where('extra_service_id', $service['id'])
                ->first();

            BookingExtraService::create([
                'booking_id' => $booking->id,
                'extra_service_id' => $service['id'],
                'price' => $carExtraService->pivot->price,
                'quantity' => $service['quantity'],
            ]);
        }
    }

    /**
     * Create insurance record for the booking
     */
    private function createBookingInsurance(Booking $booking, Car $car, ?int $insuranceId, array $priceDetails): void
    {
        if (!$insuranceId) {
            return;
        }

        $insurance = $car->insurances()->where('insurances.id', $insuranceId)->first();
        $insuranceDays = $priceDetails['rental_days'];
        $insurancePrice = $insurance->price * $insuranceDays;

        BookingInsurance::create([
            'booking_id' => $booking->id,
            'insurance_id' => $insuranceId,
            'price' => $insurancePrice,
            'deposit_price' => $insurance->deposit_price,
        ]);
    }

    /**
     * Create document records for the booking
     */
    private function createBookingDocuments(Booking $booking, array $documents): void
    {
        foreach ($documents as $documentData) {
            $filePath = null;
            $documentValue = null;

            if (isset($documentData['file'])) {
                $filePath = $documentData['file']->store('booking-documents', 'public');
            } elseif (isset($documentData['value'])) {
                $documentValue = $documentData['value'];
            }

            BookingDocument::create([
                'booking_id' => $booking->id,
                'document_id' => $documentData['document_id'],
                'file_path' => $filePath,
                'document_value' => $documentValue,
                'verified' => false,
            ]);
        }
    }

    /**
     * Log the booking creation
     */
    private function logBookingCreation(Booking $booking, int $userId): void
    {
        BookingStatusLog::create([
            'booking_id' => $booking->id,
            'old_status' => null,
            'new_status' => BookingStatusEnum::Pending->value,
            'changed_by_type' => 'user',
            'changed_by_id' => $userId,
            'notes' => 'Booking created',
        ]);
    }

    private function getVendorRentalShopIds(int $vendorId): array
    {
        return DB::table('rental_shop_vendor')
            ->where('vendor_id', $vendorId)
            ->pluck('rental_shop_id')
            ->toArray();
    }

    /**
     * Check if car is available for given dates
     */
    public function isCarAvailable(int $carId, string $pickupDate, string $returnDate): bool
    {
        $conflictingBookings = Booking::where('car_id', $carId)
            ->whereIn('status', [BookingStatusEnum::Confirmed->value, BookingStatusEnum::Active->value])
            ->where(function ($query) use ($pickupDate, $returnDate) {
                $query->whereBetween('pickup_date', [$pickupDate, $returnDate])
                    ->orWhereBetween('return_date', [$pickupDate, $returnDate])
                    ->orWhere(function ($q) use ($pickupDate, $returnDate) {
                        $q->where('pickup_date', '<=', $pickupDate)
                            ->where('return_date', '>=', $returnDate);
                    });
            })
            ->exists();

        return !$conflictingBookings;
    }

    private function generateCsvContent($bookings): string
    {
        $csv = "Booking Number,User,Car,Pickup Date,Return Date,Status,Total Price\n";

        foreach ($bookings as $booking) {
            $csv .= "{$booking->booking_number},{$booking->user->name},{$booking->car->carModel->name},{$booking->pickup_date},{$booking->return_date},{$booking->status},{$booking->total_price}\n";
        }

        return $csv;
    }

    /**
     * Get booking statistics for a user
     *
     * @param int $userId
     * @return array
     */
    public function getUserBookingStats(int $userId): array
    {
        $stats = [
            'total_bookings' => 0,
            'upcoming_bookings' => 0,
            'completed_bookings' => 0,
            'cancelled_bookings' => 0,
            'total_spent' => 0,
            'favorite_car' => null,
            'favorite_rental_shop' => null,
        ];

        // Get all user bookings with relationships
        $bookings = Booking::with(['car.carModel', 'rentalShop'])
            ->where('user_id', $userId)
            ->get();

        if ($bookings->isEmpty()) {
            return $stats;
        }

        $stats['total_bookings'] = $bookings->count();
        $stats['upcoming_bookings'] = $bookings->whereIn('status', [
            BookingStatusEnum::Pending->value,
            BookingStatusEnum::Confirmed->value,
            BookingStatusEnum::Active->value
        ])->count();

        $stats['completed_bookings'] = $bookings->where('status', BookingStatusEnum::Completed->value)->count();
        $stats['cancelled_bookings'] = $bookings->where('status', BookingStatusEnum::Cancelled->value)->count();
        $stats['total_spent'] = $bookings->where('status', '!=', BookingStatusEnum::Cancelled->value)->sum('total_price');

        // Get favorite car
        $favoriteCar = $bookings->groupBy('car_id')
            ->map->count()
            ->sortDesc()
            ->keys()
            ->first();

        if ($favoriteCar) {
            $car = $bookings->firstWhere('car_id', $favoriteCar);
            $stats['favorite_car'] = [
                'id' => $favoriteCar,
                'name' => $car->car->carModel->name ?? 'Unknown',
                'model' => $car->car->carModel->model ?? 'Unknown',
                'brand' => $car->car->carModel->brand->name ?? 'Unknown'
            ];
        }

        // Get favorite rental shop
        $favoriteShop = $bookings->groupBy('rental_shop_id')
            ->map->count()
            ->sortDesc()
            ->keys()
            ->first();

        if ($favoriteShop) {
            $shop = $bookings->firstWhere('rental_shop_id', $favoriteShop);
            $stats['favorite_rental_shop'] = [
                'id' => $favoriteShop,
                'name' => $shop->rentalShop->name ?? 'Unknown',
                'city' => $shop->rentalShop->city ?? 'Unknown'
            ];
        }

        return $stats;
    }
}
