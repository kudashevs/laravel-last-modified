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
    private LastModified $middleware;

    protected function setUp(): void
    {
        parent::setUp();

        $this->middleware = new LastModified();
    }

    #[Test]
    public function it_can_set_status_to_ok_when_if_modified_since_is_in_the_past(): void
    {
        $requestTime = $this->timeToIfModifiedSince(time() - 5);

        $response = $this->middleware->handle(
            $this->createRequest('get', '/', $requestTime),
            fn() => new Response(),
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function it_can_set_status_to_not_modified_when_if_modified_since_is_in_the_present(): void
    {
        $requestTime = $this->timeToIfModifiedSince(time());

        $response = $this->middleware->handle(
            $this->createRequest('get', '/', $requestTime),
            fn() => new Response(),
        );

        $this->assertSame(304, $response->getStatusCode());
        $this->assertFalse($response->headers->has('Last-Modified'));
    }

    #[Test]
    public function it_can_set_status_to_not_modified_when_if_modified_since_is_in_the_future(): void
    {
        $requestTime = $this->timeToIfModifiedSince(time() + 5);

        $response = $this->middleware->handle(
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

    #[Test]
    public function it_can_retrieve_from_view_cache(): void
    {
        $responseStub = $this->stubResponseForCache();
        $expectedTime = filemtime(__DIR__);

        $middleware = new LastModified();
        $requestTime = $this->timeToIfModifiedSince($expectedTime - 5);

        $response = $middleware->handle(
            $this->createRequest('get', '/', $requestTime),
            fn() => $responseStub,
        );

        $lastModified = $response->headers->get('Last-Modified');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(strtotime($lastModified), $expectedTime);
    }

    private function createRequest(string $method, string $uri, string $time): Request
    {
        $request = BaseRequest::create($uri, $method);
        $request->headers->set('If-Modified-Since', $time);

        return Request::createFromBase($request);
    }

    private function stubResponseForCache(): Response
    {
        $response = new Response('', 200, []);
        $response->original = new class {
            public function getEngine(): object
            {
                return new class {
                    public function getCompiler(): object
                    {
                        return new class {
                            public function getCompiledPath(string $any): string
                            {
                                return __FILE__;
                            }
                        };
                    }
                };
            }

            public function getPath(): string
            {
                return __FILE__;
            }
        };

        return $response;
    }
}
