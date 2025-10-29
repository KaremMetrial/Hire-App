<?php

namespace App\Http\Resources;

use App\Services\BookingTimerService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_number' => $this->booking_number,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'payment_status' => $this->payment_status->value,
            'payment_status_label' => $this->payment_status->label(),

            // User Information
            'user' => $this->when($this->user, [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'phone' => $this->user->phone,
            ]),

            // Car Information
            'car' => $this->when($this->car, [
                'id' => $this->car->id,
                'model' => $this->car->carModel->name ?? null,
                'brand' => $this->car->carModel->brand->name ?? null,
                'year' => $this->car->year_of_manufacture,
                'color' => $this->car->color,
                'license_plate' => $this->car->license_plate,
                'num_of_seats' => $this->car->num_of_seat,
                'kilometers' => $this->car->kilometers,
                'fuel_type' => $this->car->fuel->name ?? null,
                'transmission_type' => $this->car->transmission->name ?? null,
                'category' => $this->car->category->name ?? null,
                'images' => $this->car->images->map(fn($image) => [
                    'id' => $image->id,
                    'image' => $image->image,
                    'type' => $image->type,
                ]),
            ]),

            // Rental Shop Information
            'rental_shop' => $this->when($this->rentalShop, [
                'id' => $this->rentalShop->id,
                'name' => $this->rentalShop->name,
                'phone' => $this->rentalShop->phone,
                'address' => $this->rentalShop->address,
                'image' => $this->rentalShop->image,
                'vendor' => $this->rentalShop->vendor ? [
                    'id' => $this->rentalShop->vendor->id,
                    'name' => $this->rentalShop->vendor->name,
                ] : null,
            ]),

            // Dates and Locations
            'pickup_date' => $this->pickup_date->format('Y-m-d H:i:s'),
            'pickup_date_formatted' => $this->pickup_date->format('M d, Y h:i A'),
            'return_date' => $this->return_date->format('Y-m-d H:i:s'),
            'return_date_formatted' => $this->return_date->format('M d, Y h:i A'),
            'duration_days' => $this->getDurationInDays(),
            'duration_hours' => $this->getDurationInHours(),
            'duration_text' => $this->getDurationText(),

            'pickup_location_type' => $this->pickup_location_type->value,
            'pickup_location_label' => $this->pickup_location_type->label(),
            'pickup_address' => $this->pickup_address,
            'pickup_latitude' => (float) $this->pickup_latitude,
            'pickup_longitude' => (float) $this->pickup_longitude,
            'return_location_type' => $this->return_location_type->value,
            'return_location_label' => $this->return_location_type->label(),
            'return_address' => $this->return_address,
            'return_latitude' => (float) $this->return_latitude,
            'return_longitude' => (float) $this->return_longitude,

            // Pricing
            'pricing' => [
                'rental_price' => (float) $this->rental_price,
                'delivery_fee' => (float) $this->delivery_fee,
                'extra_services_total' => (float) $this->extra_services_total,
                'insurance_total' => (float) $this->insurance_total,
                'mileage_fee' => (float) $this->mileage_fee,
                'tax' => (float) $this->tax,
                'discount' => (float) $this->discount,
                'subtotal' => (float) ($this->rental_price + $this->delivery_fee + $this->extra_services_total + $this->insurance_total),
                'total_price' => (float) $this->total_price,
                'deposit_amount' => (float) $this->deposit_amount,
                'currency' => 'JOD',
            ],

            // Mileage
            'mileage' => [
                'pickup_mileage' => $this->pickup_mileage,
                'return_mileage' => $this->return_mileage,
                'actual_mileage_used' => $this->actual_mileage_used,
                'included_mileage' => $this->car->mileages->daily_mileage_limit ?? 200,
                'extra_mileage' => max(0, ($this->return_mileage - $this->pickup_mileage) - ($this->car->mileages->daily_mileage_limit ?? 200)),
            ],

            // Extra Services
            'extra_services' => $this->whenLoaded('extraServices', $this->extraServices->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->extraService->name ?? null,
                    'description' => $service->extraService->description ?? null,
                    'price' => (float) $service->price,
                    'quantity' => $service->quantity,
                    'total' => (float) ($service->price * $service->quantity),
                ];
            })),

            // Insurances
            'insurances' => $this->whenLoaded('insurances', $this->insurances->map(function ($insurance) {
                return [
                    'id' => $insurance->id,
                    'title' => $insurance->insurance->title ?? null,
                    'description' => $insurance->insurance->description ?? null,
                    'price' => (float) $insurance->price,
                    'deposit_price' => (float) $insurance->deposit_price,
                    'coverage' => $insurance->insurance->coverage ?? null,
                ];
            })),

            // Documents
            'documents' => $this->whenLoaded('documents', $this->documents->map(function ($document) {
                return [
                    'id' => $document->id,
                    'document_name' => $document->document->name ?? null,
                    'document_type' => $document->document->type ?? null,
                    'file_path' => $document->file_path ? asset('storage/'.$document->file_path) : null,
                    'document_value' => $document->document_value,
                    'verified' => $document->verified,
                    'verified_at' => $document->verified_at?->format('Y-m-d H:i:s'),
                ];
            })),

            // Payments
            'payments' => $this->whenLoaded('payments', $this->payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'amount' => (float) $payment->amount,
                    'payment_method' => $payment->payment_method->value,
                    'payment_method_label' => $payment->payment_method->label(),
                    'payment_type' => $payment->payment_type->value,
                    'payment_type_label' => $payment->payment_type->label(),
                    'status' => $payment->status->value,
                    'status_label' => $payment->status->label(),
                    'transaction_id' => $payment->transaction_id,
                    'payment_date' => $payment->payment_date?->format('Y-m-d H:i:s'),
                ];
            })),

            // Status Logs
            'status_logs' => $this->whenLoaded('statusLogs', $this->statusLogs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'old_status' => $log->old_status ? \App\Enums\BookingStatusEnum::tryFrom($log->old_status)?->label() : $log->old_status,
                    'new_status' => $log->new_status ? \App\Enums\BookingStatusEnum::tryFrom($log->new_status)?->label() : $log->new_status,
                    'changed_by_type' => $log->changed_by_type,
//                    'changed_by' => $log->changedBy && isset($log->changedBy->id) ? [
//                        'id' => $log->changedBy->id ?? null,
//                        'name' => $log->changedBy->name ?? 'Unknown',
//                    ] : null,
                    'notes' => $log->notes,
                    'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                ];
            })),

            // Notes and Reasons
            'customer_notes' => $this->customer_notes,
            'admin_notes' => $this->admin_notes,
            'cancellation_reason' => $this->cancellation_reason,
            'rejection_reason' => $this->rejection_reason,

            // Important Dates
            'confirmed_at' => $this->confirmed_at?->format('Y-m-d H:i:s'),
            'cancelled_at' => $this->cancelled_at?->format('Y-m-d H:i:s'),
            'completed_at' => $this->completed_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),

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

            // Flags and Helpers
            'can_be_cancelled' => $this->canBeCancelled(),
            'is_pending' => $this->isPending(),
            'is_confirmed' => $this->isConfirmed(),
            'is_active' => $this->isActive(),
            'is_completed' => $this->isCompleted(),
            'is_cancelled' => $this->isCancelled(),
            'is_rejected' => $this->isRejected(),
            'is_info_requested' => $this->isInfoRequested(),
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
