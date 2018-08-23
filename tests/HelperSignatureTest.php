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
class HelperSignatureTest extends TestCase
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

        $this->assertTrue(is_string($url = signed_route('foo', ['id' => 1])));

        $this->assertEquals('valid', $this->get($url)->original);
    }

    public function test_signing_url_using_input_facade()
    {
        Route::get('/foo/{id}', ['as' => 'foo', function ($id) {
            $request = app('request');

            return Input::hasValidSignature() ? 'valid' : 'invalid';
        }]);

        $this->assertTrue(is_string($url = signed_route('foo', ['id' => 1])));

        $this->assertEquals('valid', $this->get($url)->original);
    }

    public function test_signing_url_one_week()
    {
        Route::get('/foo/{id}', ['as' => 'foo', function ($id) {
            $request = app('request');

            return Request::hasValidSignature() ? 'valid' : 'invalid';
        }]);

        $this->assertTrue(is_string($url = one_week_route('foo', ['id' => 1])));

        $this->assertEquals('valid', $this->get($url)->original);

        Carbon::setTestNow(Carbon::now()->addWeek());

        $this->assertEquals('valid', $this->get($url)->original);

        Carbon::setTestNow(Carbon::now()->addWeek()->addSecond());

        $this->assertEquals('invalid', $this->get($url)->original);
    }

    public function test_signing_url_one_day()
    {
        Route::get('/foo/{id}', ['as' => 'foo', function ($id) {
            $request = app('request');

            return Request::hasValidSignature() ? 'valid' : 'invalid';
        }]);

        $this->assertTrue(is_string($url = one_day_route('foo', ['id' => 1])));

        $this->assertEquals('valid', $this->get($url)->original);

        Carbon::setTestNow(Carbon::now()->addDay());

        $this->assertEquals('valid', $this->get($url)->original);

        Carbon::setTestNow(Carbon::now()->addDay()->addSecond());

        $this->assertEquals('invalid', $this->get($url)->original);
    }
}