<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Otium Documentation Generator Configuration
    |--------------------------------------------------------------------------
    |
    | Set API version
    |
    */
    'version' => '1.0',

    /*
    |
    | Servers
    |
    */
    'servers' => [
        [
            'url' => '/acl/',
        ],
        /*[
            'url' => env('APP_URL'),
        ],*/
    ],

    /*
    |
    | Meta information
    |
    */
    'meta' => [
        'title' => 'API Documentation',
        'description' => 'This documentation was generated with Otium',
        'contact' => [
            'email' => '',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Ignore routes
    |--------------------------------------------------------------------------
    |
    | You can exclude some routes by uri or prefix
    |
    */
    'exclude' => [
        /*
        |
        | if route uri starts with underscore symbol, route would be exclude
        |
        */
        'ignore_private_routes' => true,

        /*
        |
        | if route uri match with those routes, route would be exclude
        | You can exclude route group with wildcard symbol
        |
        */
        'ban' => [
            //'debug/*',
            //'user/logout'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Export settings
    |--------------------------------------------------------------------------
    |
    | Set export type, path to export
    |
    */
    'export' => [
        /*
        |--------------------------------------------------------------------------
        | Documentation format
        |--------------------------------------------------------------------------
        |
        | Currently supported: openapi
        | http://apiblueprint.org/ will be added soon
        |
        */
        'format' => 'openapi',

        /*
        |--------------------------------------------------------------------------
        | Documentation exporters
        |--------------------------------------------------------------------------
        |
        | Currently supported: openapi
        | http://apiblueprint.org/ will be added soon
        |
        */
        'exporters' => [
            'openapi' => \Loot\Otium\Writers\OpenApiWriter::class,
        ],

        /*
        |--------------------------------------------------------------------------
        | Export path
        |--------------------------------------------------------------------------
        |
        | Provide export path
        |
        */
        'path' => storage_path('api-docs'),
        'filename' => 'api-docs.json',
    ]
];
