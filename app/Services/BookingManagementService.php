<?php

namespace App\Services;

use App\Contracts\BookingStatusChangeInterface;
use App\Events\BookingStatusChanged;
use App\Models\Booking;
use App\Enums\BookingStatusEnum;
use Exception;
use Illuminate\Support\Facades\Log;

class BookingManagementService
{
    public function __construct(
        protected BookingService $bookingService,
        protected iterable $statusChangeHandlers = []
    ) {}

    /**
     * Confirm booking with event dispatching
     */
    public function confirmBooking(int $bookingId, int $vendorId): Booking
    {
        $booking = $this->bookingService->confirmBooking($bookingId, $vendorId);

        $this->dispatchStatusChangeEvent($booking, BookingStatusEnum::Pending->value, BookingStatusEnum::Confirmed->value, [
            'changed_by_type' => 'vendor',
            'changed_by_id' => $vendorId,
            'notes' => 'Booking confirmed by vendor',
            'notify_vendor' => false, // Vendor is the one making the change
        ]);

        return $booking;
    }

    /**
     * Start booking with event dispatching
     */
    public function startBooking(int $bookingId, int $pickupMileage): Booking
    {
        $booking = $this->bookingService->startBooking($bookingId, $pickupMileage);

        $this->dispatchStatusChangeEvent($booking, BookingStatusEnum::Confirmed->value, BookingStatusEnum::Active->value, [
            'changed_by_type' => 'vendor',
            'changed_by_id' => $booking->car->rentalShop->vendors->first()->id,
            'notes' => "Booking started with mileage: {$pickupMileage}",
            'notify_vendor' => false,
        ]);

        return $booking;
    }

    /**
     * Complete booking with event dispatching
     */
    public function completeBooking(int $bookingId, int $returnMileage): Booking
    {
        $booking = $this->bookingService->completeBooking($bookingId, $returnMileage);

        $this->dispatchStatusChangeEvent($booking, BookingStatusEnum::Active->value, BookingStatusEnum::Completed->value, [
            'changed_by_type' => 'vendor',
            'changed_by_id' => $booking->car->rentalShop->vendors->first()->id,
            'notes' => "Booking completed with return mileage: {$returnMileage}",
            'notify_vendor' => false,
        ]);

        return $booking;
    }

    /**
     * Cancel booking with event dispatching
     */
    public function cancelBooking(int $bookingId, int $userId, ?string $reason = null): Booking
    {
        $booking = $this->bookingService->cancelBooking($bookingId, $userId, $reason);

        $this->dispatchStatusChangeEvent($booking, $booking->getOriginal('status'), BookingStatusEnum::Cancelled->value, [
            'changed_by_type' => 'user',
            'changed_by_id' => $userId,
            'notes' => $reason ?? 'Booking cancelled by user',
            'notify_vendor' => true,
        ]);

        return $booking;
    }

    /**
     * Request extension with event dispatching
     */
    public function requestExtension(int $bookingId, int $userId, array $data): Booking
    {
        $booking = $this->bookingService->requestExtension($bookingId, $userId, $data);

        $this->dispatchStatusChangeEvent($booking, BookingStatusEnum::Active->value, BookingStatusEnum::ExtensionRequested->value, [
            'changed_by_type' => 'user',
            'changed_by_id' => $userId,
            'notes' => 'Extension requested by user',
            'notify_vendor' => true,
        ]);

        return $booking;
    }

    /**
     * Approve extension with event dispatching
     */
    public function approveExtension(int $bookingId, int $vendorId): Booking
    {
        $booking = $this->bookingService->approveExtension($bookingId, $vendorId);

        $this->dispatchStatusChangeEvent($booking, BookingStatusEnum::ExtensionRequested->value, BookingStatusEnum::Active->value, [
            'changed_by_type' => 'vendor',
            'changed_by_id' => $vendorId,
            'notes' => 'Extension approved by vendor',
            'notify_vendor' => false,
        ]);

        return $booking;
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
     * Add a status change handler
     */
    public function addStatusChangeHandler(BookingStatusChangeInterface $handler): void
    {
        $this->statusChangeHandlers[] = $handler;
    }

    /**
     * Get all status change handlers
     */
    public function getStatusChangeHandlers(): iterable
    {
        return $this->statusChangeHandlers;
    }
}
