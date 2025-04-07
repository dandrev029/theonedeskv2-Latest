<?php

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use stdClass;

class VerifyEmail extends Notification
{
    use Queueable;

    private $notificationData;

    /**
     * Create a new notification instance.
     *
     * @param  stdClass  $objNotificationData
     */
    public function __construct(stdClass $objNotificationData)
    {
        $this->notificationData = $objNotificationData;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject(__('Verify Email Address'))
            ->greeting(__('Hello').' '.$this->notificationData->user->name.',')
            ->line(__('Please click the button below to verify your email address.'))
            ->action(__('Verify Email Address'), $verificationUrl)
            ->line(__('If you did not create an account, no further action is required.'));
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        $token = sha1($notifiable->getEmailForVerification());

        // Store token in email_verifiers table
        \DB::table('email_verifiers')->where('email', $notifiable->email)->delete();
        \DB::table('email_verifiers')->insert([
            'email' => $notifiable->email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);

        return URL::temporarySignedRoute(
            'verification.verify.web',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'token' => $token,
            ]
        );
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
