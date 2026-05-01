<?php

namespace Kudashevs\LaravelLastModified\Tests\Regression\Middleware;

use Kudashevs\LaravelLastModified\Middleware\LastModified;
use Kudashevs\LaravelLastModified\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class LastModifiedTest extends TestCase
{
    #[Test]
    public function it_can_handle_a_bug_in_the_retrieve_acceptable_languages_intersection(): void
    {
        /*
         * Bug found: 15.01.2025
         * Details: returning response()->file($source) lead to an Error exception:
         * Call to undefined method Symfony\Component\HttpFoundation\BinaryFileResponse::header().
         */
        $this->fakeFileResponse('/file');

        $response = $this->get('/file');

        $response->assertOk();
    }

    private function fakeFileResponse(string $route): void
    {
        \Illuminate\Support\Facades\Route::get($route, function () {
            return response()->file(__FILE__, ['Content-Type' => 'text/txt']);
        })->middleware(LastModified::class);
    }
}
