<?php

namespace Endeavors\Components\Routing;

use Illuminate\Routing\Redirector;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class FoundationServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRequestSignatureValidation();
    }

    /**
     * Register the signature macros on the request.
     * @todo is URL::getRequest the same as $this
     * @return void
     */
    public function registerRequestSignatureValidation()
    {
        Request::macro('hasValidSignature', function() {
            return URL::hasValidSignature($this ?? URL::getRequest());
        });

        Request::macro('hasInvalidSignature', function() {
            return !URL::hasValidSignature($this ?? URL::getRequest());
        });

        Request::macro('hasValidParameterSignature', function(array $parameters = []) {
            return URL::hasValidParameterSignature($this ?? URL::getRequest(), $parameters);
        });

        Request::macro('hasInvalidParameterSignature', function(array $parameters = []) {
            return !URL::hasValidParameterSignature($this ?? URL::getRequest(), $parameters);
        });
    }
}
