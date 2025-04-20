<?php

namespace App\Notifications\Ticket;

use App\Models\Ticket;
use App\Traits\CreatesAppNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Str;

class NewTicketReplyFromAgentToUser extends Notification
{
    use Queueable, CreatesAppNotification;

    private $ticket;

    /**
     * Create a new notification instance.
     *
     * @param  Ticket  $ticket
     */
    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
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
        return (new MailMessage)
            ->subject(__('New reply').': '.Str::limit($this->ticket->subject, 35))
            ->greeting(__('Hi').' '.$this->ticket->user->name.',')
            ->line(__('You have received a response on a ticket, you can view the ticket details from this link').':')
            ->action(__('See ticket'), url('/tickets/'.$this->ticket->uuid))
            ->line(__('In order to view the ticket you have to log in with your email and password, if you do not remember the password, you can reset it using the email account that this message has reached').'.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        try {
            // Ensure notifiable has an ID
            if ($notifiable && $notifiable->id) {
                // Create an in-app notification
                $this->createAppNotification(
                    $notifiable->id,
                    __('New reply to your ticket'),
                    __('An agent has replied to your ticket: ') . $this->ticket->subject,
                    'ticket_reply',
                    'font-awesome.comment-alt-solid',
                    '/tickets/' . $this->ticket->uuid
                );
            } else {
                \Log::warning('Cannot create notification: notifiable has no ID', [
                    'ticket_id' => $this->ticket->id,
                    'ticket_uuid' => $this->ticket->uuid
                ]);
            }
        } catch (\Exception $e) {
            // Log the error but don't throw it to prevent breaking the main functionality
            \Log::error('Error in NewTicketReplyFromAgentToUser notification: ' . $e->getMessage(), [
                'ticket_id' => $this->ticket->id,
                'ticket_uuid' => $this->ticket->uuid
            ]);
        }

        return [
            'id' => $this->ticket->id,
            'uuid' => $this->ticket->uuid,
            'subject' => $this->ticket->subject,
            'type' => 'ticket_reply',
            'message' => __('An agent has replied to your ticket')
        ];
    }
}
