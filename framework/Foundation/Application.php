<?php

declare(strict_types=1);

namespace Trash\Foundation;

use Trash\Container\Container;
use Trash\Foundation\Facades\Facade;

class Application extends Container
{
    private static ?Application $instance = null;

    public function __construct(private string $basePath)
    {
        self::$instance = $this;
        $this->instance(Application::class, $this);
        Facade::setFacadeApplication($this);
        $this->registerProviders();
    }

    private function registerProviders(): void
    {
        $providers = config('app.providers', []);
        $registered = [];
        foreach ($providers as $providerClass) {
            $provider = new $providerClass($this);
            $provider->register();
            $registered[] = $provider;
        }
        foreach ($registered as $provider) {
            $provider->boot();
        }
    }

    public static function getInstance(): Application
    {
        return self::$instance;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function handle(): void
    {
        echo "Absolute Trash is running.";
    }
}
