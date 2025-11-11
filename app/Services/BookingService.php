<?php

namespace App\Services;

use App\Enums\BookingStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Booking;
use App\Models\BookingInformationRequest;
use App\Models\BookingProcedure;
use App\Models\BookingProcedureImage;
use App\Models\BookingStatusLog;
use App\Repositories\Interfaces\BookingRepositoryInterface;
use App\Repositories\Interfaces\CarRepositoryInterface;
use App\Events\BookingStatusChanged;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
     * Get booking statistics for a user
     *
     * @param int $userId
     * @return array
     */
    public function getUserBookingStats(int $userId): array
    {
        return $this->bookingRepository->getUserBookingStats($userId);
    }

    /**
     * Confirm booking (Vendor action)
     */
    public function confirmBooking(int $bookingId, int $vendorId): Booking
    {
        return DB::transaction(function () use ($bookingId, $vendorId) {
            $booking = $this->getBookingForVendor($bookingId, $vendorId);

            if ($booking->status !== BookingStatusEnum::Pending->value) {
                throw new Exception('Only pending bookings can be confirmed');
            }

            $oldStatus = $booking->status;
            $booking->update([
                'status' => BookingStatusEnum::Confirmed->value,
                'confirmed_at' => now(),
            ]);

            // Dispatch status change event
            $this->dispatchStatusChangeEvent($booking, $oldStatus->value, $booking->status->value, [
                'changed_by_type' => 'vendor',
                'changed_by_id' => $vendorId,
                'notes' => 'Booking confirmed by vendor',
                'notify_vendor' => false,
            ]);

            return $booking->fresh();
        });
    }

    /**
     * Reject booking (Vendor action)
     */
    public function rejectBooking(int $bookingId, string $reason, int $vendorId): Booking
    {
        return DB::transaction(function () use ($bookingId, $reason, $vendorId) {
            $booking = $this->getBookingForVendor($bookingId, $vendorId);

            if ($booking->status !== BookingStatusEnum::Pending->value) {
                throw new Exception('Only pending bookings can be rejected');
            }

            $booking->update([
                'status' => BookingStatusEnum::Rejected->value,
                'rejection_reason' => $reason,
            ]);

            return $booking->fresh();
        });
    }

    /**
     * Start booking (Vendor action - car pickup)
     */
    public function startBooking(int $bookingId, int $pickupMileage): Booking
    {
        return DB::transaction(function () use ($bookingId, $pickupMileage) {
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
                    Log::warning('Mileage warnings for booking ' . $bookingId, $mileageValidation['warnings']);
                }
            }

            $oldStatus = $booking->status;
            $booking->update([
                'status' => BookingStatusEnum::Active->value,
                'pickup_mileage' => $pickupMileage,
            ]);

            // Dispatch status change event
            $this->dispatchStatusChangeEvent($booking, $oldStatus->value, $booking->status->value, [
                'changed_by_type' => 'vendor',
                'changed_by_id' => $booking->car->rentalShop->vendors->first()->id,
                'notes' => "Booking started with mileage: {$pickupMileage}",
                'notify_vendor' => false,
            ]);

            return $booking->fresh();
        });
    }

    /**
     * Complete booking (Vendor action - car return)
     */
    public function completeBooking(int $bookingId, int $returnMileage): Booking
    {
        return DB::transaction(function () use ($bookingId, $returnMileage) {
            $booking = Booking::findOrFail($bookingId);

            if ($booking->status !== BookingStatusEnum::Active->value && $booking->status !== BookingStatusEnum::AccidentReported->value) {
                throw new Exception('Only active or accident reported bookings can be completed');
            }

            // Validate return mileage and calculate fee if service is available
            if ($this->mileageValidationService) {
                $mileageResult = $this->mileageValidationService->calculateMileageFeeWithValidation($bookingId, $returnMileage);

                if (!$mileageResult['valid']) {
                    throw new Exception('Invalid return mileage: ' . implode(', ', $mileageResult['errors']));
                }

                // Log warnings if any
                if (!empty($mileageResult['warnings'])) {
                    Log::warning('Mileage warnings for booking completion ' . $bookingId, $mileageResult['warnings']);
                }

                $mileageFee = $mileageResult['fee'];
                $actualMileage = $mileageResult['actual_mileage'];
            } else {
                // Fallback to original logic
                $actualMileage = $returnMileage - $booking->pickup_mileage;
                $mileageFee = $this->calculateMileageFee($booking->car, $actualMileage);
            }

            $oldStatus = $booking->status;
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

            // Dispatch status change event
            $this->dispatchStatusChangeEvent($booking, $oldStatus->value, $booking->status->value, [
                'changed_by_type' => 'vendor',
                'changed_by_id' => $booking->car->rentalShop->vendors->first()->id,
                'notes' => "Booking completed with return mileage: {$returnMileage}",
                'notify_vendor' => false,
            ]);

            return $booking->fresh();
        });
    }

    /**
     * Cancel booking (User action)
     */
    public function cancelBooking(int $bookingId, int $userId, ?string $reason = null): Booking
    {
        return DB::transaction(function () use ($bookingId, $userId, $reason) {
            $booking = Booking::where('user_id', $userId)->findOrFail($bookingId);

            if (!$booking->canBeCancelled() && !$booking->isAccidentReported()) {
                throw new Exception('This booking cannot be cancelled');
            }

            $cancellationFee = $this->calculateCancellationFee($booking);
            $oldStatus = $booking->status;

            $booking->update([
                'status' => BookingStatusEnum::Cancelled->value,
                'cancellation_reason' => $reason,
                'cancelled_at' => now(),
            ]);

            if ($cancellationFee > 0) {
                $booking->update(['cancellation_fee' => $cancellationFee]);
            }

            // Dispatch status change event
            $this->dispatchStatusChangeEvent($booking, $oldStatus->value, $booking->status->value, [
                'changed_by_type' => 'user',
                'changed_by_id' => $userId,
                'notes' => $reason ?? 'Booking cancelled by user',
                'notify_vendor' => true,
            ]);

            return $booking->fresh();
        });
    }

    /**
     * Request additional information from user (Vendor action)
     */
    public function requestBookingInfo(int $bookingId, int $vendorId, array $informationRequests): Booking
    {
        return DB::transaction(function () use ($bookingId, $vendorId, $informationRequests) {
            $booking = $this->getBookingForVendor($bookingId, $vendorId);
            if ($booking->status != BookingStatusEnum::Pending->value) {
                throw new Exception('Only pending bookings can have info requested');
            }

            // Create information request records
            foreach ($informationRequests as $requestData) {
                BookingInformationRequest::create([
                    'booking_id' => $bookingId,
                    'requested_field' => $requestData['field'],
                    'is_required' => $requestData['is_required'] ?? true,
                    'notes' => $requestData['notes'] ?? null,
                ]);
            }

            $booking->update([
                'status' => BookingStatusEnum::InfoRequested->value,
            ]);

            return $booking->fresh()->load('informationRequests');
        });
    }

    /**
     * Submit additional information for booking (User action)
     */
    public function submitBookingInfo(int $bookingId, int $userId, array $info): Booking
    {
        return DB::transaction(function () use ($bookingId, $userId, $info) {
            $booking = Booking::where('user_id', $userId)->findOrFail($bookingId);

            if ($booking->status !== BookingStatusEnum::InfoRequested->value) {
                throw new Exception('This booking is not waiting for information');
            }

            // Update information requests with submitted values
            $pendingRequests = $booking->informationRequests()->where('status', 'pending')->get();

            foreach ($pendingRequests as $request) {
                $field = $request->requested_field;
                if (isset($info[$field])) {
                    $value = $info[$field];

                    // Handle file uploads for photos
                    if (in_array($field, ['face_license_id_photo', 'back_license_id_photo']) && $value instanceof \Illuminate\Http\UploadedFile) {
                        $value = $value->store('license-photos', 'public');
                    }

                    $request->markAsSubmitted($value);
                } elseif ($request->is_required) {
                    throw new Exception("Required field '{$request->getFieldLabel()}' is missing");
                }
            }

            // Check if all required information has been submitted
            $remainingRequired = $booking->informationRequests()->where('status', 'pending')->where('is_required', true)->count();

            if ($remainingRequired === 0) {
                // All required info submitted, move back to pending for vendor review
                $booking->update([
                    'status' => BookingStatusEnum::Pending->value,
                    'customer_notes' => $info['additional_notes'] ?? $booking->customer_notes,
                ]);
            } else {
                // Still waiting for some required info, keep status as info_requested
                $booking->update([
                    'customer_notes' => $info['additional_notes'] ?? $booking->customer_notes,
                ]);
            }

            return $booking->fresh()->load('informationRequests');
        });
    }

    /**
     * Get user bookings with filters
     */
    public function getUserBookings(int $userId, ?array $statuses = null, ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->bookingRepository->getUserBookings($userId, $statuses, $perPage);
    }

    /**
     * Get vendor bookings with filters
     */
    public function getVendorBookings(int $vendorId, ?array $statuses = null, ?int $perPage = 15): LengthAwarePaginator
    {
        // Use repository method instead of duplicating logic
        return $this->bookingRepository->getVendorBookings($vendorId, $statuses, $perPage);
    }

    /**
     * Get all bookings for admin with filters
     */
    public function getAllBookings(?string $status = null, ?string $dateFrom = null, ?string $dateTo = null, ?int $perPage = 15): LengthAwarePaginator
    {
        $query = Booking::with(['user', 'car.carModel', 'rentalShop.vendors']);

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
        // Use repository method instead of duplicating logic
        return $this->bookingRepository->getUpcomingBookings($vendorId, $days)->toArray();
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
        $underReview = $query->where('status', BookingStatusEnum::UnderReview->value)->count();
        $confirmed = $query->where('status', BookingStatusEnum::Confirmed->value)->count();
        $active = $query->where('status', BookingStatusEnum::Active->value)->count();
        $completed = $query->where('status', BookingStatusEnum::Completed->value)->count();
        $cancelled = $query->where('status', BookingStatusEnum::Cancelled->value)->count();
        $rejected = $query->where('status', BookingStatusEnum::Rejected->value)->count();
        $infoRequested = $query->where('status', BookingStatusEnum::InfoRequested->value)->count();
        $underDispute = $query->where('status', BookingStatusEnum::UnderDispute->value)->count();
        $disputeOpened = $query->where('status', BookingStatusEnum::DisputeOpened->value)->count();

        $totalRevenue = $query->where('status', BookingStatusEnum::Completed->value)->sum('total_price');

        return [
            'total' => $total,
            'pending' => $pending,
            'under_review' => $underReview,
            'confirmed' => $confirmed,
            'active' => $active,
            'completed' => $completed,
            'cancelled' => $cancelled,
            'rejected' => $rejected,
            'info_requested' => $infoRequested,
            'under_dispute' => $underDispute,
            'dispute_opened' => $disputeOpened,
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
     * Dispatch status change event with proper error handling
     */
    protected function dispatchStatusChangeEvent(Booking $booking, string $oldStatus, string $newStatus, array $context = []): void
    {
        try {
            BookingStatusChanged::dispatch($booking, $oldStatus, $newStatus, $context);
        } catch (Exception $e) {
            // Log the error but don't break the booking operation
            Log::error('Failed to dispatch booking status change event', [
                'booking_id' => $booking->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'error' => $e->getMessage(),
            ]);
        }
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
        // Use repository method instead of duplicating logic
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
     * Submit pickup procedure (User action)
     */
    public function submitPickupProcedure(int $bookingId, int $userId, array $data): BookingProcedure
    {
        return DB::transaction(function () use ($bookingId, $userId, $data) {
            $booking = Booking::where('user_id', $userId)->findOrFail($bookingId);
            if ($booking->status !== BookingStatusEnum::Confirmed->value) {
                throw new Exception('Only confirmed bookings can have pickup procedures submitted');
            }

            // Check if user already submitted pickup procedure
            $existingProcedure = $booking->pickupProcedures()->byUser()->first();
            if ($existingProcedure) {
                throw new Exception('Pickup procedure already submitted');
            }

            $procedure = BookingProcedure::create([
                'booking_id' => $bookingId,
                'user_id' => $userId,
                'type' => 'pickup',
                'submitted_by' => 'user',
                'notes' => $data['notes'] ?? null,
            ]);

            // Handle image uploads
            if (isset($data['images']) && is_array($data['images'])) {
                foreach ($data['images'] as $imageData) {
                    $imagePath = $imageData['image']->store('booking-procedures', 'public');
                    BookingProcedureImage::create([
                        'booking_procedure_id' => $procedure->id,
                        'image_path' => $imagePath,
                        'image_type' => $imageData['image_type'],
                        'uploaded_by' => 'user',
                    ]);
                }
            }

            return $procedure->load('images');
        });
    }

    /**
     * Confirm pickup procedure (Vendor action)
     */
    public function confirmPickupProcedure(int $bookingId, int $vendorId, array $data): Booking
    {
        return DB::transaction(function () use ($bookingId, $vendorId, $data) {
            $booking = $this->getBookingForVendor($bookingId, $vendorId);

            if ($booking->status !== BookingStatusEnum::Confirmed->value) {
                throw new Exception('Only confirmed bookings can have pickup procedures confirmed');
            }

            $userProcedure = $booking->pickupProcedures()->byUser()->first();
            if (!$userProcedure) {
                throw new Exception('User must submit pickup procedure before vendor can confirm');
            }

            // Create vendor procedure if not exists
            $vendorProcedure = $booking->pickupProcedures()->byVendor()->first();
            if (!$vendorProcedure) {
                $vendorProcedure = BookingProcedure::create([
                    'booking_id' => $bookingId,
                    'user_id' => $booking->user_id,
                    'type' => 'pickup',
                    'submitted_by' => 'vendor',
                    'notes' => $data['notes'] ?? null,
                    'confirmed_by_vendor' => true,
                    'confirmed_at' => now(),
                ]);
            } else {
                $vendorProcedure->update([
                    'notes' => $data['notes'] ?? $vendorProcedure->notes,
                    'confirmed_by_vendor' => true,
                    'confirmed_at' => now(),
                ]);
            }

            // Handle vendor image uploads
            if (isset($data['images']) && is_array($data['images'])) {
                foreach ($data['images'] as $imageData) {
                    $imagePath = $imageData['image']->store('booking-procedures', 'public');

                    BookingProcedureImage::create([
                        'booking_procedure_id' => $vendorProcedure->id,
                        'image_path' => $imagePath,
                        'image_type' => $imageData['image_type'],
                        'uploaded_by' => 'vendor',
                    ]);
                }
            }

            // Mark user procedure as confirmed
            $userProcedure->markAsConfirmed();

            // If confirmed is true, start the booking
            if (($data['confirmed'] ?? false)) {
                $booking->update([
                    'status' => BookingStatusEnum::Active->value,
                    'pickup_mileage' => $data['pickup_mileage'] ?? null,
                ]);
            }

            return $booking->fresh()->load(['pickupProcedures.images']);
        });
    }

    /**
     * Submit return procedure (User action)
     */
    public function submitReturnProcedure(int $bookingId, int $userId, array $data): BookingProcedure
    {
        return DB::transaction(function () use ($bookingId, $userId, $data) {
            $booking = Booking::where('user_id', $userId)->findOrFail($bookingId);
            if ($booking->status !== BookingStatusEnum::Active->value) {
                throw new Exception('Only active bookings can have return procedures submitted');
            }

            // Update booking status to under_delivery when user submits return procedure
            $booking->update([
                'status' => BookingStatusEnum::UnderDelivery->value,
            ]);

            // Check if user already submitted return procedure
            $existingProcedure = $booking->returnProcedures()->byUser()->first();
            if ($existingProcedure) {
                throw new Exception('Return procedure already submitted');
            }

            $procedure = BookingProcedure::create([
                'booking_id' => $bookingId,
                'user_id' => $userId,
                'type' => 'return',
                'submitted_by' => 'user',
                'notes' => $data['notes'] ?? null,
            ]);

            // Handle image uploads
            if (isset($data['images']) && is_array($data['images'])) {
                foreach ($data['images'] as $imageData) {
                    $imagePath = $imageData['image']->store('booking-procedures', 'public');

                    BookingProcedureImage::create([
                        'booking_procedure_id' => $procedure->id,
                        'image_path' => $imagePath,
                        'image_type' => $imageData['image_type'],
                        'uploaded_by' => 'user',
                    ]);
                }
            }

            return $procedure->load('images');
        });
    }

    /**
     * Confirm return procedure (Vendor action)
     */
    public function confirmReturnProcedure(int $bookingId, int $vendorId, array $data): Booking
    {
        return DB::transaction(function () use ($bookingId, $vendorId, $data) {
            $booking = $this->getBookingForVendor($bookingId, $vendorId);

            if ($booking->status !== BookingStatusEnum::UnderDelivery->value) {
                throw new Exception('Only under delivery bookings can have return procedures confirmed');
            }

            $userProcedure = $booking->returnProcedures()->byUser()->first();
            if (!$userProcedure) {
                throw new Exception('User must submit return procedure before vendor can confirm');
            }

            // Create vendor procedure if not exists
            $vendorProcedure = $booking->returnProcedures()->byVendor()->first();
            if (!$vendorProcedure) {
                $vendorProcedure = BookingProcedure::create([
                    'booking_id' => $bookingId,
                    'user_id' => $booking->user_id,
                    'type' => 'return',
                    'submitted_by' => 'vendor',
                    'notes' => $data['notes'] ?? null,
                    'confirmed_by_vendor' => true,
                    'confirmed_at' => now(),
                ]);
            } else {
                $vendorProcedure->update([
                    'notes' => $data['notes'] ?? $vendorProcedure->notes,
                    'confirmed_by_vendor' => true,
                    'confirmed_at' => now(),
                ]);
            }

            // Handle vendor image uploads
            if (isset($data['images']) && is_array($data['images'])) {
                foreach ($data['images'] as $imageData) {
                    $imagePath = $imageData['image']->store('booking-procedures', 'public');

                    BookingProcedureImage::create([
                        'booking_procedure_id' => $vendorProcedure->id,
                        'image_path' => $imagePath,
                        'image_type' => $imageData['image_type'],
                        'uploaded_by' => 'vendor',
                    ]);
                }
            }

            // Mark user procedure as confirmed
            $userProcedure->markAsConfirmed();

            // Complete the booking
            $returnMileage = $data['return_mileage'] ?? null;
            if ($returnMileage) {
                // Validate return mileage and calculate fee if service is available
                if ($this->mileageValidationService) {
                    $mileageResult = $this->mileageValidationService->calculateMileageFeeWithValidation($bookingId, $returnMileage);

                    if (!$mileageResult['valid']) {
                        throw new Exception('Invalid return mileage: ' . implode(', ', $mileageResult['errors']));
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
            }

            return $booking->fresh()->load(['returnProcedures.images']);
        });
    }

    /**
     * Request extension for booking
     */
    public function requestExtension(int $bookingId, int $userId, array $data): Booking
    {
        return DB::transaction(function () use ($bookingId, $userId, $data) {
            $booking = Booking::where('user_id', $userId)->findOrFail($bookingId);

            if (!$booking->isActive()) {
                throw new Exception('Only active bookings can request extension');
            }

            $oldStatus = $booking->status;
            $booking->update([
                'status' => BookingStatusEnum::ExtensionRequested->value,
                'extension_reason' => $data['reason'],
                'requested_return_date' => $data['requested_return_date'],
            ]);

            // Dispatch status change event
            $this->dispatchStatusChangeEvent($booking, $oldStatus->value, $booking->status->value, [
                'changed_by_type' => 'user',
                'changed_by_id' => $userId,
                'notes' => 'Extension requested by user',
                'notify_vendor' => true,
            ]);

            return $booking->fresh();
        });
    }

    /**
     * Approve extension request (Vendor action)
     */
    public function approveExtension(int $bookingId, int $vendorId): Booking
    {
        return DB::transaction(function () use ($bookingId, $vendorId) {
            $booking = $this->getBookingForVendor($bookingId, $vendorId);

            if ($booking->status !== BookingStatusEnum::ExtensionRequested->value) {
                throw new Exception('Only extension requested bookings can be approved');
            }

            $oldStatus = $booking->status;
            $booking->update([
                'status' => BookingStatusEnum::Active->value,
                'return_date' => $booking->requested_return_date,
                'extension_reason' => null,
                'requested_return_date' => null,
            ]);

            // Dispatch status change event
            $this->dispatchStatusChangeEvent($booking, $oldStatus->value, $booking->status->value, [
                'changed_by_type' => 'vendor',
                'changed_by_id' => $vendorId,
                'notes' => 'Extension approved by vendor',
                'notify_vendor' => false,
            ]);

            return $booking->fresh();
        });
    }

    /**
     * Reject extension request (Vendor action)
     */
    public function rejectExtension(int $bookingId, int $vendorId): Booking
    {
        return DB::transaction(function () use ($bookingId, $vendorId) {
            $booking = $this->getBookingForVendor($bookingId, $vendorId);

            if ($booking->status !== BookingStatusEnum::ExtensionRequested->value) {
                throw new Exception('Only extension requested bookings can be rejected');
            }

            $oldStatus = $booking->status;
            $booking->update([
                'status' => BookingStatusEnum::Active->value,
                'extension_reason' => null,
                'requested_return_date' => null,
            ]);

            // Dispatch status change event
            $this->dispatchStatusChangeEvent($booking, $oldStatus->value, $booking->status->value, [
                'changed_by_type' => 'vendor',
                'changed_by_id' => $vendorId,
                'notes' => 'Extension rejected by vendor',
                'notify_vendor' => false,
            ]);

            return $booking->fresh();
        });
    }

    /**
     * Get booking procedures
     */
    public function getBookingProcedures(int $bookingId, int $userId, ?string $type = null): array
    {
        $booking = Booking::where('user_id', $userId)->findOrFail($bookingId);
        $query = $booking->procedures();

        if ($type) {
            $query->where('type', $type);
        }

        $procedures = $query->with('images')->get();
        return [
            'pickup' => $procedures->where('type', 'pickup')->values(),
            'return' => $procedures->where('type', 'return')->values(),
        ];
    }

    /**
     * Get vendor booking procedures
     */
    public function getVendorBookingProcedures(int $bookingId, int $vendorId, ?string $type = null): array
    {
        $booking = $this->getBookingForVendor($bookingId, $vendorId);

        $query = $booking->procedures();

        if ($type) {
            $query->where('type', $type);
        }

        $procedures = $query->with('images')->get();

        return [
            'pickup' => $procedures->where('type', 'pickup')->values(),
            'return' => $procedures->where('type', 'return')->values(),
        ];
    }

    /**
     * Move booking to under review (Vendor action)
     */
    public function moveToUnderReview(int $bookingId, int $vendorId): Booking
    {
        return DB::transaction(function () use ($bookingId, $vendorId) {
            $booking = $this->getBookingForVendor($bookingId, $vendorId);

            if (!in_array($booking->status, [BookingStatusEnum::Pending->value, BookingStatusEnum::Confirmed->value, BookingStatusEnum::Active->value])) {
                throw new Exception('Only pending, confirmed, or active bookings can be moved to under review');
            }

            $oldStatus = $booking->status;
            $booking->update([
                'status' => BookingStatusEnum::UnderReview->value,
            ]);

            // Dispatch status change event
            $this->dispatchStatusChangeEvent($booking, $oldStatus->value, $booking->status->value, [
                'changed_by_type' => 'vendor',
                'changed_by_id' => $vendorId,
                'notes' => 'Booking moved to under review by vendor',
                'notify_vendor' => false,
            ]);

            return $booking->fresh();
        });
    }

    /**
     * Open dispute for booking (Vendor action)
     */
    public function openDispute(int $bookingId, int $vendorId, ?string $reason = null): Booking
    {
        return DB::transaction(function () use ($bookingId, $vendorId, $reason) {
            $booking = $this->getBookingForVendor($bookingId, $vendorId);

            if (!in_array($booking->status, [BookingStatusEnum::Active->value, BookingStatusEnum::Completed->value, BookingStatusEnum::UnderDelivery->value])) {
                throw new Exception('Only active, completed, or under delivery bookings can have disputes opened');
            }

            $oldStatus = $booking->status;
            $booking->update([
                'status' => BookingStatusEnum::DisputeOpened->value,
                'dispute_reason' => $reason,
                'dispute_opened_at' => now(),
            ]);

            // Dispatch status change event
            $this->dispatchStatusChangeEvent($booking, $oldStatus->value, $booking->status->value, [
                'changed_by_type' => 'vendor',
                'changed_by_id' => $vendorId,
                'notes' => 'Dispute opened by vendor: ' . ($reason ?? 'No reason provided'),
                'notify_vendor' => false,
            ]);

            return $booking->fresh();
        });
    }

    /**
     * Move booking under dispute (Vendor action)
     */
    public function moveUnderDispute(int $bookingId, int $vendorId, ?string $reason = null): Booking
    {
        return DB::transaction(function () use ($bookingId, $vendorId, $reason) {
            $booking = $this->getBookingForVendor($bookingId, $vendorId);

            if (!in_array($booking->status, [BookingStatusEnum::Active->value, BookingStatusEnum::Completed->value, BookingStatusEnum::UnderDelivery->value])) {
                throw new Exception('Only active, completed, or under delivery bookings can be moved under dispute');
            }

            $oldStatus = $booking->status;
            $booking->update([
                'status' => BookingStatusEnum::UnderDispute->value,
                'dispute_reason' => $reason,
                'dispute_opened_at' => now(),
            ]);

            // Dispatch status change event
            $this->dispatchStatusChangeEvent($booking, $oldStatus->value, $booking->status->value, [
                'changed_by_type' => 'vendor',
                'changed_by_id' => $vendorId,
                'notes' => 'Booking moved under dispute by vendor: ' . ($reason ?? 'No reason provided'),
                'notify_vendor' => false,
            ]);

            return $booking->fresh();
        });
    }

}
