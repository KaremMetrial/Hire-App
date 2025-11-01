<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingAccidentReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'accident_location' => $this->accident_location,
            'accident_details' => $this->accident_details,
            'accident_location_coordinates' => $this->accident_location_coordinates,
            'accident_date' => $this->accident_date->format('Y-m-d'),
            'severity' => $this->severity,
            'severity_label' => $this->getSeverityLabel(),
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'admin_notes' => $this->admin_notes,
            'resolved_at' => $this->resolved_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),

            // Relations
            'booking' => $this->whenLoaded('booking', [
                'id' => $this->booking->id,
                'booking_number' => $this->booking->booking_number,
                'car' => $this->when($this->booking->car, [
                    'id' => $this->booking->car->id,
                    'model' => $this->booking->car->carModel->name ?? null,
                    'brand' => $this->booking->car->carModel->brand->name ?? null,
                    'license_plate' => $this->booking->car->license_plate,
                ]),
            ]),

            'images' => BookingAccidentReportImageResource::collection($this->whenLoaded('images')),

            // Helper flags
            'is_pending' => $this->isPending(),
            'is_investigating' => $this->isInvestigating(),
            'is_resolved' => $this->isResolved(),
        ];
    }

    private function getSeverityLabel(): string
    {
        return match ($this->severity) {
            'minor' => __('accident_reports.severity.minor'),
            'moderate' => __('accident_reports.severity.moderate'),
            'major' => __('accident_reports.severity.major'),
            default => $this->severity,
        };
    }

    private function getStatusLabel(): string
    {
        return match ($this->status) {
            'pending' => __('accident_reports.status.pending'),
            'investigating' => __('accident_reports.status.investigating'),
            'resolved' => __('accident_reports.status.resolved'),
            default => $this->status,
        };
    }
}
