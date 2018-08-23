<?php

namespace Endeavors\Components\Routing\Tests\Integration\Routing;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Route;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Routing\Middleware\ValidateSignature;
use Endeavors\Components\Routing\Tests\TestCase;
use Endeavors\Components\Routing\Tests\Support\Carbon;

/**
 * @group integration
 */
class UrlSigningTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function test_signing_url()
    {
        Route::get('/foo/{id}', ['as' => 'foo', function ($id) {
            $request = app('request');

            return $request->hasValidSignature() ? 'valid' : 'invalid';
        }]);

        $this->assertTrue(is_string($url = URL::signedRoute('foo', ['id' => 1])));

        $this->assertEquals('valid', $this->get($url)->original);
    }

    public function test_temporary_signed_urls()
    {
        Route::get('/foo/{id}', ['as' => 'foo', function ($id) {
            $request = app('request');

            return $request->hasValidSignature() ? 'valid' : 'invalid';
        }]);

        Carbon::setTestNow(Carbon::create(2018, 1, 1));
        $this->assertTrue(is_string($url = URL::temporarySignedRoute('foo', \Carbon\Carbon::now()->addMinutes(5), ['id' => 1])));
        $this->assertEquals('valid', $this->get($url)->original);

        Carbon::setTestNow(Carbon::create(2018, 1, 1)->addMinutes(10));
        $this->assertEquals('invalid', $this->get($url)->original);
    }

    public function test_signed_url_with_url_without_signature_parameter()
    {
        Route::get('/foo/{id}', ['as' => 'foo', function ($id) {
            $request = app('request');

            return $request->hasValidSignature() ? 'valid' : 'invalid';
        }]);

        $this->assertEquals('invalid', $this->get('/foo/1')->original);
    }
}