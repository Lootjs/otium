<?php

namespace Loot\Otium\Writers;

use Illuminate\Support\Facades\File;
use Loot\Otium\FetchRoute;

class OpenApiWriter implements DocumentationWriter
{
    public const OPENAPI_VERSION = '3.0.0';

    /**
     * @var FetchRoute
     */
    private $fetchRoute;

    /**
     * @param FetchRoute $fetchRoute
     */
    public function save(FetchRoute $fetchRoute)
    {
        $this->fetchRoute = $fetchRoute;
        $content = $this->organizeContent();
        $json = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );

        if (! File::isDirectory(config('otium.export.path'))) {
            File::makeDirectory(config('otium.export.path'));
        }

        File::put(config('otium.export.path').'/'.config('otium.export.filename'), $json);
    }

    /**
     * @return array
     */
    private function organizeContent(): array
    {
        return [
            'openapi' => self::OPENAPI_VERSION,
            'info' => [
                'title' => config('otium.meta.title'),
                'description' => config('otium.meta.description'),
                'contact' => [
                    'email' => config('otium.meta.contact.email'),
                ],
                'version' => config('otium.version'),
            ],
            'servers' => config('otium.servers'),
            'paths' => $this->organizePaths(),
        ];
    }

    /**
     * @return array
     */
    private function organizePaths(): array
    {
        $result = [];
        $paths = $this->fetchRoute->getPaths();

        foreach ($paths as $uri => $pathList) {
            $method = strtolower(key($pathList));

            foreach ($pathList as $path) {
                $tmpData = [
                    'tags' => $path['tags'],
                    'summary' => $path['summary'],
                    'description' => $path['description'],
                    'operationId' => $path['operationId'],
                    'parameters' => $path['parameters'],
                ];

                $tmpData += $path['extra'];

                $result[$uri][$method] = $tmpData;
            }
        }

        return $result;
    }
}
