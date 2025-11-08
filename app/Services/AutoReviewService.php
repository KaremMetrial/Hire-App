<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingReview;
use App\Models\User;
use App\Notifications\ReviewRequestNotification;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AutoReviewService
{
    private const REVIEW_REQUEST_DELAY_HOURS = 2; // Send review request 2 hours after completion
    private const REVIEW_EXPIRY_DAYS = 30; // Review link expires after 30 days

    /**
     * Create review request for completed booking
     */
    public function createReviewForCompletedBooking(Booking $booking): void
    {
        try {
            // Check if review already exists
            if ($booking->review) {
                Log::info("Review already exists for booking {$booking->id}");
                return;
            }

            // Schedule review request notification
            $this->scheduleReviewRequest($booking);

            Log::info("Review request scheduled for booking {$booking->id}");

        } catch (\Exception $e) {
            Log::error("Failed to create review request for booking {$booking->id}: " . $e->getMessage());
        }
    }

    /**
     * Schedule review request notification
     */
    private function scheduleReviewRequest(Booking $booking): void
    {
        // You can use Laravel's job queue for this
        // For now, we'll create a pending review record
        $this->createPendingReview($booking);
    }

    /**
     * Create pending review record
     */
    private function createPendingReview(Booking $booking): void
    {
        BookingReview::create([
            'booking_id' => $booking->id,
            'user_id' => $booking->user_id,
            'rental_shop_id' => $booking->rental_shop_id,
            'car_id' => $booking->car_id,
            'rating' => null, // Will be filled by user
            'cleanliness_rating' => null,
            'service_rating' => null,
            'value_rating' => null,
            'comment' => null,
            'is_approved' => false, // Will be approved after submission
        ]);

        // Send notification to user (if notification system exists)
        $this->sendReviewRequestNotification($booking);
    }

    /**
     * Send review request notification to user
     */
    private function sendReviewRequestNotification(Booking $booking): void
    {
        try {
            $user = $booking->user;

            // Create review token for secure access
            $reviewToken = $this->generateReviewToken($booking);

            // Store token temporarily
            cache()->put(
                "review_token_{$reviewToken}",
                [
                    'booking_id' => $booking->id,
                    'user_id' => $user->id,
                    'expires_at' => now()->addDays(self::REVIEW_EXPIRY_DAYS)
                ],
                self::REVIEW_EXPIRY_DAYS * 24 * 60 // 30 days in minutes
            );

            // Store token in booking token list for cleanup
            $tokenListKey = "booking_tokens_{$booking->id}";
            $existingTokens = cache()->get($tokenListKey, []);
            $existingTokens[] = $reviewToken;
            cache()->put($tokenListKey, $existingTokens, self::REVIEW_EXPIRY_DAYS * 24 * 60);

            // Send notification (you need to create this notification class)
            if (class_exists(ReviewRequestNotification::class)) {
                $user->notify(new ReviewRequestNotification($booking, $reviewToken));
            }

        } catch (\Exception $e) {
            Log::error("Failed to send review notification for booking {$booking->id}: " . $e->getMessage());
        }
    }

    /**
     * Generate secure review token
     */
    private function generateReviewToken(Booking $booking): string
    {
        return hash('sha256', $booking->id . $booking->user_id . $booking->completed_at . microtime());
    }

    /**
     * Submit review using token
     */
    public function submitReviewWithToken(string $token, array $reviewData): BookingReview
    {
        $tokenData = cache()->get("review_token_{$token}");

        if (!$tokenData) {
            throw new \Exception('Invalid or expired review token');
        }

        if (Carbon::parse($tokenData['expires_at'])->isPast()) {
            cache()->forget("review_token_{$token}");
            throw new \Exception('Review token has expired');
        }

        $booking = Booking::findOrFail($tokenData['booking_id']);

        if ($booking->user_id !== $tokenData['user_id']) {
            throw new \Exception('Invalid token for this user');
        }

        return $this->submitReview($booking, $reviewData);
    }

    /**
     * Submit review data
     */
    public function submitReview(Booking $booking, array $reviewData): BookingReview
    {
        // Validate review data
        $this->validateReviewData($reviewData);

        $review = $booking->review ?? new BookingReview([
            'booking_id' => $booking->id,
            'user_id' => $booking->user_id,
            'rental_shop_id' => $booking->rental_shop_id,
            'car_id' => $booking->car_id,
        ]);

        $review->fill([
            'rating' => $reviewData['rating'],
            'cleanliness_rating' => $reviewData['cleanliness_rating'] ?? null,
            'service_rating' => $reviewData['service_rating'] ?? null,
            'value_rating' => $reviewData['value_rating'] ?? null,
            'comment' => $reviewData['comment'] ?? null,
            'is_approved' => true, // Auto-approve user reviews
        ]);

        $review->save();

        // Clean up token if exists
        $this->cleanupReviewTokens($booking->id);

        // Update ratings for related entities
        $this->updateEntityRatings($booking);

        Log::info("Review submitted for booking {$booking->id}");

        return $review;
    }

    /**
     * Validate review data
     */
    private function validateReviewData(array $reviewData): void
    {
        $validator = validator($reviewData, [
            'rating' => 'required|integer|min:1|max:5',
            'cleanliness_rating' => 'nullable|integer|min:1|max:5',
            'service_rating' => 'nullable|integer|min:1|max:5',
            'value_rating' => 'nullable|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            throw new \Exception('Invalid review data: ' . implode(', ', $validator->errors()->all()));
        }
    }

    /**
     * Update ratings for car and rental shop
     */
    private function updateEntityRatings(Booking $booking): void
    {
        // Update car rating
        $this->updateCarRating($booking->car_id);

        // Update rental shop rating
        $this->updateRentalShopRating($booking->rental_shop_id);
    }

    /**
     * Update car average rating
     */
    private function updateCarRating(int $carId): void
    {
        $reviews = BookingReview::where('car_id', $carId)
            ->where('is_approved', true)
            ->whereNotNull('rating')
            ->get();

        if ($reviews->isNotEmpty()) {
            $averageRating = $reviews->avg('rating');

            // Update car's average rating (assuming you have this column)
            \App\Models\Car::where('id', $carId)
                ->update(['average_rating' => round($averageRating, 2)]);
        }
    }

    /**
     * Update rental shop average rating
     */
    private function updateRentalShopRating(int $rentalShopId): void
    {
        $reviews = BookingReview::where('rental_shop_id', $rentalShopId)
            ->where('is_approved', true)
            ->whereNotNull('rating')
            ->get();

        if ($reviews->isNotEmpty()) {
            $averageRating = $reviews->avg('rating');

            // Update rental shop's average rating (assuming you have this column)
            \App\Models\RentalShop::where('id', $rentalShopId)
                ->update(['average_rating' => round($averageRating, 2)]);
        }
    }

    /**
     * Clean up review tokens for a booking
     */
    private function cleanupReviewTokens(int $bookingId): void
    {
        // Since getMatchingKeys() is not available in all cache drivers,
        // we'll store a list of tokens per booking for cleanup
        $tokenListKey = "booking_tokens_{$bookingId}";
        $tokens = cache()->get($tokenListKey, []);

        foreach ($tokens as $token) {
            cache()->forget("review_token_{$token}");
        }

        // Clean up the token list
        cache()->forget($tokenListKey);
    }

    /**
     * Get pending reviews for user
     */
    public function getPendingReviewsForUser(int $userId): array
    {
        $pendingReviews = BookingReview::where('user_id', $userId)
            ->whereNull('rating')
            ->with(['booking.car.carModel', 'booking.rentalShop'])
            ->get();

        return $pendingReviews->map(function ($review) {
            return [
                'id' => $review->id,
                'booking_id' => $review->booking_id,
                'booking_number' => $review->booking->booking_number,
                'car_name' => $review->booking->car->carModel->name,
                'rental_shop_name' => $review->booking->rentalShop->name,
                'completed_at' => $review->booking->completed_at,
                'review_token' => $this->generateReviewToken($review->booking)
            ];
        })->toArray();
    }

    /**
     * Send reminder notifications for pending reviews
     */
    public function sendReviewReminders(): int
    {
        $pendingReviews = BookingReview::whereNull('rating')
            ->where('created_at', '<', now()->subDays(3))
            ->where('created_at', '>', now()->subDays(7))
            ->get();

        $sentCount = 0;

        foreach ($pendingReviews as $review) {
            try {
                $this->sendReviewRequestNotification($review->booking);
                $sentCount++;
            } catch (\Exception $e) {
                Log::error("Failed to send review reminder for booking {$review->booking_id}: " . $e->getMessage());
            }
        }

        return $sentCount;
    }

    /**
     * Get review statistics
     */
    public function getReviewStatistics(?int $rentalShopId = null, ?int $carId = null): array
    {
        $query = BookingReview::where('is_approved', true);

        if ($rentalShopId) {
            $query->where('rental_shop_id', $rentalShopId);
        }

        if ($carId) {
            $query->where('car_id', $carId);
        }

        $reviews = $query->get();

        return [
            'total_reviews' => $reviews->count(),
            'average_rating' => $reviews->avg('rating') ? round($reviews->avg('rating'), 2) : 0,
            'average_cleanliness' => $reviews->avg('cleanliness_rating') ? round($reviews->avg('cleanliness_rating'), 2) : 0,
            'average_service' => $reviews->avg('service_rating') ? round($reviews->avg('service_rating'), 2) : 0,
            'average_value' => $reviews->avg('value_rating') ? round($reviews->avg('value_rating'), 2) : 0,
            'rating_distribution' => $this->getRatingDistribution($reviews),
        ];
    }

    /**
     * Get rating distribution
     */
    private function getRatingDistribution($reviews): array
    {
        $distribution = [];

        for ($i = 1; $i <= 5; $i++) {
            $distribution[$i] = $reviews->where('rating', $i)->count();
        }

        return $distribution;
    }

    /**
     * Get reviews for a specific rental shop
     */
    public function getRentalShopReviews(int $rentalShopId, int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return \App\Models\BookingReview::with(['user', 'car', 'booking'])
            ->where('rental_shop_id', $rentalShopId)
            ->where('is_approved', true)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
