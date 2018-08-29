<?php

namespace Endeavors\Components\Routing\Tests;

use Endeavors\Components\Routing\RoutingServiceProvider;
use Endeavors\Components\Routing\FoundationServiceProvider;
use Orchestra\Testbench\TestCase as OriginalTestCase;
use Illuminate\Config\EnvironmentVariables;
use Illuminate\Config\Repository as Config;
use Illuminate\Foundation\AliasLoader;
use Endeavors\Components\Routing\Request;
use Illuminate\Support\Facades\Facade;

class TestCase extends OriginalTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            RoutingServiceProvider::class,
            FoundationServiceProvider::class
        ];
    }

    /**
     * Visit the given URI with a GET request.
     *
     * @param  string  $uri
     * @param  array  $headers
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    public function get($uri, array $headers = [])
    {
        $server = $this->transformHeadersToServerVars($headers);
        return $this->call('GET', $uri, [], [], [], $server);
    }
}
