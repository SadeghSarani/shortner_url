<?php

namespace App\Http\Controllers;

use App\Models\Url;
use App\Repositories\UrlRepository;
use Illuminate\Http\Request;

class RedirectController extends Controller
{
    public function redirect($short_code, UrlRepository $urlRepository)
    {
        $url = $urlRepository->getUrlWithShortCode($short_code);

        if ($url->expires_at && now()->greaterThan($url->expires_at)) {

            return response()->json([
                'message' => 'Link expired'
            ], 410);
        }

        $url->increment('clicks');

        return redirect()->away($url->original_url, 301);
    }
}
