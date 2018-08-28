<?php

namespace Endeavors\Components\Routing\Tests;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Route;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Routing\Middleware\ValidateSignature;
use Endeavors\Components\Routing\Tests\Support\Carbon;

/**
 * @group integration
 */
class FacadeSignatureTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function test_signing_url()
    {
        Route::get('/foo/{id}', ['as' => 'foo', function ($id) {
            $request = app('request');

            return Request::hasValidSignature() ? 'valid' : 'invalid';
        }]);

        $this->assertTrue(is_string($url = URL::signedRoute('foo', ['id' => 1])));

        $this->assertEquals('valid', $this->get($url)->original);

        Route::get('/foo/{id}', ['as' => 'foo', function ($id) {
            $request = app('request');

            return Request::hasInvalidSignature() ? 'invalid' : 'valid';
        }]);

        $this->assertTrue(is_string($url = URL::signedRoute('foo', ['id' => 1])));

        $this->assertEquals('valid', $this->get($url)->original);
    }

    public function test_signing_url_using_input_facade()
    {
        Route::get('/foo/{id}', ['as' => 'foo', function ($id) {
            $request = app('request');

            return Input::hasValidSignature() ? 'valid' : 'invalid';
        }]);

        $this->assertTrue(is_string($url = URL::signedRoute('foo', ['id' => 1])));

        $this->assertEquals('valid', $this->get($url)->original);

        Route::get('/foo/{id}', ['as' => 'foo', function ($id) {
            $request = app('request');

            return Input::hasInvalidSignature() ? 'invalid' : 'valid';
        }]);

        $this->assertTrue(is_string($url = URL::signedRoute('foo', ['id' => 1])));

        $this->assertEquals('valid', $this->get($url)->original);
    }

    public function test_signing_url_specified_parameters_using_input_facade()
    {
        Route::get('/foo/{id}', ['as' => 'foo', function ($id) {
            $request = app('request');

            return Input::hasValidParameterSignature(['username']) ? 'valid' : 'invalid';
        }]);

        $this->assertTrue(is_string($url = URL::signedRoute('foo', ['id' => 1, 'username' => 'bob'])));

        $this->assertEquals('valid', $this->get($url)->original);
    }
}