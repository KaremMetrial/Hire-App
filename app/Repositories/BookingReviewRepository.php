<?php

namespace App\Repositories;

use App\Models\BookingReview;
use App\Repositories\Interfaces\BookingReviewRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class BookingReviewRepository implements BookingReviewRepositoryInterface
{
    private const CACHE_TTL = 1800; // 30 minutes
    private const DEFAULT_PER_PAGE = 15;

    public function getByRentalShop(int $rentalShopId, array $filters = []): LengthAwarePaginator
    {
        $cacheKey = $this->generateCacheKey("reviews_rental_shop_{$rentalShopId}", $filters);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($rentalShopId, $filters) {
            $query = BookingReview::with($this->getReviewRelations())
                ->where('rental_shop_id', $rentalShopId)
                ->approved();

            $this->applyReviewFilters($query, $filters);
            $this->applyReviewSorting($query, $filters);
            return $query->paginate($filters['per_page'] ?? self::DEFAULT_PER_PAGE);
        });
    }

    public function findById(int $id): ?BookingReview
    {
        $cacheKey = "review_{$id}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($id) {
            return BookingReview::with($this->getReviewRelations())->find($id);
        });
    }

    /**
     * Get standard review relationships
     */
    private function getReviewRelations(): array
    {
        return [
            'user',
            'car',
            'booking'
        ];
    }

    /**
     * Apply filters to review query
     */
    private function applyReviewFilters($query, array $filters): void
    {
        if (isset($filters['rating']) && !empty($filters['rating'])) {
            $query->where('rating', $filters['rating']);
        }

        if (isset($filters['min_rating']) && !empty($filters['min_rating'])) {
            $query->where('rating', '>=', $filters['min_rating']);
        }
    }

    /**
     * Apply sorting to review query
     */
    private function applyReviewSorting($query, array $filters): void
    {
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';

        $allowedSorts = ['rating', 'created_at', 'updated_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }
    }

    /**
     * Generate cache key for reviews
     */
    private function generateCacheKey(string $prefix, array $params): string
    {
        $paramString = http_build_query($params);
        return "{$prefix}_" . md5($paramString);
    }

    /**
     * Clear review cache
     */
    public function clearCache(): void
    {
        Cache::forget('reviews_rental_shop_*');
        Cache::forget('review_*');
    }
}
