<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateBookingStatusRequest;
use App\Http\Resources\BookingResource;
use App\Services\BookingService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    use ApiResponse;

    public function __construct(private BookingService $bookingService) {}

    /**
     * Get all bookings with filters
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $status = $request->get('status');
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            $perPage = $request->get('per_page', 15);

            $bookings = $this->bookingService->getAllBookings(
                $status,
                $dateFrom,
                $dateTo,
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
            $booking = $this->bookingService->getBookingById($id);

            return $this->successResponse([
                'booking' => new BookingResource($booking->load([
                    'user',
                    'car.carModel',
                    'car.images',
                    'rentalShop.vendor',
                    'payments',
                    'extraServices',
                    'insurances',
                    'documents',
                    'statusLogs.changedBy'
                ])),
            ], 'Booking retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    /**
     * Update booking status (Admin override)
     */
    public function updateStatus(UpdateBookingStatusRequest $request, int $id): JsonResponse
    {
        try {
            $booking = $this->bookingService->updateBookingStatus(
                $id,
                $request->get('status'),
                $request->get('reason'),
                'admin',
                auth()->id()
            );

            return $this->successResponse([
                'booking' => new BookingResource($booking),
            ], 'Booking status updated successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Force cancel booking (Admin action)
     */
    public function forceCancel(Request $request, int $id): JsonResponse
    {
        try {
            $booking = $this->bookingService->forceCancelBooking(
                $id,
                $request->get('reason'),
                auth()->id()
            );

            return $this->successResponse([
                'booking' => new BookingResource($booking),
            ], 'Booking force cancelled successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get booking statistics
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->bookingService->getBookingStats();

            return $this->successResponse([
                'stats' => $stats,
            ], 'Booking statistics retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get booking analytics
     */
    public function analytics(Request $request): JsonResponse
    {
        try {
            $period = $request->get('period', 'month'); // day, week, month, year
            $analytics = $this->bookingService->getBookingAnalytics($period);

            return $this->successResponse([
                'analytics' => $analytics,
            ], 'Booking analytics retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get revenue report
     */
    public function revenue(Request $request): JsonResponse
    {
        try {
            $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
            $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());

            $revenue = $this->bookingService->getRevenueReport($startDate, $endDate);

            return $this->successResponse([
                'revenue' => $revenue,
            ], 'Revenue report retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get problematic bookings
     */
    public function problematic(): JsonResponse
    {
        try {
            $bookings = $this->bookingService->getProblematicBookings();

            return $this->successResponse([
                'bookings' => BookingResource::collection($bookings),
            ], 'Problematic bookings retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get vendor performance
     */
    public function vendorPerformance(): JsonResponse
    {
        try {
            $performance = $this->bookingService->getVendorPerformanceMetrics();

            return $this->successResponse([
                'performance' => $performance,
            ], 'Vendor performance metrics retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Export bookings
     */
    public function export(Request $request): JsonResponse
    {
        try {
            $format = $request->get('format', 'csv'); // csv, excel, pdf
            $filters = $request->only(['status', 'date_from', 'date_to', 'vendor_id']);

            $exportUrl = $this->bookingService->exportBookings($filters, $format);

            return $this->successResponse([
                'download_url' => $exportUrl,
            ], 'Bookings exported successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get booking trends
     */
    public function trends(Request $request): JsonResponse
    {
        try {
            $period = $request->get('period', '30'); // days
            $trends = $this->bookingService->getBookingTrends($period);

            return $this->successResponse([
                'trends' => $trends,
            ], 'Booking trends retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get car utilization report
     */
    public function carUtilization(Request $request): JsonResponse
    {
        try {
            $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
            $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());

            $utilization = $this->bookingService->getCarUtilizationReport($startDate, $endDate);

            return $this->successResponse([
                'utilization' => $utilization,
            ], 'Car utilization report retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get customer insights
     */
    public function customerInsights(): JsonResponse
    {
        try {
            $insights = $this->bookingService->getCustomerInsights();

            return $this->successResponse([
                'insights' => $insights,
            ], 'Customer insights retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Bulk update booking statuses
     */
    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        try {
            $bookingIds = $request->get('booking_ids', []);
            $status = $request->get('status');
            $reason = $request->get('reason');

            $results = $this->bookingService->bulkUpdateBookingStatus(
                $bookingIds,
                $status,
                $reason,
                'admin',
                auth()->id()
            );

            return $this->successResponse([
                'results' => $results,
            ], 'Bulk status update completed successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
