<?php

namespace PatrikGrinsvall\XConsole\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Console\Concerns\HasParameters;
use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command as CommandAlias;
use Symfony\Component\Yaml\Yaml;

class RecordCommand extends Command
{
    use HasParameters;

    /**
     * The console command name.
     *
     * @var string
     */
    public $name = 'x:record';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates process run configuration yaml files to use as templates ';

    /**
     * Execute the console command.
     *
     * @return int
     *
     * @throws Exception
     */
    public function handle()
    {
        $running = true;
        while ( $running ) {
            $filename = $this->ask("What is the output config filename?");
            if ( Str::endsWith($filename, [ '.yml', '.yaml', '.json', '.xml' ]) ) {
                if ( $filename == "" ) $filename = "../Resources/.xconsole." . date("y-m-d_his") . ".yaml";
            } else $filename .= ".yaml";

            $process[ 'cwd' ]        = $this->ask("What is the directory you want to run process from? (default=./)", "./");
            $process[ 'executable' ] = $this->ask("What process you want to run: " . $process[ 'cwd' ] . "?", "");
            $process[ 'title' ]      = $this->ask('What is the process title?(enter=' . $process[ 'executable' ], $process[ 'executable' ]);
            $process[ 'parameters' ] = $this->ask('What is the process parameters? (Separated by space)');
            $process[ 'parameters' ] = explode(" ", $process[ 'parameters' ]);

            $yaml = Yaml::dump($process);
            file_put_contents($filename, $yaml);


        }

        return CommandAlias::SUCCESS;
    }

    public function whileEmpty($q, $default)
    {
        return $this->ask($q, $default);

    }


    function setProcessTitle(string $title): static
    {
        return parent::setProcessTitle("-> Artisan Assistant <-");
    }


}
