<?php

namespace Endeavors\Components\Routing\Tests;

use Illuminate\Foundation\Application as OriginalApplication;
use Endeavors\Components\Routing\RoutingServiceProvider;

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
}