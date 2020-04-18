<?php

namespace Loot\Otium\Parsers;

use Illuminate\Routing\Route;

class ParseHeaders
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
     * @return array
     */
    public function start()
    {
        $middlewares = $this->getMiddlewares();
        $parameters = [];

        foreach ($middlewares as $middleware) {
            switch ($middleware) {
                case 'api':
                    $parameters[] = [
                        'name' => 'Accept',
                        'value' => 'application/json',
                        'in' => 'header',
                        'type' => 'text',
                        'required' => true,
                        'description' => '',
                    ];
                    $parameters[] = [
                        'name' => 'Content-Type',
                        'value' => 'application/json',
                        'in' => 'header',
                        'type' => 'text',
                        'required' => true,
                        'description' => '',
                    ];
                    break;

                case 'gateway.auth':
                    $parameters[] = [
                        'name' => 'X-User',
                        'in' => 'header',
                        'type' => 'integer',
                        'required' => true,
                        'description' => '',
                    ];
                    break;
            }
        }

        return $parameters;
    }

    /**
     * @return array
     */
    private function getMiddlewares(): array
    {
        return $this->route->getAction()['middleware'];
    }
}
