<?php

namespace Loot\Otium;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Loot\Otium\Commands\GenerateDocs;
use Loot\Otium\Writers\DocumentationWriter;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * @var string[]
     */
    private $commands = [
        GenerateDocs::class,
    ];

    /**
     * @var string
     */
    private $configFile = __DIR__.'/../config/otium.php';

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands($this->commands);
        }

        $this->publishes([
            $this->configFile => config_path('otium.php'),
        ], 'config');
        $this->mergeConfigFrom($this->configFile, 'otium');
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->app->bind(DocumentationWriter::class, function() {
            $format = config('otium.export.format');
            $writer = config('otium.export.exporters')[$format] ?? 'openapi';

            return new $writer;
        });

        $this->app->singleton(FetchRoute::class, FetchRoute::class);
    }
}
