<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\CompleteBookingRequest;
use App\Http\Requests\Vendor\ConfirmBookingRequest;
use App\Http\Requests\Vendor\ConfirmPickupProcedureRequest;
use App\Http\Requests\Vendor\ConfirmReturnProcedureRequest;
use App\Http\Requests\Vendor\RejectBookingRequest;
use App\Http\Requests\Vendor\RequestInfoRequest;
use App\Http\Requests\Vendor\StartBookingRequest;
use App\Http\Resources\BookingProcedureResource;
use App\Http\Resources\BookingResource;
use App\Http\Resources\PaginationResource;
use App\Services\BookingService;
use App\Services\BookingTimerService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    use ApiResponse;

    public function __construct(
        private BookingService $bookingService,
        private BookingTimerService $bookingTimerService
    ) {}

    /**
     * Get all bookings for vendor's rental shops
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $bookings = $this->bookingService->getVendorBookings(
                auth()->id(),
                $request->get('status'),
                $request->get('per_page', 15)
            );

            return $this->successResponse([
                'bookings' => BookingResource::collection($bookings),
                'pagination' => new PaginationResource($bookings),
            ], 'Bookings retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get specific booking details
     */
    public function show(int $id): JsonResponse
    {
        try {
            $booking = $this->bookingService->getVendorBooking($id, auth()->id());

            return $this->successResponse([
                'booking' => new BookingResource($booking->load([
                    'user',
                    'car.carModel',
                    'car.images',
                    'rentalShop',
                    'payments',
                    'extraServices',
                    'insurances',
                    'documents',
                    'statusLogs',
                    'informationRequests'
                ])),
            ], 'Booking retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    /**
     * Confirm booking
     */
    public function confirm(ConfirmBookingRequest $request, int $id): JsonResponse
    {
        try {
            $booking = $this->bookingService->confirmBooking($id, auth()->id());

            return $this->successResponse([
                'booking' => new BookingResource($booking),
            ], 'Booking confirmed successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Reject booking
     */
    public function reject(RejectBookingRequest $request, int $id): JsonResponse
    {
        try {
            $booking = $this->bookingService->rejectBooking(
                $id,
                $request->get('rejection_reason'),
                auth()->id()
            );

            return $this->successResponse([
                'booking' => new BookingResource($booking),
            ], 'Booking rejected successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Start booking (car pickup)
     */
    public function start(StartBookingRequest $request, int $id): JsonResponse
    {
        try {
            $booking = $this->bookingService->startBooking(
                $id,
                $request->get('pickup_mileage')
            );

            return $this->successResponse([
                'booking' => new BookingResource($booking),
            ], 'Booking started successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Complete booking (car return)
     */
    public function complete(CompleteBookingRequest $request, int $id): JsonResponse
    {
        try {
            $booking = $this->bookingService->completeBooking(
                $id,
                $request->get('return_mileage')
            );

            return $this->successResponse([
                'booking' => new BookingResource($booking),
            ], 'Booking completed successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get upcoming bookings
     */
    public function upcoming(): JsonResponse
    {
        try {
            $bookings = $this->bookingService->getUpcomingBookings(auth()->id(), 7);

            return $this->successResponse([
                'bookings' => BookingResource::collection($bookings),
            ], 'Upcoming bookings retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get today's bookings
     */
    public function today(): JsonResponse
    {
        try {
            $bookings = $this->bookingService->getVendorBookings(
                auth()->id(),
                null,
                50
            );

            $todayBookings = $bookings->getCollection()->filter(function ($booking) {
                return now()->parse($booking->pickup_date)->isToday() ||
                       now()->parse($booking->return_date)->isToday();
            });

            return $this->successResponse([
                'bookings' => BookingResource::collection($todayBookings),
            ], 'Today\'s bookings retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get active bookings
     */
    public function active(): JsonResponse
    {
        try {
            $bookings = $this->bookingService->getVendorBookings(
                auth()->id(),
                'active',
                50
            );

            return $this->successResponse([
                'bookings' => BookingResource::collection($bookings),
            ], 'Active bookings retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get booking statistics for vendor
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->bookingService->getBookingStats(auth()->id());

            return $this->successResponse([
                'stats' => $stats,
            ], 'Booking statistics retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get booking calendar view
     */
    public function calendar(Request $request): JsonResponse
    {
        try {
            $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
            $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());

            $bookings = $this->bookingService->getVendorBookingsByDateRange(
                auth()->id(),
                $startDate,
                $endDate
            );

            $calendarData = $bookings->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'title' => $booking->car->carModel->name,
                    'start' => $booking->pickup_date,
                    'end' => $booking->return_date,
                    'status' => $booking->status,
                    'customer' => $booking->user->name,
                    'color' => $this->getBookingColor($booking->status),
                ];
            });

            return $this->successResponse([
                'bookings' => $calendarData,
            ], 'Calendar data retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get booking status logs
     */
    public function statusLogs(int $id): JsonResponse
    {
        try {
            $booking = $this->bookingService->getVendorBooking($id, auth()->id());
            $logs = $booking->statusLogs()->with('changedBy')->orderBy('created_at', 'desc')->get();

            return $this->successResponse([
                'logs' => $logs,
            ], 'Status logs retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Request additional information from user
     */
    public function requestInfo(RequestInfoRequest $request, int $id): JsonResponse
    {
        try {
            $booking = $this->bookingService->requestBookingInfo(
                $id,
                auth()->id(),
                $request->get('information_requests')
            );

            return $this->successResponse([
                'booking' => new BookingResource($booking->load('informationRequests')),
            ], 'Information request sent successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get remaining acceptance time for a booking
     */
    public function getRemainingAcceptanceTime(int $id): JsonResponse
    {
        try {
            $booking = $this->bookingService->getVendorBooking($id, auth()->id());

            // Calculate remaining acceptance time considering working hours
            $remainingTime = $this->bookingTimerService->calculateRemainingAcceptanceTime($booking);

            return $this->successResponse([
                'booking_id' => $booking->id,
                'booking_status' => $booking->status,
                'remaining_acceptance_time' => $remainingTime,
            ], 'Remaining acceptance time retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Confirm pickup procedure
     */
    public function confirmPickupProcedure(ConfirmPickupProcedureRequest $request, int $id): JsonResponse
    {
        try {
            $booking = $this->bookingService->confirmPickupProcedure(
                $id,
                auth()->id(),
                $request->validated()
            );

            return $this->successResponse([
                'booking' => new BookingResource($booking->load(['pickupProcedures.images'])),
            ], 'Pickup procedure confirmed successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Confirm return procedure
     */
    public function confirmReturnProcedure(ConfirmReturnProcedureRequest $request, int $id): JsonResponse
    {
        try {
            $booking = $this->bookingService->confirmReturnProcedure(
                $id,
                auth()->id(),
                $request->validated()
            );

            return $this->successResponse([
                'booking' => new BookingResource($booking->load(['returnProcedures.images'])),
            ], 'Return procedure confirmed successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get booking procedures
     */
    public function getProcedures(int $id, Request $request): JsonResponse
    {
        try {
            $type = $request->get('type'); // 'pickup' or 'return'
            $procedures = $this->bookingService->getVendorBookingProcedures(
                $id,
                auth()->id(),
                $type
            );

            return $this->successResponse([
                'procedures' => [
                    'pickup' => BookingProcedureResource::collection($procedures['pickup']),
                    'return' => BookingProcedureResource::collection($procedures['return']),
                ],
            ], 'Procedures retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get color for booking status
     */
    private function getBookingColor(string $status): string
    {
        return match ($status) {
            'pending' => '#FFA500',
            'confirmed' => '#008000',
            'active' => '#0000FF',
            'completed' => '#808080',
            'cancelled' => '#FF0000',
            'rejected' => '#8B0000',
            default => '#000000',
        };
    }
}
