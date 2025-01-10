<?php

namespace Kudashevs\LaravelLastModified\Tests\Acceptance\Middleware;

use Kudashevs\LaravelLastModified\Middleware\LastModified;
use Kudashevs\LaravelLastModified\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class LastModifiedTest extends TestCase
{
    private const DEFAULT_FAKE_URL = '/fake';

    protected function setUp(): void
    {
        parent::setUp();

        $this->fakeRoute(self::DEFAULT_FAKE_URL);
    }

    #[Test]
    public function it_can_be_disabled(): void
    {
        config()->set('last-modified.enable', false);

        $response = $this->get('fake');

        $response->assertHeaderMissing('Last-Modified');
    }

    #[Test]
    public function it_should_add_the_last_modified_header(): void
    {
        $this->fakeRoute('fake');

        $response = $this->get('fake');

        $response->assertHeader('Last-Modified');
    }

    #[Test]
    public function it_should_ignore_if_an_if_none_match_header_is_present(): void
    {
        $this->fakeRoute('fake');

        $response = $this->get(
            'fake',
            [
                'If-None-Match' => '*',
                'If-Modified-Since' => $this->timeToIfModifiedSince(time() + 1),
            ],
        );

        $response->assertStatus(200);
    }

    private function fakeRoute(string $route): void
    {
        \Illuminate\Support\Facades\Route::get($route, function () use ($route) {
            return $route;
        })->middleware(LastModified::class);
    }
}
