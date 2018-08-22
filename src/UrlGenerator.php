<?php

namespace Endeavors\Components\Routing;

use Illuminate\Routing\UrlGenerator as OriginalUrlGenerator;
use DateInterval;
use DateTimeInterface;
use Carbon\Carbon;

/**
 * Decorate the UrlGenerator for simplicity
 */
class UrlGenerator extends OriginalUrlGenerator
{
    private $originalUrlGenerator;

    public function __construct(OriginalUrlGenerator $originalUrlGenerator)
    {
        $this->originalUrlGenerator = $originalUrlGenerator;
    }

    /**
	 * Get the full URL for the current request.
	 *
	 * @return string
	 */
	public function full()
	{
		return $this->originalUrlGenerator->fullUrl();
	}

	/**
	 * Get the current URL for the request.
	 *
	 * @return string
	 */
	public function current()
	{
		return $this->originalUrlGenerator->current();
	}

	/**
	 * Get the URL for the previous request.
	 *
	 * @return string
	 */
	public function previous()
	{
		return $this->originalUrlGenerator->previous();
	}

	/**
	 * Generate a absolute URL to the given path.
	 *
	 * @param  string  $path
	 * @param  mixed  $extra
	 * @param  bool|null  $secure
	 * @return string
	 */
	public function to($path, $extra = array(), $secure = null)
	{
		return $this->originalUrlGenerator->to($path, $extra, $secure);
	}

	/**
	 * Generate a secure, absolute URL to the given path.
	 *
	 * @param  string  $path
	 * @param  array   $parameters
	 * @return string
	 */
	public function secure($path, $parameters = array())
	{
		return $this->originalUrlGenerator->secure($path, $parameters);
	}

	/**
	 * Generate a URL to an application asset.
	 *
	 * @param  string  $path
	 * @param  bool|null  $secure
	 * @return string
	 */
	public function asset($path, $secure = null)
	{
		return $this->originalUrlGenerator->asset($path, $secure);
    }

    /**
	 * Generate a URL to a secure asset.
	 *
	 * @param  string  $path
	 * @return string
	 */
	public function secureAsset($path)
	{
		return $this->originalUrlGenerator->secureAsset($path);
    }

    /**
	 * Force the schema for URLs.
	 *
	 * @param  string  $schema
	 * @return void
	 */
	public function forceSchema($schema)
	{
		$this->originalUrlGenerator->forceSchema($schema);
	}

	/**
	 * Get the URL to a named route.
	 *
	 * @param  string  $name
	 * @param  mixed   $parameters
	 * @param  bool  $absolute
	 * @param  \Illuminate\Routing\Route  $route
	 * @return string
	 *
	 * @throws \InvalidArgumentException
	 */
	public function route($name, $parameters = array(), $absolute = true, $route = null)
	{
		return $this->originalUrlGenerator->route($name, $parameters, $absolute, $route);
    }

    /**
	 * Get the URL to a controller action.
	 *
	 * @param  string  $action
	 * @param  mixed   $parameters
	 * @param  bool    $absolute
	 * @return string
	 */
	public function action($action, $parameters = array(), $absolute = true)
	{
		return $this->originalUrlGenerator->action($action, $parameters, $absolute);
    }

    /**
	 * Set the forced root URL.
	 *
	 * @param  string  $root
	 * @return void
	 */
	public function forceRootUrl($root)
	{
		$this->originalUrlGenerator->forceRootUrl($root);
	}

	/**
	 * Determine if the given path is a valid URL.
	 *
	 * @param  string  $path
	 * @return bool
	 */
	public function isValidUrl($path)
	{
		return $this->originalUrlGenerator->isValidUrl($path);
    }

    /**
	 * Get the request instance.
	 *
	 * @return \Symfony\Component\HttpFoundation\Request
	 */
	public function getRequest()
	{
		return $this->originalUrlGenerator->getRequest();
	}

	/**
	 * Set the current request instance.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return void
	 */
	public function setRequest(\Illuminate\Http\Request $request)
	{
		$this->originalUrlGenerator->setRequest($request);
    }

    /**
     * @return bool
     */
    public function hasValidSignature(Request $request)
    {
        $original = rtrim($request->url().'?'.http_build_query(
            Arr::except($request->query(), 'signature')
        ), '?');

        $expires = Arr::get($request->query(), 'expires');
        $signature = hash_hmac('sha256', $original, call_user_func($this->keyResolver));
        return  hash_equals($signature, $request->query('signature', '')) && ! ($expires && Carbon::now()->getTimestamp() > $expires);
    }

    /**
     * Create a signed route URL for a named route.
     *
     * @param  string  $name
     * @param  array  $parameters
     * @param  \DateTimeInterface|int  $expiration
     * @return string
     */
    public function signedRoute($name, $parameters = [], $expiration = null)
    {
        $parameters = $this->formatParameters($parameters);
        if ($expiration) {
            $parameters = $parameters + ['expires' => $this->availableAt($expiration)];
        }
        ksort($parameters);
        $key = call_user_func($this->keyResolver);
        return $this->route($name, $parameters + [
            'signature' => hash_hmac('sha256', $this->route($name, $parameters), $key),
        ]);
    }
    /**
     * Create a temporary signed route URL for a named route.
     *
     * @param  string  $name
     * @param  \DateTimeInterface|int  $expiration
     * @param  array  $parameters
     * @return string
     */
    public function temporarySignedRoute($name, $expiration, $parameters = [])
    {
        return $this->signedRoute($name, $parameters, $expiration);
    }

    /**
     * Format the array of URL parameters.
     *
     * @param  mixed|array  $parameters
     * @return array
     */
    public function formatParameters($parameters)
    {
        $parameters = Arr::wrap($parameters);
        foreach ($parameters as $key => $parameter) {
            if ($parameter instanceof UrlRoutable) {
                $parameters[$key] = $parameter->getRouteKey();
            }
        }
        return $parameters;
    }

    /**
     * Get the "available at" UNIX timestamp.
     *
     * @param  \DateTimeInterface|\DateInterval|int  $delay
     * @return int
     */
    protected function availableAt($delay = 0)
    {
        $delay = $this->parseDateInterval($delay);
        return $delay instanceof DateTimeInterface
                            ? $delay->getTimestamp()
                            : Carbon::now()->addSeconds($delay)->getTimestamp();
    }

    /**
     * If the given value is an interval, convert it to a DateTime instance.
     *
     * @param  \DateTimeInterface|\DateInterval|int  $delay
     * @return \DateTimeInterface|int
     */
    protected function parseDateInterval($delay)
    {
        if ($delay instanceof DateInterval) {
            $delay = Carbon::now()->add($delay);
        }
        return $delay;
    }

    /**
     * The encryption key resolver callable.
     *
     * @var callable
     */
    protected $keyResolver;

    /**
     * Set the encryption key resolver.
     *
     * @param  callable  $keyResolver
     * @return $this
     */
    public function setKeyResolver(callable $keyResolver)
    {
        $this->keyResolver = $keyResolver;
        return $this;
    }
}
