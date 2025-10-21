<?php

namespace App\Services\User;

use App\Repositories\Interfaces\BookingRepositoryInterface;

class BookingService
{
    public function __construct(private BookingRepositoryInterface $bookingRepository) {}

    public function calculatePrice($data)
    {
        return $this->bookingRepository->calculatePrice($data);
    }

    public function createBooking($data, $userId)
    {
        return $this->bookingRepository->createBooking($data, $userId);
    }
}
