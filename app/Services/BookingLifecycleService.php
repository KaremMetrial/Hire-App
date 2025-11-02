<?php

namespace App\Services;

use App\Models\Booking;
use App\Enums\BookingStatusEnum;
use Exception;

class BookingLifecycleService
{
    public function __construct(
        protected BookingManagementService $bookingManagementService,
        protected AccidentReportService $accidentReportService
    ) {}

    /**
     * Handle complete booking lifecycle from creation to completion
     */
    public function processBookingLifecycle(Booking $booking): void
    {
        $this->validateBookingState($booking);

        switch ($booking->status) {
            case BookingStatusEnum::Pending->value:
                $this->handlePendingBooking($booking);
                break;
            case BookingStatusEnum::Confirmed->value:
                $this->handleConfirmedBooking($booking);
                break;
            case BookingStatusEnum::Active->value:
                $this->handleActiveBooking($booking);
                break;
            case BookingStatusEnum::UnderDelivery->value:
                $this->handleUnderDeliveryBooking($booking);
                break;
            case BookingStatusEnum::Completed->value:
                $this->handleCompletedBooking($booking);
                break;
        }
    }

    /**
     * Handle pending booking state
     */
    protected function handlePendingBooking(Booking $booking): void
    {
        // Check if booking needs information
        if ($this->needsAdditionalInfo($booking)) {
            $booking->update(['status' => BookingStatusEnum::InfoRequested->value]);
        }

        // Check for auto-confirmation if configured
        if ($this->shouldAutoConfirm($booking)) {
            // This would be handled by a scheduled job or admin setting
        }
    }

    /**
     * Handle confirmed booking state
     */
    protected function handleConfirmedBooking(Booking $booking): void
    {
        // Check if pickup date has passed and booking should be started
        if ($booking->pickup_date->isPast() && !$booking->pickup_mileage) {
            // Could trigger notifications or automatic status changes
        }

        // Check for procedure submissions
        $this->checkProcedureRequirements($booking);
    }

    /**
     * Handle active booking state
     */
    protected function handleActiveBooking(Booking $booking): void
    {
        // Check return date approaching
        if ($booking->return_date->isToday()) {
            // Trigger return reminders
        }

        // Check for overdue bookings
        if ($booking->return_date->isPast()) {
            $booking->update(['status' => BookingStatusEnum::UnreasonableDelay->value]);
        }

        // Check for extension requests
        $this->checkExtensionRequests($booking);
    }

    /**
     * Handle under delivery booking state
     */
    protected function handleUnderDeliveryBooking(Booking $booking): void
    {
        // Check if return procedure is complete
        if ($this->isReturnProcedureComplete($booking)) {
            // Could auto-complete booking or notify vendor
        }
    }

    /**
     * Handle completed booking state
     */
    protected function handleCompletedBooking(Booking $booking): void
    {
        // Trigger post-completion processes
        $this->triggerPostCompletionProcesses($booking);
    }

    /**
     * Validate booking state consistency
     */
    protected function validateBookingState(Booking $booking): void
    {
        // Add business rule validations
        if ($booking->status === BookingStatusEnum::Active->value && !$booking->pickup_mileage) {
            throw new Exception('Active booking must have pickup mileage');
        }

        if ($booking->status === BookingStatusEnum::Completed->value && !$booking->return_mileage) {
            throw new Exception('Completed booking must have return mileage');
        }
    }

    /**
     * Check if booking needs additional information
     */
    protected function needsAdditionalInfo(Booking $booking): bool
    {
        return $booking->informationRequests()->where('status', 'pending')->exists();
    }

    /**
     * Check if booking should be auto-confirmed
     */
    protected function shouldAutoConfirm(Booking $booking): bool
    {
        // Implementation based on business rules
        return false; // Placeholder
    }

    /**
     * Check procedure requirements
     */
    protected function checkProcedureRequirements(Booking $booking): void
    {
        // Check if pickup procedure is required and submitted
        if ($this->requiresPickupProcedure($booking) && !$this->hasPickupProcedure($booking)) {
            // Could trigger notifications
        }
    }

    /**
     * Check extension requests
     */
    protected function checkExtensionRequests(Booking $booking): void
    {
        if ($booking->status === BookingStatusEnum::ExtensionRequested->value) {
            // Handle pending extension requests
        }
    }

    /**
     * Check if return procedure is complete
     */
    protected function isReturnProcedureComplete(Booking $booking): bool
    {
        return $booking->returnProcedures()->where('confirmed_by_vendor', true)->exists();
    }

    /**
     * Trigger post-completion processes
     */
    protected function triggerPostCompletionProcesses(Booking $booking): void
    {
        // Trigger review creation, notifications, etc.
        // This would use the existing AutoReviewService
    }

    /**
     * Check if pickup procedure is required
     */
    protected function requiresPickupProcedure(Booking $booking): bool
    {
        // Business logic to determine if procedure is required
        return true; // Most bookings require procedures
    }

    /**
     * Check if booking has pickup procedure
     */
    protected function hasPickupProcedure(Booking $booking): bool
    {
        return $booking->pickupProcedures()->exists();
    }
}
