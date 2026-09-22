<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class AboutController extends Controller
{
    /**
     * The "About Us" screen's text, read from config/about.php instead of
     * being hardcoded in the Flutter app — lets it be updated with a
     * backend deploy only, no new app build/release needed.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'title' => config('about.title'),
            'body' => config('about.body'),
        ]);
    }
}
