<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface BookingRepositoryInterface
{
    public function calculatePrice($data);

    public function createBooking(array $data, int $userId);

    public function findById(int $id);

    public function findUserBooking(int $bookingId, int $userId);

    public function findVendorBooking(int $bookingId, int $vendorId);

    public function getUserBookings(int $userId, ?string $status = null, ?int $perPage = 15): LengthAwarePaginator;

    public function getVendorBookings(int $vendorId, ?string $status = null, ?int $perPage = 15): LengthAwarePaginator;

    public function getAllBookings(?string $status = null, ?string $dateFrom = null, ?string $dateTo = null, ?int $perPage = 15): LengthAwarePaginator;

    public function getUpcomingBookings(int $vendorId, int $days = 7): Collection;

    public function getVendorBookingsByDateRange(int $vendorId, string $startDate, string $endDate): Collection;

    public function getProblematicBookings(): Collection;

    public function getBookingAnalytics(string $period): array;

    public function getRevenueReport(string $startDate, string $endDate): array;

    public function getVendorPerformanceMetrics(): array;

    public function getCarUtilizationReport(string $startDate, string $endDate): array;

    public function getCustomerInsights(): array;

    public function getBookingTrends(int $days): array;

    public function updateBookingStatus(int $bookingId, string $status, ?string $reason = null);

    public function forceCancelBooking(int $bookingId, string $reason, int $adminId);

    public function bulkUpdateBookingStatus(array $bookingIds, string $status, ?string $reason = null, string $changedByType = 'admin', int $changedById = null): array;

    public function exportBookings(array $filters, string $format): string;
}
