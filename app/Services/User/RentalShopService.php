<?php

namespace App\Services\User;

use App\Models\RentalShop;
use App\Repositories\Interfaces\RentalShopRepositryInterface;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class RentalShopService
{
    private const CACHE_TTL = 3600; // 1 hour
    private const DEFAULT_PER_PAGE = 15;

    public function __construct(private RentalShopRepositryInterface $rentalShopRepository) {}

    public function getAll(Request $request): LengthAwarePaginator
    {
        $cacheKey = $this->generateCacheKey('rental_shops_all', $request->all());

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($request) {
            return $this->buildQuery($request)
                ->paginate($request->get('per_page', self::DEFAULT_PER_PAGE));
        });
    }

    public function getByCity(int $cityId, Request $request): LengthAwarePaginator
    {
        $cacheKey = $this->generateCacheKey("rental_shops_city_{$cityId}", $request->all());

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($request, $cityId) {
            return $this->buildQuery($request)
                ->whereHas('address', function (Builder $q) use ($cityId) {
                    $q->where('city_id', $cityId);
                })
                ->paginate($request->get('per_page', self::DEFAULT_PER_PAGE));
        });
    }

    public function findById(int $id): ?RentalShop
    {
        $cacheKey = "rental_shop_{$id}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($id) {
            return RentalShop::with($this->getStandardRelations())
                ->where('is_active', true)
                ->where('status', 'approved')
                ->find($id);
        });
    }

    /**
     * Build base query with standard relationships and filters
     */
    private function buildQuery(Request $request): Builder
    {
        $query = RentalShop::with($this->getStandardRelations())
            ->where('is_active', true)
            ->where('status', 'approved');

        // Apply filters
        $this->applyFilters($query, $request);

        return $query;
    }

    /**
     * Get standard relationships for rental shops
     */
    private function getStandardRelations(): array
    {
        return [
            'address.country',
            'address.city',
            'workingDays',
            'approvedReviews',
            'allReviews'
        ];
    }

    /**
     * Apply filters to query
     */
    private function applyFilters(Builder $query, Request $request): void
    {
        // Search by name
        if ($request->filled('search')) {
            $query->searchName($request->get('search'));
        }

        // Filter by city
        if ($request->filled('city_id')) {
            $query->whereHas('address', function (Builder $q) use ($request) {
                $q->where('city_id', $request->get('city_id'));
            });
        }

        // Filter by rating
        if ($request->filled('min_rating')) {
            $query->where('rating', '>=', $request->get('min_rating'));
        }

        // Apply sorting
        $this->applySorting($query, $request);
    }

    /**
     * Apply sorting to query
     */
    private function applySorting(Builder $query, Request $request): void
    {
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $allowedSorts = [
            'name', 'rating', 'created_at', 'updated_at',
            'newest', 'latest', 'oldest'
        ];

        if (in_array($sortBy, $allowedSorts)) {
            // Handle special sort values
            $sortBy = match ($sortBy) {
                'newest', 'latest' => 'created_at',
                'oldest' => 'created_at',
                default => $sortBy
            };

            $order = match ($sortBy) {
                'newest', 'latest' => 'desc',
                'oldest' => 'asc',
                default => $sortOrder
            };

            $query->orderBy($sortBy, $order);
        }
    }

    /**
     * Generate cache key based on request parameters
     */
    private function generateCacheKey(string $prefix, array $params): string
    {
        $paramString = http_build_query($params);
        return "{$prefix}_" . md5($paramString);
    }

    /**
     * Clear rental shop cache
     */
    public function clearCache(): void
    {
        Cache::forget('rental_shops_all');
        Cache::forget('rental_shops_city_*');
        Cache::forget('rental_shop_*');
    }

    /**
     * Clear all rental shop related cache
     */
    public function clearAllCache(): void
    {
        $this->clearCache();
        Cache::forget('reviews_rental_shop_*');
        Cache::forget('review_*');
    }
}
