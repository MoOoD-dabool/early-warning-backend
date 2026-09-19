<?php

/*
|--------------------------------------------------------------------------
| App-specific API messages (English)
|--------------------------------------------------------------------------
| Custom, hand-written response/exception strings used across the mobile
| API controllers — as opposed to lang/en/validation.php, which only
| covers Laravel's auto-generated field-validation messages. Mirrored
| exactly (same keys) by lang/ar/messages.php.
*/

return [

    'auth' => [
        'register_success' => 'Registered successfully. An OTP code was sent to your email.',
        'otp_invalid' => 'Invalid or expired OTP code.',
        'account_verified' => 'Account verified successfully.',
        'already_verified' => 'This account is already verified.',
        'otp_resent' => 'A new OTP code was sent.',
        'invalid_credentials' => 'The provided credentials are incorrect.',
        'not_verified' => 'Account not verified yet. Please verify the OTP sent to your email.',
        'logged_out' => 'Logged out successfully.',
        'reset_code_sent' => 'A password reset code was sent to your email.',
        'reset_code_invalid' => 'Invalid or expired code.',
        'reset_code_verified' => 'Code verified.',
        'password_reset' => 'Password reset successfully.',
        'google_token_invalid' => 'Could not verify Google sign-in. Please try again.',
        'password_format' => 'Password must be 8 to 24 English letters and digits only, and include at least one letter and one number.',
    ],

    'profile' => [
        'updated' => 'Profile updated successfully.',
        'wrong_current_password' => 'The current password is incorrect.',
        'password_changed' => 'Password changed successfully.',
    ],

    'weather' => [
        'no_data' => 'No weather data available for your city yet.',
    ],

    'reports' => [
        'submitted' => 'Report submitted successfully.',
    ],

    'relief' => [
        'incomplete_profile' => 'Please complete your city, street, and building number in your profile before requesting relief.',
        'submitted' => 'Relief request submitted successfully.',
    ],

    'device_tokens' => [
        'registered' => 'Device token registered successfully.',
        'removed' => 'Device token removed successfully.',
    ],

    'felt_reports' => [
        'incomplete_profile' => 'Please complete your city in your profile before reporting that you felt an earthquake.',
        'no_recent_earthquake' => 'There is no recent earthquake near you to report.',
        'already_reported' => 'You already submitted a report for this earthquake.',
        'submitted' => 'Your report was submitted successfully, thank you for your contribution.',
    ],

    'unauthenticated' => 'Unauthenticated. Please log in again.',

];
