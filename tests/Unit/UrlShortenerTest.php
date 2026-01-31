<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Url;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class UrlShortenerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_updates_existing_url_if_expired()
    {
        // Create an expired URL
        $url = Url::create([
            'original_url' => 'http://example.com',
            'short_code'   => 'OLD123',
            'expires_at'   => Carbon::now()->subDay(),
        ]);

        $requestData = [
            'url' => 'http://example.com',
            'expires_at' => Carbon::now()->addDays(7),
        ];

        $shortCode = 'NEW456';

        $url = $this->createOrUpdateUrl($requestData, $shortCode);

        $this->assertEquals('NEW456', $url->short_code);
        $this->assertDatabaseHas('urls', [
            'original_url' => 'http://example.com',
            'short_code'   => 'NEW456',
        ]);
    }

    /** @test */
    public function it_can_get_list_of_all_urls()
    {
        Url::create([
            'original_url' => 'http://example1.com',
            'short_code'   => 'CODE1',
            'expires_at'   => Carbon::now()->addDays(5),
        ]);

        Url::create([
            'original_url' => 'http://example2.com',
            'short_code'   => 'CODE2',
            'expires_at'   => Carbon::now()->addDays(5),
        ]);

        $urls = Url::all();

        $this->assertCount(2, $urls);
        $this->assertEquals('http://example1.com', $urls[0]->original_url);
        $this->assertEquals('http://example2.com', $urls[1]->original_url);
    }

    /** @test */
    public function it_redirects_to_original_url()
    {
        $url = Url::create([
            'original_url' => 'http://example.com',
            'short_code'   => 'ABC123',
            'expires_at'   => now()->addDay(),
        ]);

        $response = $this->get('/s/ABC123');

        $response->assertRedirect('http://example.com');
    }

    /** @test */
    public function it_returns_404_if_short_code_not_found()
    {
        $response = $this->get('/s/NOTEXIST');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_returns_410_if_url_expired()
    {
        $url = Url::create([
            'original_url' => 'http://example.com',
            'short_code'   => 'EXPIRED123',
            'expires_at'   => now()->subDay(),
        ]);

        $response = $this->get('/s/EXPIRED123');

        $response->assertStatus(410);
    }


    /** @test */
    public function it_can_remove_a_url()
    {
        $url = Url::create([
            'original_url' => 'http://example.com',
            'short_code'   => 'CODE123',
            'expires_at'   => Carbon::now()->addDays(7),
        ]);

        $url->delete();

        $this->assertDatabaseMissing('urls', [
            'original_url' => 'http://example.com',
            'short_code'   => 'CODE123',
        ]);
    }

    private function createOrUpdateUrl(array $requestData, string $shortCode)
    {
        $url = Url::where('original_url', $requestData['url'])->first();

        if ($url && $url->expires_at && Carbon::parse($url->expires_at)->isPast()) {
            $url->update([
                'short_code' => $shortCode,
                'expires_at' => $requestData['expires_at'],
            ]);
        }

        if (!$url) {
            $url = Url::create([
                'original_url' => $requestData['url'],
                'short_code'   => $shortCode,
                'expires_at'   => $requestData['expires_at'],
            ]);
        }

        return $url;
    }
}
