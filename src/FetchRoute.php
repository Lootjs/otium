<?php

namespace Loot\Otium;

use App\Http\Requests\WriteRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Routing\Route;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;

class FetchRoute {
    /**
     * @var array storage for fetched routes.
     */
    private $paths = [];

    /**
     * helpers for recognize form field type
     */
    private $integerRules = [
        'integer',
        'numeric',
        'digits',
    ];

    private $dateRules = [
        'date',
    ];

    private $fileRules = [
        'file',
        'image',
        'dimensions',
    ];

    /**
     * @param Route[] $routes
     * @return void
     */
    public function load(array $routes): void
    {
        foreach ($routes as $route) {
            $this->parseRoute($route);
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
     * @param string $path
     * @param Fluent $data
     * @return void
     */
    private function addPath(string $path, Fluent $data): void
    {
        $this->paths[$path][$data->method] = $data;
    }

    /**
     * Fetch data from route.
     *
     * @param Route $route
     */
    private function parseRoute(Route $route): void
    {
        if (! is_string($route->getAction()['uses'])) {
            return; // closure as route handler not supported
        }

        $method = $route->methods()[0];
        [$controllerNamespace, ] = explode('@', $route->getAction()['uses']);
        $controllerName = str_replace('\\', '.', $controllerNamespace);
        $parameters = array_merge(
            $this->parseHeaders($route),
            $this->parseParameters($route),
            $this->parseFields($route)
        );
        $meta = $this->parseMeta($route);

        $data = new Fluent([
            'uri' => $route->uri(),
            'method' => $method,
            'tags' => [$controllerName],
            'summary' => '',
            'description' => $meta['description'],
            'operationId' => $route->getName() ?? $controllerName.'::'.$route->getActionMethod(),
            'uses' => $route->getAction()['uses'],
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

        $this->addPath($route->uri(), $data);
    }

    /**
     * Parse URI parameters.
     *
     * @param Route $route
     * @return array
     */
    private function parseParameters(Route $route): array
    {
        $parameters = [];

        if (count($route->parameterNames())) {
            foreach ($this->getRouteParameters($route->uri) as $parameter) {
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

    /**
     * @param Route $route
     * @return array
     */
    private function parseHeaders(Route $route): array
    {
        $parameters = [];
        $middlewares = $route->getAction()['middleware'];

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
     * @param Route $route
     */
    private function parseMeta(Route $route): array
    {
        $reader = new PhpDocReader($route->getController(), $route->getActionMethod());

        return [
            'description' => $reader->getDescription(),
        ];
    }

    /**
     * @param Route $route
     */
    private function parseFields(Route $route): array
    {
        if ($this->isGetMethod($route)) {
            return [];
        }

        return $this->parseFormFields($route);
    }

    /**
     * @param Route $route
     * @return array
     */
    private function parseFormFields(Route $route): array
    {
        $result = [];
        $reflection = new \ReflectionMethod($route->getController(), $route->getActionMethod());
        $parameters = $reflection->getParameters();
        $needleParameters = array_filter($parameters, function (\ReflectionParameter $p) {
            return $p->getClass()->isSubclassOf(FormRequest::class);
        });

        if (count($needleParameters) === 0) {
            return $result;
        }

        /**
         * @var $firstParameter \ReflectionParameter
         */
        $firstParameter = $needleParameters[0];
        $firstParameterType = $firstParameter->getClass()->getName();

        /**
         * @var $request FormRequest
         */
        $request = new $firstParameterType;
        foreach ($request->rules() as $rule => $validation) {
            $result[] = [
                'name' => $rule,
                'in' => 'formData',
                'type' => $this->recognizeFieldType($validation),
                'required' => Str::contains($validation, 'required'),
                'description' => $request->attributes()[$rule] ?? $rule,
            ];
        }

        return $result;
    }

    /**
     * @param Route $route
     * @return bool
     */
    private function isGetMethod(Route $route): bool
    {
        return $route->methods()[0] === 'GET';
    }

    /**
     * @param string $validations
     * @return string
     */
    private function recognizeFieldType(string $validations): string
    {
        switch (true) {
            case Str::contains($validations, $this->integerRules):
                return 'integer';

            case Str::contains($validations, $this->dateRules):
                return 'date';

            case Str::contains($validations, $this->fileRules):
                return 'file';

            case Str::contains($validations, 'array'):
                return 'array';

            default:
                return 'text';
        }
    }
}
