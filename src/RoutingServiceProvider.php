<?php

namespace Endeavors\Components\Routing;

use Illuminate\Routing\RoutingServiceProvider as OriginalRoutingServiceProvider;
use Illuminate\Routing\UrlGenerator as OriginalUrlGenerator;
use Illuminate\Routing\Redirector;

class RoutingServiceProvider extends OriginalRoutingServiceProvider
{
    /**
     * Register the URL generator service.
     *
     * @return void
     */
    protected function registerUrlGenerator()
    {
        $this->app['url'] = $this->app->share(function($app)
        {
            // The URL generator needs the route collection that exists on the router.
            // Keep in mind this is an object, so we're passing by references here
            // and all the registered routes will be available to the generator.
            $routes = $app['router']->getRoutes();

            $url = new UrlGenerator(new OriginalUrlGenerator($routes, $app->rebinding('request', function($app, $request)
            {
                $app['url']->setRequest($request);
            })));

            $url->setKeyResolver(function () {
                return $this->app->make('config')->get('app.key');
            });

            return $url;
        });
        // We don't have a FoundationServiceProvider
        // in earlier versions of Laravel
        $this->registerRequestSignatureValidation();

        
    }
    
    /**
     * Register the Redirector service.
     *
     * @return void
     */
    protected function registerRedirector()
    {
        $this->app['redirect'] = $this->app->share(function($app)
        {
            $redirector = new Redirector($app['url']);

            // If the session is set on the application instance, we'll inject it into
            // the redirector instance. This allows the redirect responses to allow
            // for the quite convenient "with" methods that flash to the session.
            if (isset($app['session.store']))
            {
                $redirector->setSession($app['session.store']);
            }

            return $redirector;
        });
    }

    /**
     * Register the "hasValidSignature" macro on the request.
     *
     * @return void
     */
    public function registerRequestSignatureValidation()
    {
        Request::macro('hasValidSignature', function () {
            return $this->app['url']->hasValidSignature($this);
        });

        $this->app::requestClass(new Request);
    }
}
