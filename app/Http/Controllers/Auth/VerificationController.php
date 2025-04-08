<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VerificationController extends Controller
{
    /**
     * Handle the email verification link.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify(Request $request)
    {
        // Get the user and token from the URL
        $userId = $request->route('id');
        $token = $request->route('token');

        // Check if the URL is valid
        if (!$request->hasValidSignature()) {
            return redirect('/auth/verify-email?error=invalid_link');
        }

        // Find the user
        $user = User::find($userId);
        if (!$user) {
            return redirect('/auth/verify-email?error=user_not_found');
        }

        // Check if the token is valid
        $verifier = DB::table('email_verifiers')
            ->where('email', $user->email)
            ->where('token', $token)
            ->first();

        if (!$verifier) {
            return redirect('/auth/verify-email?error=invalid_token');
        }

        // Check if the email is already verified
        if ($user->hasVerifiedEmail()) {
            return redirect('/auth/email-verified?status=already_verified');
        }

        // Check if user has dashboard access (admin or privileged user)
        // If they do, they should already be verified, but just in case
        if ($user->userRole && $user->userRole->dashboard_access) {
            $user->markEmailAsVerified();
            DB::table('email_verifiers')->where('email', $user->email)->delete();
            return redirect('/auth/email-verified?status=success');
        }

        // Mark the email as verified
        $user->markEmailAsVerified();
        DB::table('email_verifiers')->where('email', $user->email)->delete();

        // Redirect to the success page
        return redirect('/auth/email-verified?status=success');
    }
}
