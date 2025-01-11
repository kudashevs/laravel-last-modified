<?php

namespace Kudashevs\LaravelLastModified\Tests\Unit\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Kudashevs\LaravelLastModified\Middleware\LastModified;
use Kudashevs\LaravelLastModified\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request as BaseRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;

class LastModifiedTest extends TestCase
{
    #[Test]
    public function it_can_set_status_to_ok_when_if_modified_since_is_in_the_past(): void
    {
        $middleware = new LastModified();
        $requestTime = $this->timeToIfModifiedSince(time() - 5);

        $response = $middleware->handle(
            $this->createRequest('get', '/', $requestTime),
            fn() => new Response(),
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function it_can_set_status_to_not_modified_when_if_modified_since_is_in_the_present(): void
    {
        $middleware = new LastModified();
        $requestTime = $this->timeToIfModifiedSince(time());

        $response = $middleware->handle(
            $this->createRequest('get', '/', $requestTime),
            fn() => new Response(),
        );

        $this->assertSame(304, $response->getStatusCode());
        $this->assertFalse($response->headers->has('Last-Modified'));
    }

    #[Test]
    public function it_can_set_status_to_not_modified_when_if_modified_since_is_in_the_future(): void
    {
        $middleware = new LastModified();
        $requestTime = $this->timeToIfModifiedSince(time() + 5);

        $response = $middleware->handle(
            $this->createRequest('get', '/', $requestTime),
            fn() => new Response(),
        );

        $this->assertSame(304, $response->getStatusCode());
        $this->assertFalse($response->headers->has('Last-Modified'));
    }

    #[Test]
    public function it_can_be_disabled(): void
    {
        config()->set('last-modified.enable', false);

        $middleware = new LastModified();
        $requestTime = $this->timeToIfModifiedSince(time() + 5);

        $response = $middleware->handle(
            $this->createRequest('get', '/', $requestTime),
            fn() => new Response(),
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function it_can_abort_aggressively(): void
    {
        config()->set('last-modified.aggressive', true);

        $middleware = new LastModified();
        $requestTime = $this->timeToIfModifiedSince(time());

        try {
            $middleware->handle(
                $this->createRequest('get', '/', $requestTime),
                fn() => new Response(),
            );
        } catch (\Throwable $exception) {
            $this->assertEquals(
                new HttpException(304, ''),
                $exception
            );
        }
    }

    private function createRequest(string $method, string $uri, string $time): Request
    {
        $request = BaseRequest::create($uri, $method);
        $request->headers->set('If-Modified-Since', $time);

        return Request::createFromBase($request);
    }
}
