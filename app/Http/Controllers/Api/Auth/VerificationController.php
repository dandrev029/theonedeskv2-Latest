<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\Auth\VerifyEmail;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use stdClass;

class VerificationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum')->only('resend');
    }

    /**
     * Mark the authenticated user's email address as verified.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function verify(Request $request): JsonResponse
    {
        if (!$request->hasValidSignature()) {
            return response()->json(['message' => __('Invalid verification link or link has expired')], 403);
        }

        $user = User::findOrFail($request->route('id'));
        $token = $request->route('token');

        $verifier = DB::table('email_verifiers')
            ->where('email', $user->email)
            ->where('token', $token)
            ->first();

        if (!$verifier) {
            return response()->json(['message' => __('Invalid verification token')], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => __('Email already verified')]);
        }

        $user->markEmailAsVerified();
        DB::table('email_verifiers')->where('email', $user->email)->delete();

        return response()->json(['message' => __('Email verified successfully')]);
    }

    /**
     * Resend the email verification notification.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function resend(Request $request): JsonResponse
    {
        $user = $request->user();

        // Check if user is authenticated
        if (!$user) {
            return response()->json(['message' => __('You must be logged in to resend verification email')], 401);
        }

        // Check if email is already verified
        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => __('Email already verified')]);
        }

        // Check if user was created before email verification was implemented
        if ($user->created_at < '2023-07-01 00:00:00') {
            // Mark email as verified for older accounts
            $user->markEmailAsVerified();
            return response()->json(['message' => __('Your account has been automatically verified')]);
        }

        // Send verification email
        $objNotificationData = new stdClass();
        $objNotificationData->user = $user;
        $user->notify((new VerifyEmail($objNotificationData)));

        return response()->json(['message' => __('Verification link sent')]);
    }
}
