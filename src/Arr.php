<?php

namespace Endeavors\Components\Routing;

use Illuminate\Support\Arr as BaseArr;

class Arr extends BaseArr
{
    /**
     * If the given value is not an array and not null, wrap it in one.
     *
     * @param  mixed  $value
     * @return array
     */
    public static function wrap($value)
    {
        if (is_null($value)) {
            return [];
        }

        return ! is_array($value) ? [$value] : $value;
    }
}