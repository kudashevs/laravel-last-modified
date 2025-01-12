<?php

namespace Kudashevs\LaravelLastModified\Middleware;

use Closure;
use DateTime;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class LastModified
{
    private const IF_MODIFIED_SINCE_DATE_FORMAT = 'D, d M Y H:i:s \G\M\T';

    private const IF_MODIFIED_SINCE_ALLOWED_METHODS = ['GET', 'HEAD'];

    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldSkipProcessing($request)) {
            return $response;
        }

        $lastModifiedTime = $this->getLastModified($response);
        $response->header('Last-Modified', $lastModifiedTime);

        if ($request->headers->has('If-Modified-Since')) {
            $lastAccessTime = $request->headers->get('If-Modified-Since');

            /*
             * A recipient MUST ignore the If-Modified-Since header field if the received field-value is not a valid HTTP-date
             * See RFC 7232, Section 3.3.
             */
            if (!$this->isValidHttpDate($lastAccessTime)) {
                return $response;
            }

            /*
             * The HTTP If-Modified-Since request header makes a request conditional. The server sends back the requested resource,
             * with a 200 status, only if it has been modified after the date in the If-Modified-Since header. If the resource has
             * not been modified since, the response is a 304 without any body, and the Last-Modified response header of the previous
             * request contains the date of the last modification.
             *
             * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-Modified-Since
             */
            if (strtotime($lastModifiedTime) <= strtotime($lastAccessTime)) {
                $response->headers->remove('Last-Modified');

                if (config('last-modified.aggressive')) {
                    abort(304);
                }

                $response->setStatusCode(304);
            }
        }

        return $response;
    }

    private function shouldSkipProcessing(Request $request): bool
    {
        if (config('last-modified.enable') === false) {
            return true;
        }

        /*
         * A recipient MUST ignore If-Modified-Since if the request contains an If-None-Match header field;
         * See RFC 7232, Section 3.3.
         */
        if ($request->hasHeader('If-None-Match')) {
            return true;
        }

        /*
         * A recipient MUST ignore ..., or if the request method is neither GET nor HEAD.
         * See RFC 7232, Section 3.3.
         */
        return !in_array(
            $request->getMethod(),
            self::IF_MODIFIED_SINCE_ALLOWED_METHODS,
        );
    }

    private function isValidHttpDate(string $date): bool
    {
        $converted = DateTime::createFromFormat(self::IF_MODIFIED_SINCE_DATE_FORMAT, $date);

        return $converted && $converted->format(self::IF_MODIFIED_SINCE_DATE_FORMAT) === $date;
    }

    /**
     * @param Response $response
     * @return string
     */
    private function getLastModified(Response $response): string
    {
        $lastModificationTimestamp = $this->retrieveLastModified($response);

        return date(self::IF_MODIFIED_SINCE_DATE_FORMAT, $lastModificationTimestamp);
    }

    /**
     * @param Response $response
     * @return int
     */
    private function retrieveLastModified(Response $response): int
    {
        if (!is_object($response?->original)) {
            return config('last-modified.fallback');
        }

        if ( // original response content has any data
            method_exists($response->original, 'getData')
            && count($response?->original->getData()) > 0
        ) {
            $first = current($response->original->getData());

            if ($this->isModel($first) && count($first->getAttributes()) > 0) {
                if (array_key_exists('updated_at', $first->getAttributes())) {
                    return strtotime($first->getAttributes()['updated_at']);
                }
            }

            if ($this->isCollection($first) && $first->count() > 0) {
                $entity = $first->sortBy('updated_at')->first();
                if (
                    method_exists($entity, 'getAttributes')
                    && array_key_exists('updated_at', $entity->getAttributes())
                ) {
                    return strtotime($entity->getAttributes()['updated_at']);
                }
            }
        }

        if ( // original response content has no data
            method_exists($response->original, 'getPath')
            && method_exists($response->original, 'getEngine')
            && is_object($response->original->getEngine()->getCompiler())
        ) {
            $compiler = $response->original->getEngine()->getCompiler();
            $compiled = $compiler->getCompiledPath($response->original->getPath());

            if (file_exists($compiled)) {
                return (int)filemtime($compiled);
            }
        }

        if ( // no origins for the Last-Modified were found
        method_exists($response->original, 'getPath')
        ) {
            // if nothing was found, use the template last modified date
            return (int)filemtime($response->original->getPath());
        }

        // should never happen but who knows
        return config('last-modified.fallback');
    }

    protected function isModel($entity): bool
    {
        return is_object($entity) && is_a($entity, \Illuminate\Database\Eloquent\Model::class);
    }

    protected function isCollection($entity): bool
    {
        return is_object($entity) && $this->isSupportedCollection(get_class($entity));
    }

    private function isSupportedCollection(string $class): bool
    {
        $supportedClasses = [
            \Illuminate\Support\Collection::class,
            \Illuminate\Support\LazyCollection::class,
            \Illuminate\Database\Eloquent\Collection::class,
        ];

        return in_array($class, $supportedClasses);
    }
}
