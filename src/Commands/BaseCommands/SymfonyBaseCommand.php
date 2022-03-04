<?php

namespace PatrikGrinsvall\XConsole\Commands\BaseCommands;

use Symfony\Component\Console\Command\Command;

class SymfonyBaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'x:symfony';
    protected $name      = "synfonyx";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Starting point for the package X-Console for symfony';

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
     * @return int
     */
    public function handle()
    {
        //$this->call("x:srv");

        return 0;
    }
}
