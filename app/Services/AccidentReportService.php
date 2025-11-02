<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingAccidentReport;
use App\Models\BookingAccidentReportImage;
use App\Models\BookingStatusLog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;

class AccidentReportService
{
    /**
     * Submit an accident report for a booking
     */
    public function submitAccidentReport(array $data, int $userId): BookingAccidentReport
    {
        DB::beginTransaction();

        try {
            // Verify booking belongs to user
            $booking = Booking::where('user_id', $userId)
                ->where('id', $data['booking_id'])
                ->firstOrFail();

            // Check if user already has a pending accident report for this booking
            $existingReport = BookingAccidentReport::where('booking_id', $booking->id)
                ->where('user_id', $userId)
                ->whereIn('status', ['pending', 'investigating'])
                ->first();

            if ($existingReport) {
                throw new Exception('You already have a pending accident report for this booking.');
            }

            // Create accident report
            $accidentReport = BookingAccidentReport::create([
                'booking_id' => $booking->id,
                'user_id' => $userId,
                'accident_location' => $data['accident_location'],
                'accident_details' => $data['accident_details'],
                'accident_location_coordinates' => $data['accident_location_coordinates'] ?? null,
                'accident_date' => $data['accident_date'],
                'severity' => $data['severity'],
                'status' => 'pending',
            ]);

            // Update booking status to accident_reported
            $booking->update(['status' => \App\Enums\BookingStatusEnum::AccidentReported]);
            $this->logStatusChange($booking, \App\Enums\BookingStatusEnum::AccidentReported->value, 'user', $userId, 'Accident report submitted');
            // Handle image uploads
            if (isset($data['images']) && is_array($data['images'])) {
                $this->uploadAccidentImages($accidentReport, $data['images'], $data['image_descriptions'] ?? []);
            }

            DB::commit();
            return $accidentReport->load('images');
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get user's accident reports
     */
    public function getUserAccidentReports(int $userId, ?string $status = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = BookingAccidentReport::where('user_id', $userId)
            ->with([
                'booking.car.carModel.brand',
                'booking.car.images',
                'booking.rentalShop',
                'booking.payments',
                'booking.extraServices',
                'booking.insurances',
                'booking.documents',
                'booking.statusLogs',
                'booking.informationRequests',
                'booking.procedures',
                'images'
            ])
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        return $query->get();
    }

    /**
     * Get accident report details
     */
    public function getAccidentReport(int $reportId, int $userId): BookingAccidentReport
    {
        return BookingAccidentReport::where('id', $reportId)
            ->where('user_id', $userId)
            ->with([
                'booking.car.carModel.brand',
                'booking.car.images',
                'booking.rentalShop',
                'booking.payments',
                'booking.extraServices',
                'booking.insurances',
                'booking.documents',
                'booking.statusLogs',
                'booking.informationRequests',
                'booking.procedures',
                'images'
            ])
            ->firstOrFail();
    }

    /**
     * Update accident report status (Admin function)
     */
    public function updateReportStatus(int $reportId, string $status, ?string $adminNotes = null): BookingAccidentReport
    {
        $report = BookingAccidentReport::findOrFail($reportId);

        $updateData = ['status' => $status];

        if ($status === 'resolved') {
            $updateData['resolved_at'] = now();
            $updateData['admin_notes'] = $adminNotes;
        } elseif ($status === 'investigating') {
            $updateData['admin_notes'] = $adminNotes;
        }

        $report->update($updateData);

        return $report->fresh();
    }

    /**
     * Upload accident images
     */
    private function uploadAccidentImages(BookingAccidentReport $report, array $images, array $descriptions = []): void
    {
        foreach ($images as $index => $image) {
            if ($image instanceof UploadedFile) {
                $imagePath = $image->store('accident-reports', 'public');

                BookingAccidentReportImage::create([
                    'booking_accident_report_id' => $report->id,
                    'image_path' => $imagePath,
                    'description' => $descriptions[$index] ?? null,
                ]);
            }
        }
    }

    /**
     * Delete accident report image
     */
    public function deleteAccidentImage(int $imageId, int $userId): bool
    {
        $image = BookingAccidentReportImage::whereHas('accidentReport', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->findOrFail($imageId);

        // Delete file from storage
        Storage::disk('public')->delete($image->image_path);

        // Delete record
        return $image->delete();
    }
    private function logStatusChange(Booking $booking, string $newStatus, string $changedByType, int $changedById, ?string $notes = null): void
    {
        BookingStatusLog::create([
            'booking_id' => $booking->id,
            'old_status' => $booking->getOriginal('status'),
            'new_status' => $newStatus,
            'changed_by_type' => $changedByType,
            'changed_by_id' => $changedById,
            'notes' => $notes,
        ]);
    }
    /**
     * Get accident reports statistics for admin
     */
    public function getAccidentReportsStats(): array
    {
        return [
            'total_reports' => BookingAccidentReport::count(),
            'pending_reports' => BookingAccidentReport::where('status', 'pending')->count(),
            'investigating_reports' => BookingAccidentReport::where('status', 'investigating')->count(),
            'resolved_reports' => BookingAccidentReport::where('status', 'resolved')->count(),
            'recent_reports' => BookingAccidentReport::with(['user', 'booking.car.carModel'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
        ];
    }
}
