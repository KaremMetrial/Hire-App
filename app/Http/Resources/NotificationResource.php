<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = $this->data;

        return [
            'id' => $this->id,
            'type' => str_replace('App\\Notifications\\', '', $this->type),
            'data' => $data,
            'read_at' => $this->read_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'is_read' => !is_null($this->read_at),
            'formatted_created_at' => $this->created_at->diffForHumans(),
            // Figma-specific fields for notification tile
            'title' => $this->getNotificationTitle(),
            'description' => $this->getNotificationDescription(),
            'date' => $this->created_at->format('Y-m-d H:i:s'),
            'type' => $this->getNotificationType(),
            'booking_number' => $data['booking_number'] ?? null,
            'car_name' => $data['car_name'] ?? null,
        ];
    }

    /**
     * Get localized message for the notification
     */
    private function getLocalizedMessage(): string
    {
        $type = str_replace('App\\Notifications\\', '', $this->type);
        $data = $this->data;

        switch ($type) {
            case 'BookingStatusUpdated':
                $oldStatus = $data['old_status'] ?? '';
                $newStatus = $data['new_status'] ?? '';
                $bookingNumber = $data['booking_number'] ?? '';

                if ($data['is_vendor_notification'] ?? false) {
                    return __('notifications.booking_status.vendor_updated', [
                        'booking_number' => $bookingNumber,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                    ]);
                } else {
                    return __('notifications.booking_status.user_updated', [
                        'booking_number' => $bookingNumber,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                    ]);
                }

            case 'BookingOverdueNotification':
                return __('notifications.booking_overdue', [
                    'booking_number' => $data['booking_number'] ?? '',
                ]);

            case 'ReviewRequestNotification':
                return __('notifications.review_request', [
                    'booking_number' => $data['booking_number'] ?? '',
                ]);

            case 'SendOtpNotification':
                return __('notifications.otp_sent');

            default:
                return __('notifications.default_message');
        }
    }

    /**
     * Get notification title for Figma design
     */
    private function getNotificationTitle(): string
    {
        $type = str_replace('App\\Notifications\\', '', $this->type);

        switch ($type) {
            case 'BookingStatusUpdated':
                return __('message.notifications.titles.booking_status_updated');

            case 'BookingOverdueNotification':
                return __('message.notifications.titles.booking_overdue');

            case 'ReviewRequestNotification':
                return __('message.notifications.titles.review_request');

            case 'SendOtpNotification':
                return __('message.notifications.titles.otp_sent');

            default:
                return __('message.notifications.titles.default');
        }
    }

    /**
     * Get notification type for Figma design (for icon selection)
     */
    private function getNotificationType(): string
    {
        $type = str_replace('App\\Notifications\\', '', $this->type);

        switch ($type) {
            case 'BookingStatusUpdated':
                return 'booking_status';

            case 'BookingOverdueNotification':
                return 'booking_overdue';

            case 'ReviewRequestNotification':
                return 'review_request';

            case 'SendOtpNotification':
                return 'otp';

            default:
                return 'general';
        }
    }

    /**
     * Get notification description for display
     */
    private function getNotificationDescription(): string
    {
        $type = str_replace('App\\Notifications\\', '', $this->type);
        $data = $this->data;

        switch ($type) {
            case 'BookingStatusUpdated':
                $oldStatus = $data['old_status'] ?? '';
                $newStatus = $data['new_status'] ?? '';
                $bookingNumber = $data['booking_number'] ?? '';

                if ($data['is_vendor_notification'] ?? false) {
                    return __('message.notifications.descriptions.booking_status.vendor_updated', [
                        'booking_number' => $bookingNumber,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                    ]);
                } else {
                    return __('message.notifications.descriptions.booking_status.user_updated', [
                        'booking_number' => $bookingNumber,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                    ]);
                }

            case 'BookingOverdueNotification':
                return __('message.notifications.descriptions.booking_overdue', [
                    'booking_number' => $data['booking_number'] ?? '',
                ]);

            case 'ReviewRequestNotification':
                return __('message.notifications.descriptions.review_request', [
                    'booking_number' => $data['booking_number'] ?? '',
                ]);

            case 'SendOtpNotification':
                return __('message.notifications.otp_sent');

            default:
                return __('message.notifications.descriptions.default');
        }
    }
}
