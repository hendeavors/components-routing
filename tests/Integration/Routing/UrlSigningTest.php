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
        Route::get('/foo/{id}', ['as' => 'foo', function (Request $request, $id) {
            return $request->hasValidSignature() ? 'valid' : 'invalid';
        }]);

        $this->assertTrue(is_string($url = URL::signedRoute('foo', ['id' => 1])));

        $this->assertEquals('valid', $this->get($url)->original);
    }

    public function test_signing_url_specified_parameters()
    {
        Route::get('/foo/{id}', ['as' => 'foo', function (Request $request, $id) {
            return $request->hasValidParameterSignature(['username']) ? 'valid' : 'invalid';
        }]);

        $validUrl = URL::signedRoute('foo', ['id' => 1, 'username' => 'bob']);

        $inValidUrl = URL::signedRoute('foo', ['id' => 1, 'username' => 'fred']);

        $inValidUrl = $this->swapSignature($validUrl, $inValidUrl);

        $this->assertTrue(is_string($url = URL::signedRoute('foo', ['id' => 1, 'username' => 'bob'])));

        $this->assertEquals('valid', $this->get($url)->original);

        $this->assertEquals('invalid', $this->get($inValidUrl)->original);

        $validUrl = URL::signedRoute('foo', ['id' => 1, 'username' => 'bob']);

        $newValidUrl = URL::signedRoute('foo', ['id' => 1, 'username' => 'bob']);

        $newValidUrl = $this->swapSignature($validUrl, $newValidUrl);

        $this->assertTrue(is_string($url = URL::signedRoute('foo', ['id' => 1, 'username' => 'bob'])));

        $this->assertEquals('valid', $this->get($url)->original);

        $this->assertEquals('valid', $this->get($newValidUrl)->original);
    }

    public function test_signing_url_unspecified_parameters()
    {
        Route::get('/foo/{id}', ['as' => 'foo', function (Request $request, $id) {
            return $request->hasValidParameterSignature(['username', 'lastname']) ? 'valid' : 'invalid';
        }]);

        $validUrl = URL::signedRoute('foo', ['id' => 1, 'username' => 'bob', 'firstname' => 'smith']);

        $inValidUrl = URL::signedRoute('foo', ['id' => 1, 'username' => 'bob', 'firstname' => 'john']);

        $inValidUrl = $this->swapSignature($validUrl, $inValidUrl);

        $this->assertTrue(is_string($url = URL::signedRoute('foo', ['id' => 1, 'username' => 'bob', 'firstname' => 'smith'])));

        $this->assertEquals('valid', $this->get($url)->original);

        $this->assertEquals('invalid', $this->get($inValidUrl)->original);
    }
    
    /**
     * name is a route parameter, not a query parameter
     */
    public function test_signing_url_specified_route_parameters()
    {
        Route::get('/foo/{id}/{name}', ['as' => 'foo', function (Request $request, $id) {
            return $request->hasValidParameterSignature(['name']) ? 'valid' : 'invalid';
        }]);

        $validUrl = URL::signedRoute('foo', ['id' => 1, 'name' => 'bob']);

        $inValidUrl = URL::signedRoute('foo', ['id' => 1, 'name' => 'fred']);

        $inValidUrl = $this->swapSignature($validUrl, $inValidUrl);

        $this->assertTrue(is_string($url = URL::signedRoute('foo', ['id' => 1, 'name' => 'bob'])));

        $this->assertEquals('valid', $this->get($url)->original);

        $this->assertEquals('invalid', $this->get($inValidUrl)->original);
    }

    public function test_temporary_signed_urls()
    {
        Route::get('/foo/{id}', ['as' => 'foo', function (Request $request, $id) {
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
        Route::get('/foo/{id}', ['as' => 'foo', function (Request $request, $id) {
            return $request->hasValidSignature() ? 'valid' : 'invalid';
        }]);

        $this->assertEquals('invalid', $this->get('/foo/1')->original);

        Route::get('/foo/{id}', ['as' => 'foo', function (Request $request, $id) {
            return $request->hasInvalidSignature() ? 'invalid' : 'valid';
        }]);

        $this->assertEquals('invalid', $this->get('/foo/1')->original);
    }

    public function test_signed_url_with_url_without_signature_parameter_specified_parameters()
    {
        Route::get('/foo/{id}', ['as' => 'foo', function (Request $request, $id) {
            return $request->hasValidParameterSignature(['username']) ? 'valid' : 'invalid';
        }]);

        $this->assertEquals('valid', $this->get('/foo/1')->original);

        $this->assertEquals('invalid', $this->get('/foo/1?username=bob')->original);
    }

    public function test_signed_url_with_url_with_signature_parameter_specified_parameters()
    {
        Route::get('/foo/{id}', ['as' => 'foo', function (Request $request, $id) {
            return $request->hasValidParameterSignature(['username']) ? 'valid' : 'invalid';
        }]);

        Route::get('/foo/{id}/{username}', ['as' => 'foo', function (Request $request, $id) {
            return $request->hasValidParameterSignature(['username']) ? 'valid' : 'invalid';
        }]);

        $this->assertEquals('valid', $this->get('/foo/1')->original);

        $this->assertEquals('invalid', $this->get('/foo/1/bob')->original);

        $this->assertEquals('valid', $this->get('/foo/1?signature=1234')->original);

        $this->assertEquals('invalid', $this->get('/foo/1?username=bob&signature=1234')->original);
    }

    protected function swapSignature($validUrl, $inValidUrl)
    {
        $validparts = parse_url($validUrl);
        parse_str($validparts['query'], $query);
        $validSignature = $query['signature'];
        // we swap signatures
        $parts = parse_url($inValidUrl);
        parse_str($parts['query'], $query);
        $query['signature'] = $validSignature;
        $inValidUrl = $parts['scheme'] . "://" . $parts['host'] . $parts['path'] . '?' . http_build_query($query);

        return $inValidUrl;
    }
}