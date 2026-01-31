<?php

namespace App\Repositories;

use App\Models\Url;

class UrlRepository
{
    private Url $model;

    public function __construct()
    {
        $this->model = app(Url::class);
    }

    public function singleUrl($url)
    {
       return Url::where('original_url', $url)->first();
    }

    public function urlUpdate(Url $url, $data)
    {
       return $url->update($data);
    }

    public function create($data)
    {
       return Url::create($data);
    }

    public function getAll()
    {
       return Url::select(
            'original_url',
            'short_code',
            'clicks',
            'created_at',
            'expires_at',
        )->get();
    }

    public function delete($id)
    {
       return Url::findOrFail($id)->delete();
    }

    public function getUrlWithShortCode($short_code)
    {
       return Url::where('short_code', $short_code)->firstOrFail();
    }
}
