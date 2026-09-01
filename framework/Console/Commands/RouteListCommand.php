<?php

declare(strict_types=1);

namespace Trash\Console\Commands;

use Override;
use Trash\Console\Command;
use Trash\Routing\Router;

class RouteListCommand extends Command
{
    protected string $signature = 'route:list';
    protected string $description = 'List all registered routes';

    #[Override]
    public function handle(): int
    {
        $routes = app(Router::class)->getRoutes();
        if (empty($routes)) {
            $this->comment('No routes registered.');
            return 0;
        }
        $rows = [];
        foreach ($routes as $route) {
            $action = $route->getAction();
            $actionStr = is_array($action)
                ? (is_string($action[0]) ? "{$action[0]}@{$action[1]}" : 'Closure')
                : (is_object($action) ? 'Closure' : (string) $action);
            $rows[] = [
                implode('|', $route->getMethods()),
                $route->getPath(),
                $route->getName() ?? '',
                implode(',', $route->getMiddleware()),
                $actionStr,
            ];
        }
        $this->table(['Method', 'URI', 'Name', 'Middleware', 'Action'], $rows);
        return 0;
    }
}
