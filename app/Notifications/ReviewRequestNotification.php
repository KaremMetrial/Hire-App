<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReviewRequestNotification extends Notification
{
    use Queueable;

    public function __construct(
        private Booking $booking,
        private string $reviewToken
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $reviewUrl = config('app.url') . "/reviews/{$this->reviewToken}";

        return (new MailMessage)
            ->subject('Please Review Your Recent Car Rental')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Thank you for choosing our car rental service.')
            ->line('We hope you had a great experience with your recent booking.')
            ->line('Booking Details:')
            ->line('- Booking Number: ' . $this->booking->booking_number)
            ->line('- Car: ' . $this->booking->car->carModel->name)
            ->line('- Rental Dates: ' . $this->booking->pickup_date->format('M d, Y') . ' to ' . $this->booking->return_date->format('M d, Y'))
            ->action('Leave a Review', $reviewUrl)
            ->line('Your feedback helps us improve our service and assists other customers in making informed decisions.')
            ->line('This review link will expire in 30 days.')
            ->salutation('Thank you for your business!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Review Request',
            'message' => 'Please review your recent car rental experience',
            'booking_id' => $this->booking->id,
            'booking_number' => $this->booking->booking_number,
            'car_name' => $this->booking->car->carModel->name,
            'review_token' => $this->reviewToken,
            'review_url' => config('app.url') . "/reviews/{$this->reviewToken}",
            'expires_at' => now()->addDays(30),
        ];
    }
}
