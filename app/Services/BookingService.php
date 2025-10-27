<?php

namespace App\Services;

use App\Enums\BookingStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Booking;
use App\Models\BookingStatusLog;
use App\Repositories\Interfaces\BookingRepositoryInterface;
use App\Repositories\Interfaces\CarRepositoryInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function __construct(
        protected BookingRepositoryInterface $bookingRepository,
        protected CarRepositoryInterface $carRepository,
        protected MileageValidationService $mileageValidationService,
        protected AutoReviewService $autoReviewService
    ) {
        // Auto-inject services if not provided (for backward compatibility)
        $this->mileageValidationService ??= app(MileageValidationService::class);
        $this->autoReviewService ??= app(AutoReviewService::class);
    }

    /**
     * Calculate booking price with all components
     */
    public function calculatePrice(array $data): array
    {
        return $this->bookingRepository->calculatePrice($data);
    }

    /**
     * Create a new booking
     */
    public function createBooking(array $data, int $userId): Booking
    {
        return $this->bookingRepository->createBooking($data, $userId);
    }

    /**
     * Confirm booking (Vendor action)
     */
    public function confirmBooking(int $bookingId, int $vendorId): Booking
    {
        $booking = $this->getBookingForVendor($bookingId, $vendorId);

        if ($booking->status !== BookingStatusEnum::Pending->value) {
            throw new Exception('Only pending bookings can be confirmed');
        }

        $booking->update([
            'status' => BookingStatusEnum::Confirmed->value,
            'confirmed_at' => now(),
        ]);

        $this->logStatusChange($booking, BookingStatusEnum::Confirmed->value, 'vendor', $vendorId, 'Booking confirmed by vendor');

        return $booking->fresh();
    }

    /**
     * Reject booking (Vendor action)
     */
    public function rejectBooking(int $bookingId, string $reason, int $vendorId): Booking
    {
        $booking = $this->getBookingForVendor($bookingId, $vendorId);

        if ($booking->status !== BookingStatusEnum::Pending->value) {
            throw new Exception('Only pending bookings can be rejected');
        }

        $booking->update([
            'status' => BookingStatusEnum::Rejected->value,
            'rejection_reason' => $reason,
        ]);

        $this->logStatusChange($booking, BookingStatusEnum::Rejected->value, 'vendor', $vendorId, "Booking rejected: {$reason}");

        return $booking->fresh();
    }

    /**
     * Start booking (Vendor action - car pickup)
     */
    public function startBooking(int $bookingId, int $pickupMileage): Booking
    {
        $booking = Booking::findOrFail($bookingId);

        if ($booking->status !== BookingStatusEnum::Confirmed->value) {
            throw new Exception('Only confirmed bookings can be started');
        }

        if (now()->lt($booking->pickup_date)) {
            throw new Exception('Cannot start booking before pickup date');
        }

        // Validate pickup mileage if service is available
        if ($this->mileageValidationService) {
            $mileageValidation = $this->mileageValidationService->validatePickupMileage($booking->car_id, $pickupMileage);

            if (!$mileageValidation['valid']) {
                throw new Exception('Invalid pickup mileage: ' . implode(', ', $mileageValidation['errors']));
            }

            // Log warnings if any
            if (!empty($mileageValidation['warnings'])) {
                \Log::warning('Mileage warnings for booking ' . $bookingId, $mileageValidation['warnings']);
            }
        }

        $booking->update([
            'status' => BookingStatusEnum::Active->value,
            'pickup_mileage' => $pickupMileage,
        ]);

        $this->logStatusChange($booking, BookingStatusEnum::Active->value, 'vendor', $booking->car->rentalShop->vendors->first()->id, "Booking started with mileage: {$pickupMileage}");

        return $booking->fresh();
    }

    /**
     * Complete booking (Vendor action - car return)
     */
    public function completeBooking(int $bookingId, int $returnMileage): Booking
    {
        $booking = Booking::findOrFail($bookingId);

        if ($booking->status !== BookingStatusEnum::Active->value) {
            throw new Exception('Only active bookings can be completed');
        }

        // Validate return mileage and calculate fee if service is available
        if ($this->mileageValidationService) {
            $mileageResult = $this->mileageValidationService->calculateMileageFeeWithValidation($bookingId, $returnMileage);

            if (!$mileageResult['valid']) {
                throw new Exception('Invalid return mileage: ' . implode(', ', $mileageResult['errors']));
            }

            // Log warnings if any
            if (!empty($mileageResult['warnings'])) {
                \Log::warning('Mileage warnings for booking completion ' . $bookingId, $mileageResult['warnings']);
            }

            $mileageFee = $mileageResult['fee'];
            $actualMileage = $mileageResult['actual_mileage'];
        } else {
            // Fallback to original logic
            $actualMileage = $returnMileage - $booking->pickup_mileage;
            $mileageFee = $this->calculateMileageFee($booking->car, $actualMileage);
        }

        $booking->update([
            'status' => BookingStatusEnum::Completed->value,
            'return_mileage' => $returnMileage,
            'actual_mileage_used' => $actualMileage,
            'mileage_fee' => $mileageFee,
            'completed_at' => now(),
        ]);

        // Update total price with mileage fee
        $newTotal = $booking->calculateTotalPrice() + $mileageFee;
        $booking->update(['total_price' => $newTotal]);

        $this->logStatusChange($booking, BookingStatusEnum::Completed->value, 'vendor', $booking->car->rentalShop->vendors->first()->id, "Booking completed with return mileage: {$returnMileage}, mileage fee: {$mileageFee}");

        // Trigger review creation if service is available
        if ($this->autoReviewService) {
            $this->autoReviewService->createReviewForCompletedBooking($booking);
        }

        return $booking->fresh();
    }

    /**
     * Cancel booking (User action)
     */
    public function cancelBooking(int $bookingId, int $userId, ?string $reason = null): Booking
    {
        $booking = Booking::where('user_id', $userId)->findOrFail($bookingId);

        if (!$booking->canBeCancelled()) {
            throw new Exception('This booking cannot be cancelled');
        }

        $cancellationFee = $this->calculateCancellationFee($booking);

        $booking->update([
            'status' => BookingStatusEnum::Cancelled->value,
            'cancellation_reason' => $reason,
            'cancelled_at' => now(),
        ]);

        if ($cancellationFee > 0) {
            $booking->update(['cancellation_fee' => $cancellationFee]);
        }

        $this->logStatusChange($booking, BookingStatusEnum::Cancelled->value, 'user', $userId, $reason ?? 'Booking cancelled by user');

        return $booking->fresh();
    }

    /**
     * Get user bookings with filters
     */
    public function getUserBookings(int $userId, ?string $status = null, ?int $perPage = 15): LengthAwarePaginator
    {
        $query = Booking::where('user_id', $userId)
            ->with(['car.carModel', 'car.images', 'rentalShop']);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get vendor bookings with filters
     */
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

    /**
     * Get all bookings for admin with filters
     */
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

    /**
     * Get upcoming bookings for vendor
     */
    public function getUpcomingBookings(int $vendorId, int $days = 7): array
    {
        $rentalShopIds = $this->getVendorRentalShopIds($vendorId);

        return Booking::whereIn('rental_shop_id', $rentalShopIds)
            ->whereIn('status', [BookingStatusEnum::Confirmed->value, BookingStatusEnum::Pending->value])
            ->whereBetween('pickup_date', [now(), now()->addDays($days)])
            ->with(['user', 'car.carModel'])
            ->orderBy('pickup_date')
            ->get()
            ->toArray();
    }

    /**
     * Get booking statistics
     */
    public function getBookingStats(?int $vendorId = null): array
    {
        $query = Booking::query();

        if ($vendorId) {
            $rentalShopIds = $this->getVendorRentalShopIds($vendorId);
            $query->whereIn('rental_shop_id', $rentalShopIds);
        }

        $total = $query->count();
        $pending = $query->where('status', BookingStatusEnum::Pending->value)->count();
        $confirmed = $query->where('status', BookingStatusEnum::Confirmed->value)->count();
        $active = $query->where('status', BookingStatusEnum::Active->value)->count();
        $completed = $query->where('status', BookingStatusEnum::Completed->value)->count();
        $cancelled = $query->where('status', BookingStatusEnum::Cancelled->value)->count();
        $rejected = $query->where('status', BookingStatusEnum::Rejected->value)->count();

        $totalRevenue = $query->where('status', BookingStatusEnum::Completed->value)->sum('total_price');

        return [
            'total' => $total,
            'pending' => $pending,
            'confirmed' => $confirmed,
            'active' => $active,
            'completed' => $completed,
            'cancelled' => $cancelled,
            'rejected' => $rejected,
            'total_revenue' => $totalRevenue,
        ];
    }



    /**
     * Get booking for vendor with authorization check
     */
    private function getBookingForVendor(int $bookingId, int $vendorId): Booking
    {
        $rentalShopIds = $this->getVendorRentalShopIds($vendorId);

        $booking = Booking::whereIn('rental_shop_id', $rentalShopIds)
            ->findOrFail($bookingId);

        return $booking;
    }

    /**
     * Get rental shop IDs for vendor
     */
    private function getVendorRentalShopIds(int $vendorId): array
    {
        return DB::table('rental_shop_vendor')
            ->where('vendor_id', $vendorId)
            ->pluck('rental_shop_id')
            ->toArray();
    }

    /**
     * Calculate mileage fee
     */
    private function calculateMileageFee($car, int $actualMileage): float
    {
        return $this->bookingRepository->calculateMileageFee($car, $actualMileage);
    }

    /**
     * Calculate cancellation fee
     */
    private function calculateCancellationFee(Booking $booking): float
    {
        return $this->bookingRepository->calculateCancellationFee($booking);
    }

    /**
     * Log booking status change
     */
    private function logStatusChange(Booking $booking, string $newStatus, string $changedByType, int $changedById, ?string $notes = null): void
    {
        BookingStatusLog::create([
            'booking_id' => $booking->id,
            'old_status' => $booking->getOriginal('status'),
            'new_status' => $newStatus,
            'changed_by_type' => $changedByType,
            'changed_by_id' => $changedById,
            'notes' => $notes,
        ]);
    }

    /**
     * Get user booking by ID
     */
    public function getUserBooking(int $bookingId, int $userId): Booking
    {
        $booking = $this->bookingRepository->findUserBooking($bookingId, $userId);

        if (!$booking) {
            throw new Exception('Booking not found');
        }

        return $booking;
    }

    /**
     * Get vendor booking by ID
     */
    public function getVendorBooking(int $bookingId, int $vendorId): Booking
    {
        $booking = $this->bookingRepository->findVendorBooking($bookingId, $vendorId);

        if (!$booking) {
            throw new Exception('Booking not found');
        }

        return $booking;
    }

    /**
     * Get booking by ID (admin)
     */
    public function getBookingById(int $bookingId): Booking
    {
        $booking = $this->bookingRepository->findById($bookingId);

        if (!$booking) {
            throw new Exception('Booking not found');
        }

        return $booking;
    }

    /**
     * Update booking status (admin)
     */
    public function updateBookingStatus(int $bookingId, string $status, ?string $reason = null, string $changedByType = 'admin', int $changedById = null): Booking
    {
        return $this->bookingRepository->updateBookingStatus($bookingId, $status, $reason);
    }

    /**
     * Force cancel booking (admin)
     */
    public function forceCancelBooking(int $bookingId, string $reason, int $adminId): Booking
    {
        return $this->bookingRepository->forceCancelBooking($bookingId, $reason, $adminId);
    }

    /**
     * Get booking analytics
     */
    public function getBookingAnalytics(string $period): array
    {
        return $this->bookingRepository->getBookingAnalytics($period);
    }

    /**
     * Get revenue report
     */
    public function getRevenueReport(string $startDate, string $endDate): array
    {
        return $this->bookingRepository->getRevenueReport($startDate, $endDate);
    }

    /**
     * Get vendor performance metrics
     */
    public function getVendorPerformanceMetrics(): array
    {
        return $this->bookingRepository->getVendorPerformanceMetrics();
    }

    /**
     * Get car utilization report
     */
    public function getCarUtilizationReport(string $startDate, string $endDate): array
    {
        return $this->bookingRepository->getCarUtilizationReport($startDate, $endDate);
    }

    /**
     * Get customer insights
     */
    public function getCustomerInsights(): array
    {
        return $this->bookingRepository->getCustomerInsights();
    }

    /**
     * Get booking trends
     */
    public function getBookingTrends(int $days): array
    {
        return $this->bookingRepository->getBookingTrends($days);
    }

    /**
     * Get problematic bookings
     */
    public function getProblematicBookings(): array
    {
        return $this->bookingRepository->getProblematicBookings()->toArray();
    }

    /**
     * Get vendor bookings by date range
     */
    public function getVendorBookingsByDateRange(int $vendorId, string $startDate, string $endDate): array
    {
        return $this->bookingRepository->getVendorBookingsByDateRange($vendorId, $startDate, $endDate)->toArray();
    }

    /**
     * Export bookings
     */
    public function exportBookings(array $filters, string $format): string
    {
        return $this->bookingRepository->exportBookings($filters, $format);
    }

    /**
     * Bulk update booking status
     */
    public function bulkUpdateBookingStatus(array $bookingIds, string $status, ?string $reason = null, string $changedByType = 'admin', int $changedById = null): array
    {
        return $this->bookingRepository->bulkUpdateBookingStatus($bookingIds, $status, $reason, $changedByType, $changedById);
    }

    /**
     * Get user booking statistics
     */
    public function getUserBookingStats(int $userId): array
    {
        $bookings = Booking::where('user_id', $userId);

        return [
            'total' => $bookings->count(),
            'pending' => $bookings->where('status', BookingStatusEnum::Pending->value)->count(),
            'confirmed' => $bookings->where('status', BookingStatusEnum::Confirmed->value)->count(),
            'active' => $bookings->where('status', BookingStatusEnum::Active->value)->count(),
            'completed' => $bookings->where('status', BookingStatusEnum::Completed->value)->count(),
            'cancelled' => $bookings->where('status', BookingStatusEnum::Cancelled->value)->count(),
            'rejected' => $bookings->where('status', BookingStatusEnum::Rejected->value)->count(),
            'total_spent' => $bookings->where('status', BookingStatusEnum::Completed->value)->sum('total_price'),
        ];
    }
}
