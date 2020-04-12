<?php

namespace Loot\Otium;

use Illuminate\Routing\Route;
use Loot\Otium\Writers\DocumentationWriter;

class Generator {
    /**
     * @var Route[]
     */
    private $routes = [];

    /**
     * @var DocumentationWriter
     */
    private $writer;

    /**
     * @var FetchRoute
     */
    private $fetcher;

    /**
     * Generator constructor.
     * @param LookupRoutes $routes
     * @param FetchRoute $fetcher
     * @param DocumentationWriter $writer
     */
    public function __construct(LookupRoutes $routes, FetchRoute $fetcher, DocumentationWriter $writer)
    {
        $this->routes = $routes;
        $this->fetcher = $fetcher;
        $this->writer = $writer;
    }

    /**
     * @return int
     */
    public function getTotalRoutes(): int
    {
        return count($this->routes->getAvailableRoutes());
    }

    /**
     * Start documentation writing
     */
    public function start()
    {
        $this->fetcher->load($this->routes->getAvailableRoutes());
        $this->writer->save($this->fetcher);
    }
}
