<?php

namespace Loot\Otium\Parsers;

use Illuminate\Routing\Route;
use Illuminate\Support\Fluent;
use Loot\Otium\PhpDocReader;

class ParseComment
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
     * @return Fluent
     */
    public function start(): Fluent
    {
        $data = new Fluent;

        try {
            $reader = new PhpDocReader($this->route->getController(), $this->route->getActionMethod());
            $data['description'] = $reader->getDescription();
        } catch (\Exception $e) {

        }

        return $data;
    }
}
