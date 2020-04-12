<?php

namespace Loot\Otium\Parsers;

use Illuminate\Routing\Route;
use Illuminate\Support\Str;

class ParseParameters
{
    /**
     * @var Route
     */
    private $route;

    /**
     * ParseRoute constructor.
     * @param Route $route
     */
    public function __construct(Route $route)
    {
        $this->route = $route;
    }

    /**
     * @return
     */
    public function start()
    {
        $parameters = [];

        if (count($this->route->parameterNames())) {
            foreach ($this->getRouteParameters($this->route->uri) as $parameter) {
                $parameters[] = [
                    'name' => $parameter['name'],
                    'in' => 'path',
                    'type' => 'text',
                    'required' => $parameter['required'],
                    'description' => '',
                ];
            }
        }

        return $parameters;
    }

    /**
     * Parse parameters from URI.
     *
     * @param string $uri
     * @return array
     */
    private function getRouteParameters(string $uri): array
    {
        preg_match_all('/\{(.*?)\}/', $uri, $matches);

        return array_map(function ($m) {
            return [
                'required' => ! Str::contains($m, '?'),
                'name' => trim($m, '?')
            ];
        }, $matches[1]);

    }
}
