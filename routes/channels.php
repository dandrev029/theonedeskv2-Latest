<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Channel for user's notifications
Broadcast::channel('notifications.{userId}', function ($user, $userId) {
    \Log::info('Channel authorization request', [
        'channel' => 'notifications.' . $userId,
        'user_id' => $user->id,
        'requested_user_id' => $userId,
        'authorized' => (int) $user->id === (int) $userId
    ]);
    return (int) $user->id === (int) $userId;
});
