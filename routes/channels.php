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
    // Cast both IDs to integers to ensure proper comparison
    $userIdInt = (int) $userId;
    $authUserIdInt = (int) $user->id;
    $is_authorized = $authUserIdInt === $userIdInt;
    
    // Log detailed information for debugging
    \Log::info('Channel authorization request', [
        'channel' => 'notifications.' . $userId,
        'user_id' => $user->id,
        'user_id_int' => $authUserIdInt,
        'requested_user_id' => $userId,
        'requested_user_id_int' => $userIdInt,
        'authorized' => $is_authorized,
        'token_type' => request()->bearerToken() ? 'Bearer' : 'None'
    ]);
    
    // Return the user model on success, or false on failure
    return $is_authorized ? $user : false;
});
