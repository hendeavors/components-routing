<?php

namespace Endeavors\Components\Routing\Tests;

use Illuminate\Routing\UrlGenerator as OriginalUrlGenerator;
use Endeavors\Components\Routing\UrlGenerator;
use Illuminate\Http\Request;
use Illuminate\Routing\RouteCollection;

class ValidParamterSignatureTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testParameterCheckingIfRouteInstanceGiven()
    {
        $url = new UrlGenerator(new OriginalUrlGenerator(
            $routes = new RouteCollection,
            $request = Request::create('http://www.foo.com/')
        ));

        $url->setKeyResolver(function() {
            return $this->app->make('config')->get('app.key');
        });

        $url->hasValidParameterSignature(RequestFaker::create('http://www.foo.com/endpoint/bar'), ['foo']);

        $url->hasValidParameterSignature(RequestFaker::create('http://www.foo.com/endpoint'), ['foo']);
    }
}

class RequestFaker extends Request
{
    public function route($param = null)
    {
        $route = new \Illuminate\Routing\Route(['get'], '/endpoint/{foo}', ['@things']);
        $route ->bind($this);
        return $route;
    }
}