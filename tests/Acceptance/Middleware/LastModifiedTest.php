<?php

namespace Kudashevs\LaravelLastModified\Tests\Acceptance\Middleware;

use Kudashevs\LaravelLastModified\Middleware\LastModified;
use Kudashevs\LaravelLastModified\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class LastModifiedTest extends TestCase
{
    #[Test]
    public function it_should_add_the_last_modified_header(): void
    {
        $this->fakeRoute('fake');

        $response = $this->get('fake');

        $response->assertHeader('Last-Modified');
    }

    private function fakeRoute(string $route): void
    {
        \Illuminate\Support\Facades\Route::get($route, function () use ($route) {
            return $route;
        })->middleware(LastModified::class);
    }
}
