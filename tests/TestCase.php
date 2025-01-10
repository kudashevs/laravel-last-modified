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
}
