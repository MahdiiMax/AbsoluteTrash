<?php

declare(strict_types=1);

namespace Trash\Routing;

use ReflectionClass;
use ReflectionMethod;
use Trash\Routing\Attributes\Route as RouteAttribute;

class RouteScanner
{
    public function __construct(private Router $router) {}

    private function scanFile(string $file): void
    {
        $before = get_declared_classes();
        require_once $file;
        foreach (array_values(array_diff(get_declared_classes(), $before)) as $class) {
            $reflection = new ReflectionClass($class);
            foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                foreach ($method->getAttributes(RouteAttribute::class) as $attribute) {
                    $route = $attribute->newInstance();
                    $this->router->addRoute(
                        $route->methods,
                        $route->path,
                        [$reflection->getName(), $method->getName()],
                        $route->name,
                        $route->middleware
                    );
                }
            }
        }
    }

    public function scan(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }
        foreach (glob($directory . DIRECTORY_SEPARATOR . '*.php') ?: [] as $file) {
            $this->scanFile($file);
        }
        foreach (glob($directory . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) ?: [] as $dir) {
            $this->scan($dir);
        }
    }
}
