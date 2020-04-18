<?php

namespace Loot\Otium\Parsers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;

class ParseFields
{
    /**
     * @var Route
     */
    private $route;

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
    public function start(): array
    {
        if ($this->isGetMethod()) {
            return [];
        }

        return $this->parseFormFields();
    }

    /**
     * @return bool
     */
    private function isGetMethod(): bool
    {
        return $this->route->methods()[0] === 'GET';
    }

    /**
     * @return array
     */
    private function parseFormFields(): array
    {
        $result = [];
        $request = $this->getRequestArgument();

        foreach ($request->rules() as $rule => $validation) {
            $result[] = [
                'name' => $rule,
                'in' => 'formData',
                'type' => $this->recognizeFieldType($validation),
                'required' => $this->fieldIsRequired($validation),
                'description' => $request->attributes()[$rule] ?? $rule,
            ];
        }

        return $result;
    }

    /**
     * @param string|array $validations
     * @return string
     */
    private function recognizeFieldType($validations): string
    {
        if (! is_string($validations)) {
            $validations = $this->tryAsString($validations);
        }

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

    /**
     * @param array $validations
     * @return string
     */
    private function tryAsString(array $validations): string
    {
        return implode('|', array_filter($validations, 'is_string'));
    }

    /**
     * @return FormRequest|null
     */
    private function getRequestArgument(): ?FormRequest
    {
        $reflection = new \ReflectionMethod($this->route->getController(), $this->route->getActionMethod());
        $parameters = $reflection->getParameters();
        $needleParameters = array_filter($parameters, function (\ReflectionParameter $p) {
            return $p->getClass()->isSubclassOf(FormRequest::class);
        });

        if (count($needleParameters) === 0) {
            return new FormRequest;
        }

        /**
         * @var $firstParameter \ReflectionParameter
         */
        $firstParameter = $needleParameters[0];
        $firstParameterType = $firstParameter->getClass()->getName();

        /**
         * @var $request FormRequest
         */
        return new $firstParameterType;
    }

    /**
     * @param array|string $validations
     * @return bool
     */
    private function fieldIsRequired($validations): bool
    {
        if (is_string($validations)) {
            return Str::contains($validations, 'required');
        }

        return in_array('required', $validations);
    }
}
