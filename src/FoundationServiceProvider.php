<?php

namespace Endeavors\Components\Routing;

use Illuminate\Routing\Redirector;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
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
        //
        $this->registerRequestValidation();
        $this->registerRequestSignatureValidation();
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureFormRequests();
    }

    /**
     * Configure the form request related services.
     *
     * @return void
     */
    protected function configureFormRequests()
    {
        $this->app->afterResolving(function(ValidatesWhenResolved $resolved) {
            $resolved->validate();
        });

        $this->app->resolving(function (FormRequest $request, $app) {
            $this->initializeRequest($request, $app['request']);

            $request->setContainer($app)->setRedirector($app->make(Redirector::class));
        });
    }

    /**
     * Initialize the form request with data from the given request.
     *
     * @param  \Illuminate\Foundation\Http\FormRequest  $form
     * @param  \Symfony\Component\HttpFoundation\Request  $current
     * @return void
     */
    protected function initializeRequest(FormRequest $form, Request $current)
    {
        $files = $current->files->all();

        $files = is_array($files) ? array_filter($files) : $files;

        $form->initialize(
            $current->query->all(), $current->request->all(), $current->attributes->all(),
            $current->cookies->all(), $files, $current->server->all(), $current->getContent()
        );

        if ($session = $current->getSession()) {
            $form->setSession($session);
        }

        $form->setUserResolver($current->getUserResolver());

        $form->setRouteResolver($current->getRouteResolver());
    }

    /**
     * Register the signature macros on the request.
     * @todo is URL::getRequest the same as $this
     * @return void
     */
    public function registerRequestSignatureValidation()
    {
        FormRequest::macro('hasValidSignature', function() {
            return URL::hasValidSignature($this ?? URL::getRequest());
        });

        FormRequest::macro('hasInvalidSignature', function() {
            return !URL::hasValidSignature($this ?? URL::getRequest());
        });

        FormRequest::macro('hasValidParameterSignature', function(array $parameters = []) {
            return URL::hasValidParameterSignature($this ?? URL::getRequest(), $parameters);
        });

        FormRequest::macro('hasInvalidParameterSignature', function(array $parameters = []) {
            return !URL::hasValidParameterSignature($this ?? URL::getRequest(), $parameters);
        });
    }

    /**
     * Register the "validate" macro on the request.
     *
     * @return void
     */
    public function registerRequestValidation()
    {
        FormRequest::macro('validate', function(array $rules, ...$params) {
            return validator()->validate($this->all(), $rules, ...$params);
        });
    }
}
