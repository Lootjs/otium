<?php

namespace Loot\Otium;

use Illuminate\Routing\Route;
use Loot\Otium\Parsers\ParseRoute;

class FetchRoute {
    /**
     * @var array storage for fetched routes.
     */
    private $paths = [];

    /**
     * @param Route[] $routes
     * @return void
     */
    public function load(array $routes): void
    {
        foreach ($routes as $route) {
            if ($path = \app(ParseRoute::class, ['route' => $route])->start()) {ddd($path);
                $this->addPath($path);
            }
        }
    }

    /**
     * @return array
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * Add route to storage.
     *
     * @param Route $path
     * @return void
     */
    private function addPath(ParseRoute $path): void
    {
        $this->paths[$path->uri][$path->method] = $path;
    }
}
