<?php

namespace Kudashevs\LaravelLastModified\Tests\Unit\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Kudashevs\LaravelLastModified\Middleware\LastModified;
use Kudashevs\LaravelLastModified\Tests\Doubles\ResponseProvider;
use Kudashevs\LaravelLastModified\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request as BaseRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;

class LastModifiedTest extends TestCase
{
    private ResponseProvider $provider;

    private LastModified $middleware;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provider = new ResponseProvider();
        $this->middleware = new LastModified();
    }

    #[Test]
    public function it_can_set_status_to_ok_when_if_modified_since_is_in_the_past(): void
    {
        $lastAccessTime = config('last-modified.fallback');
        $requestTime = $this->timeToIfModifiedSince($lastAccessTime - 5);

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
    public function it_can_retrieve_from_a_first_model_in_view_data(): void
    {
        $expectedTime = strtotime('2024-12-01 12:00:00');
        $responseStub = $this->provider->stubResponseWithAModel();

        $requestTime = $this->timeToIfModifiedSince($expectedTime - 5);

        $response = $this->middleware->handle(
            $this->createRequest('get', '/', $requestTime),
            fn() => $responseStub,
        );

        $lastModified = $response->headers->get('Last-Modified');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(strtotime($lastModified), $expectedTime);
    }

    #[Test]
    public function it_can_retrieve_from_a_first_model_in_view_data_by_an_origin(): void
    {
        config()->set('last-modified.origins', ['posted_at']);

        $expectedTime = strtotime('2024-11-01 12:00:00');
        $responseStub = $this->provider->stubResponseWithAModel();

        $requestTime = $this->timeToIfModifiedSince($expectedTime - 5);

        $response = $this->middleware->handle(
            $this->createRequest('get', '/', $requestTime),
            fn() => $responseStub,
        );

        $lastModified = $response->headers->get('Last-Modified');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(strtotime($lastModified), $expectedTime);
    }

    #[Test]
    public function it_can_retrieve_from_a_first_model_in_view_data_and_handle_a_stampless_one(): void
    {
        $expectedTime = config('last-modified.fallback');
        $responseStub = $this->provider->stubResponseWithAStamplessModel();

        $requestTime = $this->timeToIfModifiedSince($expectedTime - 5);

        $response = $this->middleware->handle(
            $this->createRequest('get', '/', $requestTime),
            fn() => $responseStub,
        );

        $lastModified = $response->headers->get('Last-Modified');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(strtotime($lastModified), $expectedTime);
    }

    #[Test]
    public function it_can_retrieve_from_a_first_collection_in_view_data(): void
    {
        $expectedTime = strtotime('2023-12-01 12:00:00');
        $responseStub = $this->provider->stubResponseWithACollection();

        $requestTime = $this->timeToIfModifiedSince($expectedTime - 5);

        $response = $this->middleware->handle(
            $this->createRequest('get', '/', $requestTime),
            fn() => $responseStub,
        );

        $lastModified = $response->headers->get('Last-Modified');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(strtotime($lastModified), $expectedTime);
    }

    #[Test]
    public function it_can_retrieve_from_a_first_collection_in_view_data_by_an_origin(): void
    {
        config()->set('last-modified.origins', ['posted_at']);

        $expectedTime = strtotime('2023-11-01 12:00:00');
        $responseStub = $this->provider->stubResponseWithACollection();

        $requestTime = $this->timeToIfModifiedSince($expectedTime - 5);

        $response = $this->middleware->handle(
            $this->createRequest('get', '/', $requestTime),
            fn() => $responseStub,
        );

        $lastModified = $response->headers->get('Last-Modified');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(strtotime($lastModified), $expectedTime);
    }

    #[Test]
    public function it_can_retrieve_from_a_first_collection_in_view_data_and_handle_an_empty_one(): void
    {
        $expectedTime = config('last-modified.fallback');
        $responseStub = $this->provider->stubResponseWithAnEmptyCollection();

        $requestTime = $this->timeToIfModifiedSince($expectedTime - 5);

        $response = $this->middleware->handle(
            $this->createRequest('get', '/', $requestTime),
            fn() => $responseStub,
        );

        $lastModified = $response->headers->get('Last-Modified');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(strtotime($lastModified), $expectedTime);
    }

    #[Test]
    public function it_can_retrieve_from_a_first_paginator_in_view_data(): void
    {
        $expectedTime = strtotime('2022-12-01 12:00:00');
        $responseStub = $this->provider->stubResponseWithAPaginator();

        $requestTime = $this->timeToIfModifiedSince($expectedTime - 5);

        $response = $this->middleware->handle(
            $this->createRequest('get', '/', $requestTime),
            fn() => $responseStub,
        );

        $lastModified = $response->headers->get('Last-Modified');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(strtotime($lastModified), $expectedTime);
    }

    #[Test]
    public function it_can_retrieve_from_a_first_paginator_in_view_data_by_an_origin(): void
    {
        config()->set('last-modified.origins', ['posted_at']);

        $expectedTime = strtotime('2022-11-01 12:00:00');
        $responseStub = $this->provider->stubResponseWithAPaginator();

        $requestTime = $this->timeToIfModifiedSince($expectedTime - 5);

        $response = $this->middleware->handle(
            $this->createRequest('get', '/', $requestTime),
            fn() => $responseStub,
        );

        $lastModified = $response->headers->get('Last-Modified');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(strtotime($lastModified), $expectedTime);
    }

    #[Test]
    public function it_can_retrieve_from_a_first_paginator_in_view_data_and_handle_an_empty_one(): void
    {
        $expectedTime = config('last-modified.fallback');
        $responseStub = $this->provider->stubResponseWithAnEmptyPaginator();

        $requestTime = $this->timeToIfModifiedSince($expectedTime - 5);

        $response = $this->middleware->handle(
            $this->createRequest('get', '/', $requestTime),
            fn() => $responseStub,
        );

        $lastModified = $response->headers->get('Last-Modified');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(strtotime($lastModified), $expectedTime);
    }

    #[Test]
    public function it_can_retrieve_from_view_cache(): void
    {
        $providerFilepath = (new \ReflectionClass($this->provider))->getFileName();

        $expectedTime = filemtime($providerFilepath);
        $responseStub = $this->provider->stubResponseFromCache();

        $requestTime = $this->timeToIfModifiedSince($expectedTime - 5);

        $response = $this->middleware->handle(
            $this->createRequest('get', '/', $requestTime),
            fn() => $responseStub,
        );

        $lastModified = $response->headers->get('Last-Modified');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(strtotime($lastModified), $expectedTime);
    }

    #[Test]
    public function it_can_retrieve_from_fallback(): void
    {
        $expectedTime = config('last-modified.fallback');
        $responseStub = $this->provider->stubResponseWithNothing();

        $requestTime = $this->timeToIfModifiedSince($expectedTime - 5);

        $response = $this->middleware->handle(
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
}
