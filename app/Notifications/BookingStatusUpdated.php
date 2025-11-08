<?php

namespace App\Notifications;

use App\Enums\BookingStatusEnum;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Booking $booking,
        public string $oldStatus,
        public string $newStatus,
        public bool $isVendorNotification = false
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $oldStatusLabel = BookingStatusEnum::from($this->oldStatus)->label();
        $newStatusLabel = BookingStatusEnum::from($this->newStatus)->label();

        $subjectKey = $this->isVendorNotification ? 'subject_vendor' : 'subject_user';
        $subject = __('message.booking_status_notification.' . $subjectKey, [
            'booking_number' => $this->booking->booking_number
        ]);

        $messageKey = $this->isVendorNotification ? 'vendor_status_changed' : 'status_changed';
        $message = __('message.booking_status_notification.' . $messageKey, [
            'old_status' => $oldStatusLabel,
            'new_status' => $newStatusLabel,
        ]);

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting(__('message.booking_status_notification.greeting', ['name' => $notifiable->name]))
            ->line($message)
            ->line(__('message.booking_status_notification.booking_details'))
            ->line(__('message.booking_status_notification.car', ['car_name' => $this->booking->car->carModel->name]))
            ->line(__('message.booking_status_notification.pickup_date', ['pickup_date' => $this->booking->pickup_date->format('Y-m-d')]))
            ->line(__('message.booking_status_notification.return_date', ['return_date' => $this->booking->return_date->format('Y-m-d')]));

        $actionText = $this->isVendorNotification
            ? __('message.booking_status_notification.view_booking_details')
            : __('message.booking_status_notification.view_booking');

        $url = $this->isVendorNotification
            ? url('/vendor/bookings/' . $this->booking->id)
            : url('/bookings/' . $this->booking->id);

        $mail->action($actionText, $url);

        return $mail->line(__('message.booking_status_notification.thank_you'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'booking_number' => $this->booking->booking_number,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'car_name' => $this->booking->car->carModel->name,
            'pickup_date' => $this->booking->pickup_date->format('Y-m-d'),
            'return_date' => $this->booking->return_date->format('Y-m-d'),
            'is_vendor_notification' => $this->isVendorNotification,
        ];
    }
}
