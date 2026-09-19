<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Laravel's default auth middleware computes a redirect target by calling
// route('login') internally whenever a request doesn't send
// "Accept: application/json" — this happens before bootstrap/app.php's
// exception handler ever runs, so that handler alone can't prevent the
// crash. The Flutter app always sends that header so it never hit this in
// practice, but any other client (a browser, a bare curl request) would
// have crashed with "Route [login] not defined". This route just needs to
// exist and be named 'login' — Filament's own admin login page is
// separate (filament.admin.auth.login) and unaffected by this.
Route::get('/login', function () {
    return response()->json(['message' => 'Unauthenticated.'], 401);
})->name('login');
