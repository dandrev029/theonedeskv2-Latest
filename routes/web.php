<?php

use App\Http\Controllers\AppController as AppController;
use App\Http\Controllers\Auth\VerificationController as WebVerificationController;

// Email verification route
Route::get('/email/verify/{id}/{token}', [WebVerificationController::class, 'verify'])
    ->name('verification.verify.web');

// Catch-all route for the SPA
Route::get('{all}', [AppController::class, 'index'])->where('all', '^((?!api).)*')->name('index');
