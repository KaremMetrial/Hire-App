<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\BookingCalcPriceRequest;
use App\Http\Requests\User\CreateBookingRequest;
use App\Http\Requests\User\CancelBookingRequest;
use App\Http\Requests\User\ReportPickupIssueRequest;
use App\Http\Requests\User\SubmitBookingInfoRequest;
use App\Http\Requests\User\SubmitPickupProcedureRequest;
use App\Http\Requests\User\SubmitReturnProcedureRequest;
use App\Http\Requests\User\SubmitAccidentReportRequest;
use App\Http\Requests\User\RequestExtensionRequest;
use App\Http\Resources\BookingProcedureResource;
use App\Http\Resources\BookingResource;
use App\Http\Resources\BookingAccidentReportResource;
use App\Http\Resources\PaginationResource;
use App\Services\BookingService;
use App\Services\AccidentReportService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    use ApiResponse;

    public function __construct(
        private BookingService $bookingService,
        private AccidentReportService $accidentReportService
    ) {
    }

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
        $status = $request->get('status');
        $type = $request->get('type'); // 'current' or 'completed'

        $statuses = null;
        if ($type === 'current') {
            $statuses = ['pending', 'confirmed', 'active', 'info_requested', 'under_delivery', 'accident_reported'];
        } elseif ($type === 'completed') {
            $statuses = ['completed', 'cancelled', 'rejected'];
        } elseif ($status) {
            $statuses = [$status];
        }

        $bookings = $this->bookingService->getUserBookings(
            auth()->id(),
            $statuses,
        );
        return $this->successResponse([
            'bookings' => BookingResource::collection($bookings->load('accidentReport')),
            'pagination' => new PaginationResource($bookings),
        ], 'message.success');
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
                    'documents',
                    'informationRequests',
                    'accidentReport'
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
                    'info_requested' => BookingResource::collection($groupedBookings->get('info_requested', collect())),
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

    /**
     * Submit additional information for booking
     */
    public function submitInfo(SubmitBookingInfoRequest $request, int $id): JsonResponse
    {
        try {
            $booking = $this->bookingService->submitBookingInfo(
                $id,
                auth()->id(),
                $request->validated()
            );

            return $this->successResponse([
                'booking' => new BookingResource($booking->load(['car', 'rentalShop', 'payments', 'extraServices', 'insurances', 'documents', 'informationRequests'])),
            ], 'Information submitted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Report pickup issue
     */
    public function reportPickupIssue(ReportPickupIssueRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();

            $booking = $this->bookingService->getUserBooking($id, auth()->id());

            // Check if booking is in confirmed status (waiting for pickup)
            if ($booking->status !== \App\Enums\BookingStatusEnum::Confirmed) {
                return $this->errorResponse('You can only report pickup issues for confirmed bookings', 400);
            }

            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('pickup-issues', 'public');
            }

            $issue = \App\Models\BookingPickupIssue::create([
                'booking_id' => $booking->id,
                'user_id' => auth()->id(),
                'problem_details' => $validated['problem_details'],
                'image_path' => $imagePath,
            ]);

            return $this->successResponse([
                'issue' => $issue,
            ], 'Pickup issue reported successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Submit pickup procedure
     */
    public function submitPickupProcedure(SubmitPickupProcedureRequest $request, int $id): JsonResponse
    {
        try {
            $procedure = $this->bookingService->submitPickupProcedure(
                $id,
                auth()->id(),
                $request->validated()
            );
            return $this->successResponse([
                'procedure' => new BookingProcedureResource($procedure->load('images')),
            ], 'Pickup procedure submitted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Submit return procedure
     */
    public function submitReturnProcedure(SubmitReturnProcedureRequest $request, int $id): JsonResponse
    {
        try {
            $procedure = $this->bookingService->submitReturnProcedure(
                $id,
                auth()->id(),
                $request->validated()
            );

            return $this->successResponse([
                'procedure' => new BookingProcedureResource($procedure),
            ], 'Return procedure submitted successfully');
        } catch (\Exception $e) {
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
            $procedures = $this->bookingService->getBookingProcedures(
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
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Submit accident report
     */
    public function submitAccidentReport(SubmitAccidentReportRequest $request): JsonResponse
    {
        try {
            $report = $this->accidentReportService->submitAccidentReport(
                $request->validated(),
                auth()->id()
            );

            return $this->successResponse([
                'accident_report' => new BookingAccidentReportResource($report->load('booking.car.carModel')),
            ], 'Accident report submitted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get user's accident reports
     */
    public function getAccidentReports(Request $request): JsonResponse
    {
        try {
            $status = $request->get('status');
            $reports = $this->accidentReportService->getUserAccidentReports(auth()->id(), $status);

            return $this->successResponse([
                'accident_reports' => BookingAccidentReportResource::collection($reports),
            ], 'Accident reports retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get specific accident report
     */
    public function getAccidentReport(int $id): JsonResponse
    {
        try {
            $report = $this->accidentReportService->getAccidentReport($id, auth()->id());

            return $this->successResponse([
                'accident_report' => new BookingAccidentReportResource($report),
            ], 'Accident report retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    /**
     * Request extension for booking
     */
    public function requestExtension(RequestExtensionRequest $request, int $id): JsonResponse
    {
        try {
            $booking = $this->bookingService->requestExtension(
                $id,
                auth()->id(),
                $request->validated()
            );

            return $this->successResponse([
                'booking' => new BookingResource($booking),
            ], 'Extension request submitted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
