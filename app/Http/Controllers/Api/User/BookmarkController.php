<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookmarkResource;
use App\Http\Resources\CarResource;
use App\Http\Resources\PaginationResource;
use App\Models\Bookmark;
use App\Models\Car;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class BookmarkController extends Controller
{
    use ApiResponse;

    /**
     * Get all bookmarked cars for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $bookmarks = $user->bookmarks()
            ->with('car.carModel.brand', 'car.category', 'car.fuel', 'car.transmission', 'car.images')
            ->latest()
            ->paginate($request->get('per_page', 15));

        return $this->successResponse([
            'bookmarks' => BookmarkResource::collection($bookmarks),
            'pagination' => new PaginationResource($bookmarks),
        ], __('message.success'));
    }

    /**
     * Toggle bookmark for a car.
     */
    public function toggle(Request $request, Car $car): JsonResponse
    {
        $user = Auth::user();

        // Check if car exists and is active
        if (!$car->is_active) {
            return $this->errorResponse(__('messages.car_not_available'), 404);
        }

        $isBookmarked = $user->toggleBookmark($car->id);

        return $this->successResponse([
            'is_bookmarked' => $isBookmarked,
            'car_id' => $car->id,
        ], $isBookmarked
            ? __('messages.car_bookmarked')
            : __('messages.car_unbookmarked'));
    }

    /**
     * Check if a car is bookmarked by the authenticated user.
     */
    public function check(Request $request, Car $car): JsonResponse
    {
        $user = Auth::user();
        $isBookmarked = $user->hasBookmarked($car->id);

        return $this->successResponse([
            'is_bookmarked' => $isBookmarked,
            'car_id' => $car->id,
        ]);
    }

    /**
     * Remove bookmark for a car.
     */
    public function remove(Request $request, Car $car): JsonResponse
    {
        $user = Auth::user();
        $removed = $user->unbookmarkCar($car->id);

        if (!$removed) {
            return $this->errorResponse(__('messages.bookmark_not_found'), 404);
        }

        return $this->successResponse([
            'car_id' => $car->id,
        ], __('messages.bookmark_removed'));
    }

    /**
     * Get bookmarked cars as car resources (for car listing format).
     */
    public function cars(Request $request): JsonResponse
    {
        $user = Auth::user();
        $cars = $user->bookmarkedCars()
            ->with(['carModel.brand', 'category', 'fuel', 'transmission', 'images'])
            ->active()
            ->latest()
            ->paginate($request->get('per_page', 15));

        return $this->successResponse([
            'cars' => CarResource::collection($cars),
            'pagination' => new PaginationResource($cars),
        ], __('message.success'));
    }

    /**
     * Get bookmark count for a specific car.
     */
    public function count(Request $request, Car $car): JsonResponse
    {
        $count = $car->bookmark_count;

        return $this->successResponse([
            'car_id' => $car->id,
            'bookmark_count' => $count,
        ]);
    }
}
