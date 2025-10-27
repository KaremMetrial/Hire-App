<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\SubmitReviewRequest;
use App\Services\AutoReviewService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    use ApiResponse;

    public function __construct(private AutoReviewService $autoReviewService) {}

    /**
     * Get pending reviews for the authenticated user
     */
    public function pending(): JsonResponse
    {
        try {
            $pendingReviews = $this->autoReviewService->getPendingReviewsForUser(auth()->id());

            return $this->successResponse([
                'pending_reviews' => $pendingReviews,
            ], 'Pending reviews retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Submit a review using token
     */
    public function submitWithToken(SubmitReviewRequest $request, string $token): JsonResponse
    {
        try {
            $review = $this->autoReviewService->submitReviewWithToken($token, $request->validated());

            return $this->successResponse([
                'review' => $review,
            ], 'Review submitted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Submit a review for a specific booking
     */
    public function submit(SubmitReviewRequest $request, int $bookingId): JsonResponse
    {
        try {
            $booking = \App\Models\Booking::where('user_id', auth()->id())
                ->findOrFail($bookingId);

            $review = $this->autoReviewService->submitReview($booking, $request->validated());

            return $this->successResponse([
                'review' => $review,
            ], 'Review submitted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get review statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $rentalShopId = $request->get('rental_shop_id');
            $carId = $request->get('car_id');

            $stats = $this->autoReviewService->getReviewStatistics($rentalShopId, $carId);

            return $this->successResponse([
                'statistics' => $stats,
            ], 'Review statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
