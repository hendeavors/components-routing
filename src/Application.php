<?php

namespace Endeavors\Components\Routing;

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
    
    /**
     * Load a new application with our new Request.
     */
    public static function load()
    {
        static::requestClass(new Request);

        return new static;
    }
}
