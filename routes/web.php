<?php

use App\Http\Controllers\AppController as AppController;
use App\Http\Controllers\Auth\VerificationController as WebVerificationController;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Route;

// Email verification route
Route::get('/email/verify/{id}/{token}', [WebVerificationController::class, 'verify'])
    ->name('verification.verify.web');

// Test route for notifications
Route::get('/test-notification/{userId}', function ($userId) {
    $notificationService = app(NotificationService::class);

    $notification = $notificationService->create(
        (int) $userId,
        'Test Notification',
        'This is a test notification sent at ' . date('Y-m-d H:i:s'),
        'test',
        'font-awesome.bell-solid',
        '/dashboard'
    );

    return response()->json([
        'success' => true,
        'message' => 'Notification sent successfully',
        'notification' => $notification
    ]);
});

// Catch-all route for the SPA
Route::get('{all}', [AppController::class, 'index'])->where('all', '^((?!api).)*')->name('index');
