<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Booking $booking,
        public bool $isVendorNotification = false
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->isVendorNotification
            ? "Booking #{$this->booking->id} is Overdue"
            : "Your Booking #{$this->booking->id} is Overdue";

        $message = $this->isVendorNotification
            ? "Booking is overdue. Please contact the customer to arrange return."
            : "Your booking is overdue. Please return the vehicle as soon as possible to avoid additional charges.";

        $daysOverdue = now()->diffInDays($this->booking->return_date);

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line($message)
            ->line('Booking Details:')
            ->line('Car: ' . $this->booking->car->carModel->name)
            ->line('Original Return Date: ' . $this->booking->return_date->format('Y-m-d'))
            ->line('Days Overdue: ' . $daysOverdue)
            ->action('View Booking', url('/bookings/' . $this->booking->id))
            ->line('Please contact us immediately if you need assistance.')
            ->line('Thank you for using our service!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'type' => 'booking_overdue',
            'car_name' => $this->booking->car->carModel->name,
            'return_date' => $this->booking->return_date,
            'days_overdue' => now()->diffInDays($this->booking->return_date),
            'is_vendor_notification' => $this->isVendorNotification,
        ];
    }
}
