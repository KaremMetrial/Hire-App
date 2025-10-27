<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\BookingCalcPriceRequest;
use App\Http\Requests\User\CreateBookingRequest;
use App\Http\Requests\User\CancelBookingRequest;
use App\Http\Resources\BookingResource;
use App\Services\BookingService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    use ApiResponse;

    public function __construct(private BookingService $bookingService) {}

    /**
     * Calculate booking price
     */
    public function calculatePrice(BookingCalcPriceRequest $request): JsonResponse
    {
        try {
            $priceDetails = $this->bookingService->calculatePrice($request->all());

            return $this->successResponse([
                'price_details' => $priceDetails,
            ], 'Price calculated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Create a new booking
     */
    public function store(CreateBookingRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $booking = $this->bookingService->createBooking(
                $request->validated(),
                auth()->id()
            );
            DB::commit();
            return $this->successResponse([
                'booking' => new BookingResource($booking->load(['car', 'rentalShop', 'payments', 'extraServices', 'insurances', 'documents'])),
                'booking_number' => $booking->booking_number,
            ], 'Booking created successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get user bookings
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $status = $request->get('status');
            $perPage = $request->get('per_page', 15);

            $bookings = $this->bookingService->getUserBookings(
                auth()->id(),
                $status,
                $perPage
            );

            return $this->successResponse([
                'bookings' => BookingResource::collection($bookings),
                'pagination' => [
                    'current_page' => $bookings->currentPage(),
                    'last_page' => $bookings->lastPage(),
                    'per_page' => $bookings->perPage(),
                    'total' => $bookings->total(),
                ],
            ], 'Bookings retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get specific booking details
     */
    public function show(int $id): JsonResponse
    {
        try {
            $booking = $this->bookingService->getUserBooking($id, auth()->id());

            return $this->successResponse([
                'booking' => new BookingResource($booking->load([
                    'car.carModel',
                    'car.images',
                    'rentalShop',
                    'payments',
                    'extraServices',
                    'insurances',
                    'documents'
                ])),
            ], 'Booking retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    /**
     * Cancel booking
     */
    public function cancel(CancelBookingRequest $request, int $id): JsonResponse
    {
        try {
            $booking = $this->bookingService->cancelBooking(
                $id,
                auth()->id(),
                $request->get('reason')
            );

            return $this->successResponse([
                'booking' => new BookingResource($booking),
            ], 'Booking cancelled successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get booking history
     */
    public function history(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $bookings = $this->bookingService->getUserBookings(
                auth()->id(),
                null,
                $perPage
            );

            // Group by status for better organization
            $groupedBookings = $bookings->getCollection()->groupBy('status');

            return $this->successResponse([
                'bookings' => [
                    'active' => BookingResource::collection($groupedBookings->get('active', collect())),
                    'completed' => BookingResource::collection($groupedBookings->get('completed', collect())),
                    'cancelled' => BookingResource::collection($groupedBookings->get('cancelled', collect())),
                    'rejected' => BookingResource::collection($groupedBookings->get('rejected', collect())),
                ],
                'pagination' => [
                    'current_page' => $bookings->currentPage(),
                    'last_page' => $bookings->lastPage(),
                    'per_page' => $bookings->perPage(),
                    'total' => $bookings->total(),
                ],
            ], 'Booking history retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get upcoming bookings
     */
    public function upcoming(): JsonResponse
    {
        try {
            $bookings = $this->bookingService->getUserBookings(
                auth()->id(),
                'confirmed',
                10
            );

            return $this->successResponse([
                'bookings' => BookingResource::collection($bookings),
            ], 'Upcoming bookings retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get booking statistics for user
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->bookingService->getUserBookingStats(auth()->id());

            return $this->successResponse([
                'stats' => $stats,
            ], 'Booking statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
