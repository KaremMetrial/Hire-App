<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingReview;
use App\Models\Car;
use App\Models\User;
use App\Services\AutoReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AutoReviewServiceTest extends TestCase
{
    use RefreshDatabase;

    private AutoReviewService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(AutoReviewService::class);
        Notification::fake();
    }

    /** @test */
    public function it_creates_review_request_for_completed_booking()
    {
        $booking = $this->createCompletedBooking();

        $this->service->createReviewForCompletedBooking($booking);

        $this->assertDatabaseHas('booking_reviews', [
            'booking_id' => $booking->id,
            'user_id' => $booking->user_id,
            'rental_shop_id' => $booking->rental_shop_id,
            'car_id' => $booking->car_id,
            'rating' => null,
            'is_approved' => false
        ]);
    }

    /** @test */
    public function it_does_not_create_duplicate_review()
    {
        $booking = $this->createCompletedBooking();

        // Create initial review
        $this->service->createReviewForCompletedBooking($booking);

        // Try to create again
        $this->service->createReviewForCompletedBooking($booking);

        $this->assertEquals(1, BookingReview::where('booking_id', $booking->id)->count());
    }

    /** @test */
    public function it_can_submit_review_with_valid_token()
    {
        $booking = $this->createCompletedBooking();
        $reviewData = [
            'rating' => 5,
            'cleanliness_rating' => 4,
            'service_rating' => 5,
            'value_rating' => 4,
            'comment' => 'Great experience!'
        ];

        // Create a review token manually
        $token = 'test_token_' . uniqid();
        Cache::put("review_token_{$token}", [
            'booking_id' => $booking->id,
            'user_id' => $booking->user_id,
            'expires_at' => now()->addDays(30)
        ], 30 * 24 * 60);

        $review = $this->service->submitReviewWithToken($token, $reviewData);

        $this->assertInstanceOf(BookingReview::class, $review);
        $this->assertEquals(5, $review->rating);
        $this->assertEquals('Great experience!', $review->comment);
        $this->assertTrue($review->is_approved);
    }

    /** @test */
    public function it_fails_to_submit_review_with_invalid_token()
    {
        $reviewData = ['rating' => 5];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid or expired review token');

        $this->service->submitReviewWithToken('invalid_token', $reviewData);
    }

    /** @test */
    public function it_fails_to_submit_review_with_expired_token()
    {
        $booking = $this->createCompletedBooking();

        // Create expired token
        $token = 'expired_token_' . uniqid();
        Cache::put("review_token_{$token}", [
            'booking_id' => $booking->id,
            'user_id' => $booking->user_id,
            'expires_at' => now()->subDay() // Expired
        ], 60);

        $reviewData = ['rating' => 5];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Review token has expired');

        $this->service->submitReviewWithToken($token, $reviewData);
    }

    /** @test */
    public function it_validates_review_data()
    {
        $booking = $this->createCompletedBooking();
        $token = 'test_token_' . uniqid();
        Cache::put("review_token_{$token}", [
            'booking_id' => $booking->id,
            'user_id' => $booking->user_id,
            'expires_at' => now()->addDays(30)
        ], 30 * 24 * 60);

        // Test invalid rating
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid review data');

        $this->service->submitReviewWithToken($token, ['rating' => 6]); // Invalid rating
    }

    /** @test */
    public function it_updates_entity_ratings_after_review()
    {
        $car = Car::factory()->create();
        $booking = $this->createCompletedBooking(['car_id' => $car->id]);

        $reviewData = ['rating' => 4];
        $token = 'test_token_' . uniqid();
        Cache::put("review_token_{$token}", [
            'booking_id' => $booking->id,
            'user_id' => $booking->user_id,
            'expires_at' => now()->addDays(30)
        ], 30 * 24 * 60);

        $this->service->submitReviewWithToken($token, $reviewData);

        $car->refresh();
        $this->assertEquals(4.0, $car->average_rating);
    }

    /** @test */
    public function it_provides_pending_reviews_for_user()
    {
        $user = User::factory()->create();
        $booking1 = $this->createCompletedBooking(['user_id' => $user->id]);
        $booking2 = $this->createCompletedBooking(['user_id' => $user->id]);

        // Create reviews for one booking
        $this->service->createReviewForCompletedBooking($booking1);
        $this->service->createReviewForCompletedBooking($booking2);

        // Submit review for one booking
        $token = 'test_token_' . uniqid();
        Cache::put("review_token_{$token}", [
            'booking_id' => $booking1->id,
            'user_id' => $user->id,
            'expires_at' => now()->addDays(30)
        ], 30 * 24 * 60);

        $this->service->submitReviewWithToken($token, ['rating' => 5]);

        $pendingReviews = $this->service->getPendingReviewsForUser($user->id);

        $this->assertEquals(1, count($pendingReviews));
        $this->assertEquals($booking2->id, $pendingReviews[0]['booking_id']);
    }

    /** @test */
    public function it_provides_review_statistics()
    {
        $car = Car::factory()->create();
        $user = User::factory()->create();

        // Create multiple completed bookings with reviews
        for ($i = 0; $i < 5; $i++) {
            $booking = $this->createCompletedBooking(['car_id' => $car->id, 'user_id' => $user->id]);
            BookingReview::factory()->create([
                'booking_id' => $booking->id,
                'rating' => $i + 1, // Ratings 1-5
                'is_approved' => true
            ]);
        }

        $stats = $this->service->getReviewStatistics($car->id);

        $this->assertEquals(5, $stats['total_reviews']);
        $this->assertEquals(3.0, $stats['average_rating']); // (1+2+3+4+5)/5
        $this->assertEquals(1, $stats['rating_distribution'][1]);
        $this->assertEquals(1, $stats['rating_distribution'][2]);
        $this->assertEquals(1, $stats['rating_distribution'][3]);
        $this->assertEquals(1, $stats['rating_distribution'][4]);
        $this->assertEquals(1, $stats['rating_distribution'][5]);
    }

    private function createCompletedBooking(array $overrides = []): Booking
    {
        return Booking::factory()->create(array_merge([
            'status' => 'completed',
            'completed_at' => now(),
            'pickup_date' => now()->subDays(5),
            'return_date' => now()->subDays(2)
        ], $overrides));
    }
}
