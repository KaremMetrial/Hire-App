<?php

    namespace App\Notifications;

    use Illuminate\Bus\Queueable;
    use Illuminate\Notifications\Messages\MailMessage;
    use Illuminate\Notifications\Notification;

    class SendOtpNotification extends Notification
    {
        use Queueable;

        protected string $otp;

        /**
         * Create a new notification instance.
         */

        public function __construct(string $otp)
        {
            $this->otp = $otp;
        }


        /**
         * Get the notification's delivery channels.
         *
         * @return array<int, string>
         */
        public function via(object $notifiable): array
        {
            $channels = [];
            if ($notifiable->email) {
                $channels[] = 'mail';
            }
            // SMS channel is not configured, so only use mail for now
            // if ($notifiable->phone) {
            //     $channels[] = 'sms';
            // }
            return $channels;
        }

        /**
         * Get the mail representation of the notification.
         */
        public function toMail(object $notifiable): MailMessage
        {
            return (new MailMessage)
                ->subject('Your OTP Code')
                ->line("Your OTP is: {$this->otp}")
                ->line('It will expire in 10 minutes.');
        }

        /**
         * Get the array representation of the notification.
         *
         * @return array<string, mixed>
         */
        public function toArray(object $notifiable): array
        {
            return [
                'otp' => $this->otp,
            ];
        }
    }
