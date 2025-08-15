<?php

    namespace App\Notifications;

    use Illuminate\Bus\Queueable;
    use Illuminate\Notifications\Notification;

    class SendOtpNotification extends Notification
    {
        use Queueable;

        public function __construct(private $otp, private $channels)
        {
        }

        public function via($notifiable)
        {
            return $this->channels;
        }

        public function toMail($notifiable)
        {
            /*
             return (new MailMessage)
                            ->subject('Your OTP Code')
                            ->line("Your OTP is: {$this->otp}")
                            ->line('It will expire in 10 minutes.');
            */
        }

        public function toSms($notifiable)
        {
            // Custom SMS logic (Twilio, etc.)
            /*
                 $twilio = new \Twilio\Rest\Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
                $twilio->messages->create($notifiable->phone, [
                    'from' => env('TWILIO_PHONE_NUMBER'),
                    'body' => "Your OTP is: {$this->otp}"
                ]);
            */
        }
    }
