<?php

namespace Endeavors\Components\Routing;

use Carbon\Carbon;
use Illuminate\Http\Request;

class SignatureValidator
{
    private $request;

    private $keyResolver;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function passes()
    {
        $original = rtrim($this->request->url().'?'.http_build_query(
            Arr::except($this->request->query(), 'signature')
        ), '?');

        $expires = Arr::get($this->request->query(), 'expires');
        $signature = hash_hmac('sha256', $original, call_user_func($this->keyResolver));
        return  hash_equals($signature, $this->request->query('signature', '')) && !($expires && Carbon::now()->getTimestamp() > $expires);
    }

    public function setKeyResolver(callable $keyResolver)
    {
        $this->keyResolver = $keyResolver;

        return $this;
    }
}