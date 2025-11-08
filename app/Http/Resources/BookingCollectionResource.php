<?php

namespace App\Http\Resources;

use App\Services\BookingTimerService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingCollectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $title = $this->whenLoaded('car', function () {
            $brandName = $this->car->carModel->brand->name ?? '';
            $modelName = $this->car->carModel->name ?? '';
            return trim("{$brandName} {$modelName} {$this->car->year_of_manufacture}");
        }, '');

        return [
            'id' => $this->id,
            'title' => $title,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),

            // Dates and Locations
            'pickup_date' => $this->pickup_date->format('Y-m-d H:i:s'),
            'pickup_date_formatted' => $this->pickup_date->format('M d, Y h:i A'),
            'return_date' => $this->return_date->format('Y-m-d H:i:s'),
            'return_date_formatted' => $this->return_date->format('M d, Y h:i A'),
            'duration_days' => $this->getDurationInDays(),
            'duration_hours' => $this->getDurationInHours(),
            'duration_text' => $this->getDurationText(),

            // Timer Information (for pending bookings)
            'acceptance_timer' => $this->when($this->isPending(), function () {
                $timerService = app(BookingTimerService::class);
                return $timerService->calculateRemainingAcceptanceTime($this->resource);
            }),

            // Information Requests
            'information_requests' => $this->whenLoaded('informationRequests', $this->informationRequests->map(function ($request) {
                return [
                    'id' => $request->id,
                    'requested_field' => $request->requested_field,
                    'field_label' => $request->getFieldLabel(),
                    'is_required' => $request->is_required,
                    'status' => $request->status,
                    'notes' => $request->notes,
                    'submitted_value' => $request->submitted_value,
                    'submitted_at' => $request->submitted_at?->format('Y-m-d H:i:s'),
                    'created_at' => $request->created_at->format('Y-m-d H:i:s'),
                ];
            })),

            'can_be_cancelled' => $this->canBeCancelled(),
            'is_pending' => $this->isPending(),
            'is_confirmed' => $this->isConfirmed(),
            'is_active' => $this->isActive(),
            'is_under_delivery' => $this->isUnderDelivery(),
            'is_completed' => $this->isCompleted(),
            'is_cancelled' => $this->isCancelled(),
            'is_rejected' => $this->isRejected(),
            'is_info_requested' => $this->isInfoRequested(),
            'is_accident_reported' => $this->isAccidentReported(),
            'is_extension_requested' => $this->isExtensionRequested(),
            'is_unreasonable_delay' => $this->isUnreasonableDelay(),
            'is_under_dispute' => $this->isUnderDispute(),
        ];
    }

    private function getDurationText(): string
    {
        $days = $this->getDurationInDays();
        $hours = $this->getDurationInHours();

        if ($days > 0 && $hours > 0) {
            return __('enums.duration.days_and_hours', ['days' => $days, 'hours' => $hours]);
        } elseif ($days > 0) {
            return __('enums.duration.days_only', ['days' => $days]);
        } elseif ($hours > 0) {
            return __('enums.duration.hours_only', ['hours' => $hours]);
        }

        return __('enums.duration.less_than_hour');
    }
}
