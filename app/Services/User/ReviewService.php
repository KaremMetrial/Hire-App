<?php

namespace App\Services\User;

use App\Repositories\Interfaces\BookingReviewRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReviewService
{
    public function __construct(private BookingReviewRepositoryInterface $reviewRepository) {}

    public function getRentalShopReviews(int $rentalShopId, array $filters = []): LengthAwarePaginator
    {
        return $this->reviewRepository->getByRentalShop($rentalShopId, $filters);
    }

    public function getReviewById(int $id): ?\App\Models\BookingReview
    {
        return $this->reviewRepository->findById($id);
    }

    /**
     * Clear review cache
     */
    public function clearCache(): void
    {
        Cache::forget('reviews_rental_shop_*');
        Cache::forget('review_*');
    }

    /**
     * Clear all review related cache
     */
    public function clearAllCache(): void
    {
        $this->clearCache();
        Cache::forget('rental_shops_*');
        Cache::forget('rental_shop_*');
    }
}
