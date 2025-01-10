<?php

namespace Kudashevs\LaravelLastModified\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    /**
     * Load a Laravel LastModified service provider.
     *
     * @param \Illuminate\Foundation\Application $application
     * @return array
     */
    protected function getPackageProviders($application)
    {
        return ['Kudashevs\LaravelLastModified\Providers\LastModifiedServiceProvider'];
    }

    protected function timeToIfModifiedSince(int $time): string
    {
        return date('D, d M Y H:i:s \G\M\T', $time);
    }
}
