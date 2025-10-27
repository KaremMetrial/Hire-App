<?php

namespace App\Repositories\Interfaces;

use App\Models\BookingReview;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface BookingReviewRepositoryInterface
{
    public function getByRentalShop(int $rentalShopId, array $filters = []): LengthAwarePaginator;
    public function findById(int $id): ?BookingReview;
}
