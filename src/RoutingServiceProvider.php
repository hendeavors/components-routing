<?php

namespace Endeavors\Components\Routing;

use Illuminate\Routing\RoutingServiceProvider as OriginalRoutingServiceProvider;
use Illuminate\Routing\UrlGenerator as OriginalUrlGenerator;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;

class RoutingServiceProvider extends OriginalRoutingServiceProvider
{
    /**
     * Register the URL generator service.
     *
     * @return void
     */
    protected function registerUrlGenerator()
    {
        $this->app['url'] = $this->app->share(function ($app) {
            $routes = $app['router']->getRoutes();
            // The URL generator needs the route collection that exists on the router.
            // Keep in mind this is an object, so we're passing by references here
            // and all the registered routes will be available to the generator.
            $app->instance('routes', $routes);
            $url = new UrlGenerator(new OriginalUrlGenerator(
                $routes, $app->rebinding(
                    'request', $this->requestRebinder()
                )
            ));
            $url->setSessionResolver(function () {
                return $this->app['session'];
            });

            $url->setKeyResolver(function () {
                return $this->app->make('config')->get('app.key');
            });
            // If the route collection is "rebound", for example, when the routes stay
            // cached for the application, we will need to rebind the routes on the
            // URL generator instance so it has the latest version of the routes.
            $app->rebinding('routes', function ($app, $routes) {
                $app['url']->setRoutes($routes);
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
            $redirector = new Redirector($app['url']->getOriginalUrlGenerator());

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
            return URL::hasValidSignature($this);
        });

        Request::macro('hasInvalidSignature', function () {
            return URL::hasInvalidSignature($this);
        });

        Request::macro('hasValidParameterSignature', function (array $parameters = []) {
            return URL::hasValidParameterSignature($this, $parameters);
        });

        Request::macro('hasInvalidParameterSignature', function (array $parameters = []) {
            return ! URL::hasValidParameterSignature($this, $parameters);
        });
    }
}
