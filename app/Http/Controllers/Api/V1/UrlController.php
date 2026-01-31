<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateUrlRequest;
use App\Models\Url;
use App\Repositories\UrlRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UrlController extends Controller
{
    public function store(CreateUrlRequest $request, UrlRepository $urlRepository)
    {
        if (!$request->filled('expires_at')) {
            $request->merge(['expires_at' => Carbon::now()->addDays(7)]);
        }

        $url = $urlRepository->singleUrl($request->url);

        if ($url && $url->expires_at && Carbon::parse($url->expires_at)->isPast()) {

            $urlRepository->urlUpdate($url, [
                'short_code' => $this->generateShortCode(),
                'expires_at' => $request->expires_at,
            ]);
        }

        if (!$url) {
            $url = $urlRepository->create([
                'original_url' => $request->url,
                'short_code'   => $this->generateShortCode(),
                'expires_at'   => $request->expires_at,
            ]);
        }

        return response()->json([
            'short_url' => url($url->short_code),
        ], 201);
    }

    public function index(UrlRepository $urlRepository)
    {
        return $urlRepository->getAll();
    }

    public function destroy($id, UrlRepository $urlRepository)
    {
        $resultDelete = $urlRepository->delete($id);

        if (!$resultDelete) {
            return response()->json([
                'message' => 'URL deleted unsuccessfully'
            ]);
        }

        return response()->json([
            'message' => 'URL deleted successfully'
        ]);
    }

    private function generateShortCode()
    {
        return Str::random(6);
    }
}
