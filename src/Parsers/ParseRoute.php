<?php

namespace Loot\Otium\Parsers;

use Illuminate\Routing\Route;
use Illuminate\Support\Fluent;

class ParseRoute
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
     * Fetch data from route.
     *
     * @return Fluent|null
     */
    public function start(): ?Fluent
    {
        if (! is_string($this->route->getAction()['uses'])) {
            return null; // closure as route handler not supported
        }

        $meta = $this->parseComment();
        $parameters = array_merge(
            $this->parseHeaders(),
            $this->parseParameters(),
            $this->parseFields()
        );

        return new Fluent([
            'uri' => $this->route->uri(),
            'method' => $this->getMethod(),
            'tags' => [$this->getController()],
            'summary' => $this->route->getName(),
            'description' => $meta['description'],
            'operationId' => $this->getOperationId(),
            'uses' => $this->route->getAction()['uses'],
            'parameters' => $parameters,
            'responses' => [
                200 => [
                    'description' => 'Ok',
                ],
                400 => [
                    'description' => 'Что-то не так',
                ],
            ],
        ]);
    }

    /**
     * @return string
     */
    private function getMethod(): string
    {
        return $this->route->methods()[0];
    }

    /**
     * @return string
     */
    private function getController(): string
    {
        [$controllerNamespace, ] = explode('@', $this->route->getAction()['uses']);
        return str_replace('\\', '.', $controllerNamespace);
    }

    /**
     * @return string
     */
    private function getOperationId(): string
    {
        return $this->route->getName() ?? $this->getController().'::'.$this->route->getActionMethod();
    }

    /**
     * @return array
     */
    private function parseHeaders(): array
    {
        return (new ParseHeaders($this->route))->start();
    }

    /**
     * @return Fluent
     */
    private function parseComment(): Fluent
    {
        return (new ParseComment($this->route))->start();
    }

    /**
     * @return array
     */
    private function parseParameters(): array
    {
        return (new ParseParameters($this->route))->start();
    }

    /**
     * @return array
     */
    private function parseFields(): array
    {
        return (new ParseFields($this->route))->start();
    }
}
