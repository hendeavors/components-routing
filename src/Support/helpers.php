<?php

use Endeavors\Support\VO\Time\Week;
use Endeavors\Support\VO\Time\Day;

if (!function_exists('signed_route')) {
    /**
     * Create a signed route URL for a named route.
     *
     * @param  string  $name
     * @param  array  $parameters
     * @param  \DateTimeInterface|int  $expiration
     * @param  bool  $absolute
     * @return string
     */
    function signed_route($name, $parameters = [], $expiration = null)
    {
        return app('url')->signedRoute($name, $parameters, $expiration);
    }
}

if (!function_exists('temporary_signed_route')) {
    /**
     * Create a temporary signed route URL for a named route.
     *
     * @param  string  $name
     * @param  \DateTimeInterface|int  $expiration
     * @param  array  $parameters
     * @param  bool  $absolute
     * @return string
     */
    function temporary_signed_route($name, $expiration, $parameters = [])
    {
        return app('url')->temporarySignedRoute($name, $expiration, $parameters);
    }
}

if (!function_exists('one_week_route')) {
    /**
     * Create a temporary signed route URL for a named route.
     *
     * @param  string  $name
     * @param  \DateTimeInterface|int  $expiration
     * @param  array  $parameters
     * @param  bool  $absolute
     * @return string
     */
    function one_week_route($name, $parameters = [])
    {
        $time = Week::create(1);

        return app('url')->temporarySignedRoute($name, $time->toSeconds(), $parameters);
    }
}

if (!function_exists('one_day_route')) {
    /**
     * Create a temporary signed route URL for a named route.
     *
     * @param  string  $name
     * @param  \DateTimeInterface|int  $expiration
     * @param  array  $parameters
     * @param  bool  $absolute
     * @return string
     */
    function one_day_route($name, $parameters = [])
    {
        $time = Day::create(1);

        return app('url')->temporarySignedRoute($name, $time->toSeconds(), $parameters);
    }
}