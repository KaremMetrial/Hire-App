<?php

namespace App\Notifications;

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
        $subject = $this->isVendorNotification
            ? "Booking #{$this->booking->booking_number} Status Updated"
            : "Your Booking #{$this->booking->booking_number} Status Updated";

        $message = $this->isVendorNotification
            ? "Booking status changed from {$this->oldStatus} to {$this->newStatus}"
            : "Your booking status has been updated from {$this->oldStatus} to {$this->newStatus}";

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line($message)
            ->line('Booking Details:')
            ->line('Car: ' . $this->booking->car->carModel->name)
            ->line('Pickup Date: ' . $this->booking->pickup_date->format('Y-m-d'))
            ->line('Return Date: ' . $this->booking->return_date->format('Y-m-d'));

        if ($this->isVendorNotification) {
            $mail->action('View Booking Details', url('/vendor/bookings/' . $this->booking->id));
        } else {
            $mail->action('View Booking', url('/bookings/' . $this->booking->id));
        }

        return $mail->line('Thank you for using our service!');
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
