<?php


namespace PatrikGrinsvall\XConsole\Commands\BaseCommands;

use Illuminate\Console\Command;
use Illuminate\Console\Concerns\HasParameters;
use Illuminate\Console\Concerns\InteractsWithIO;

class LaravelBaseCommand extends Command
{
    use HasParameters, InteractsWithIO;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'x:laravel';
    protected $name      = "laravelx";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Starting point for the package X-Console';


}
