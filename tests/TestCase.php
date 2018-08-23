<?php

namespace Endeavors\Components\Routing\Tests;

use Orchestra\Testbench\TestCase as OriginalTestCase;
use Illuminate\Config\EnvironmentVariables;
use Illuminate\Config\Repository as Config;
use Illuminate\Foundation\AliasLoader;
use Endeavors\Components\Routing\Request;
use Illuminate\Support\Facades\Facade;

class TestCase extends OriginalTestCase
{
    public function setUp()
    {
        parent::setUp();
    }
    
    /**
     * Creates the application.
     *
     * Needs to be implemented by subclasses.
     *
     * @return \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    public function createApplication()
    {
        $app = $this->resolveApplication();
        $this->resolveApplicationExceptionHandler($app);
        $this->resolveApplicationCore($app);
        $this->resolveApplicationConfiguration($app);
        $this->resolveApplicationHttpKernel($app);
        $this->resolveApplicationConsoleKernel($app);
        $app->make('Illuminate\Foundation\Bootstrap\ConfigureLogging')->bootstrap($app);
        $app->make('Illuminate\Foundation\Bootstrap\HandleExceptions')->bootstrap($app);
        $app->make('Illuminate\Foundation\Bootstrap\RegisterFacades')->bootstrap($app);
        $app->make('Illuminate\Foundation\Bootstrap\SetRequestForConsole')->bootstrap($app);
        $app->make('Illuminate\Foundation\Bootstrap\RegisterProviders')->bootstrap($app);
        $this->getEnvironmentSetUp($app);
        $app->make('Illuminate\Foundation\Bootstrap\BootProviders')->bootstrap($app);
        $app['router']->getRoutes()->refreshNameLookups();
        return $app;
    }
    /**
     * Resolve application implementation.
     *
     * @return \Illuminate\Foundation\Application
     */
    protected function resolveApplication()
    {
        $app = new Application($this->getBasePath());
        $app->bind('Illuminate\Foundation\Bootstrap\LoadConfiguration', 'Orchestra\Testbench\Bootstrap\LoadConfiguration');
        return $app;
    }
}
