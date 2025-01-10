<?php

namespace Kudashevs\LaravelLastModified\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LastModified
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!config('last-modified.enable')) {
            return $response;
        }

        $lastModifiedTime = $this->getLastModified($response);
        $response->header('Last-Modified', $lastModifiedTime);

        if ($request->headers->has('If-Modified-Since')) {
            $lastAccessTime = $request->headers->get('If-Modified-Since');

            /*
             * The HTTP If-Modified-Since request header makes a request conditional. The server sends back the requested resource,
             * with a 200 status, only if it has been modified after the date in the If-Modified-Since header. If the resource has
             * not been modified since, the response is a 304 without any body, and the Last-Modified response header of the previous
             * request contains the date of the last modification.
             *
             * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-Modified-Since
             */
            if (strtotime($lastModifiedTime) <= strtotime($lastAccessTime)) {
                $response->setStatusCode(304);
            }
        }

        return $response;
    }

    /**
     * @param Response $response
     * @return string
     */
    private function getLastModified(Response $response): string
    {
        $lastModificationTimestamp = $this->retrieveLastModified($response);

        return date('D, d M Y H:i:s \G\M\T', $lastModificationTimestamp);
    }

    /**
     * @param Response $response
     * @return int
     */
    private function retrieveLastModified(Response $response): int
    {
        $timestamp = time();

        if (is_object($response->original) && method_exists($response->original, 'getPath')) {
            $timestamp = filemtime($response->original->getPath());
        }

        return $timestamp;
    }
}
