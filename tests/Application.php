<?php

namespace Endeavors\Components\Routing\Tests;

use Illuminate\Foundation\Application as OriginalApplication;
use Endeavors\Components\Routing\RoutingServiceProvider;
use Illuminate\Events\EventServiceProvider;

class Application extends OriginalApplication
{
    /**
     * Register the routing service provider.
     *
     * @return void
     */
    protected function registerRoutingProvider()
    {
        $this->register(new RoutingServiceProvider($this));
    }

    /**
     * Register all of the base service providers.
     *
     * @return void
     */
    protected function registerBaseServiceProviders()
    {
        $this->register(new EventServiceProvider($this));
        $this->registerRoutingProvider();
    }
}