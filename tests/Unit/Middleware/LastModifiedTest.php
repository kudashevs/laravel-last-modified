<?php

namespace Kudashevs\LaravelLastModified\Tests\Unit\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Kudashevs\LaravelLastModified\Middleware\LastModified;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as BaseRequest;

class LastModifiedTest extends TestCase
{
    #[Test]
    public function it_returns_ok_status_when_if_modified_since_is_in_the_past(): void
    {
        $middleware = new LastModified();

        $response = $middleware->handle(
            $this->createRequest('get', '/', time() - 5),
            fn() => new Response(),
        );

        $this->assertTrue($response->isOk());
    }

    #[Test]
    public function it_returns_not_modified_when_if_modified_since_is_in_the_present(): void
    {
        $middleware = new LastModified();

        $response = $middleware->handle(
            $this->createRequest('get', '/', time()),
            fn() => new Response(),
        );

        $this->assertSame(304, $response->getStatusCode());
    }

    #[Test]
    public function it_returns_not_modified_when_if_modified_since_is_in_the_future(): void
    {
        $middleware = new LastModified();

        $response = $middleware->handle(
            $this->createRequest('get', '/', time()),
            fn() => new Response(),
        );

        $this->assertSame(304, $response->getStatusCode());
    }

    private function createRequest(string $method, string $uri, int $time): Request
    {
        $request = BaseRequest::create($method, $uri);
        $request->headers->set('If-Modified-Since', $this->timeToIfModifiedSince($time));

        return Request::createFromBase($request);
    }

    private function timeToIfModifiedSince(int $time): string
    {
        return date('D, d M Y H:i:s \G\M\T', $time);
    }
}
