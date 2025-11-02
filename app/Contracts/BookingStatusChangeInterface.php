<?php

namespace App\Contracts;

interface BookingStatusChangeInterface
{
    public function handleStatusChange($booking, string $oldStatus, string $newStatus, array $context = []): void;
}
