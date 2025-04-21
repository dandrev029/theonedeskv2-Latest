<?php

namespace App\Channels;

use Illuminate\Notifications\Channels\DatabaseChannel;
use Illuminate\Notifications\Notification;

class DatabaseChannelWithCondoLocation extends DatabaseChannel
{
    /**
     * Build the database record for the notification.
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     * @return array
     */
    protected function buildPayload($notifiable, Notification $notification)
    {
        $payload = parent::buildPayload($notifiable, $notification);
        
        // Add condo_location_id to the payload if the notifiable has it
        if (method_exists($notifiable, 'getCondoLocationId')) {
            $payload['condo_location_id'] = $notifiable->getCondoLocationId();
        } elseif (isset($notifiable->condo_location_id)) {
            $payload['condo_location_id'] = $notifiable->condo_location_id;
        } else {
            // Default to null if no condo_location_id is available
            $payload['condo_location_id'] = null;
        }
        
        return $payload;
    }
}
