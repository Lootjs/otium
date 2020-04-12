<?php

namespace Loot\Otium\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Loot\Otium\Generator;

class GenerateDocs extends Command
{
    protected $name = 'loot:generate-docs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate api documentation';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param Generator $generator
     * @return mixed
     */
    public function handle(Generator $generator)
    {
        $start = \microtime(true);
        $generator->start();
        $end = \microtime(true);
        $this->info(\sprintf('Documented %d routes', $generator->getTotalRoutes()));
        $this->info(\sprintf('Elapsed time: %ss', round($end - $start, 3)));
    }
}
