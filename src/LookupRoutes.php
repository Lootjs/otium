<?php

namespace Loot\Otium;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Str;

class LookupRoutes {

    public const WILDCARD_SYMBOL = '*';

    /**
     * @var Router
     */
    private $router;

    /**
     * @var string[]
     */
    private $bannedRoutes = [];

    /**
     * LookupRoutes constructor.
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
        $this->bannedRoutes = $this->setBannedRoutes();
    }

    public function getAvailableRoutes()
    {
        return $this->filterRoutes(
            $this->getLaravelRoutes()
        );
    }

    /**
     * @return Route[]
     */
    public function getLaravelRoutes(): array
    {
        return $this->router->getRoutes()->getRoutes();
    }

    /**
     * @return string[]
     */
    public function getBannedRoutes(): array
    {
        return $this->bannedRoutes;
    }

    /**
     * @return string[]
     */
    public function setBannedRoutes(): array
    {
        $routes = config('otium.exclude.ban');

        if (config('otium.exclude.ignore_private_routes')) {
            $routes[] = '_*';
        }

        return $routes;
    }

    /**
     * @param Route[] $routes
     * @return Route[]
     */
    private function filterRoutes(array $routes): array
    {
        return array_filter($routes, function (Route $route) {
            return $this->routeInBan($route) === false;
        });
    }

    /**
     * @todo refactor this
     * @param Route $route
     * @return bool
     */
    public function routeInBan(Route $route): bool
    {
        $inBan = false;
        $bannedRoutes = $this->getBannedRoutes();

        foreach ($bannedRoutes as $bannedRoute) {
            if ($this->hasWildcard($bannedRoute)) {
                $newRouteUri = str_replace(self::WILDCARD_SYMBOL, '', $bannedRoute);
                $inBan = Str::startsWith($route->uri, $newRouteUri);
            } else {
                $inBan = $route->uri === $bannedRoute;
            }

            if ($inBan) {
                break;
            }
        }

        return $inBan;
    }

    /**
     * @param string $route
     * @return bool
     */
    private function hasWildcard(string $route): bool
    {
        return Str::contains($route, self::WILDCARD_SYMBOL);
    }
}
