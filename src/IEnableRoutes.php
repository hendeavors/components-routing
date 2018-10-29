<?php

namespace Endeavors\Components\Routing;

use Illuminate\Routing\RouteCollection;

interface IEnableRoutes
{
    function setRoutes(RouteCollection $routes);
}