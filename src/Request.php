<?php

namespace Endeavors\Components\Routing;

use Illuminate\Http\Request as OriginalRequest;

class Request extends OriginalRequest
{
    use Traits\Macroable;
}
