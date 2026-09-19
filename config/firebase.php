<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Firebase Cloud Messaging (FCM) HTTP v1
    |--------------------------------------------------------------------------
    */
    'project_id' => env('FIREBASE_PROJECT_ID'),

    /*
    | Path to the service account JSON key, relative to storage/app.
    */
    'credentials_path' => env('FIREBASE_CREDENTIALS_PATH', 'firebase-service-account.json'),

    /*
    | Must match the Android notification channel id created natively in
    | the Flutter app (android/app/.../EarlyWarningApplication.kt) — that
    | channel is the one configured with the custom siren sound. Sent along
    | with any alert whose trigger_siren is true so Android plays the siren
    | instead of the device's default notification sound.
    */
    'siren_android_channel_id' => 'disaster_siren_channel',

];