<?php

namespace PatrikGrinsvall\XConsole\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Console\Concerns\HasParameters;
use LaravelPresets\ColorfulConsole\Traits\HasTheme;
use Symfony\Component\Console\Command\Command as CommandAlias;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;

class CleanCommand extends Command
{
    use HasParameters;

    /**
     * The console command name.
     *
     * @var string
     */
    public $name = 'x:clean';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flushes laravel and composer caches a bit harder and with fewer keypresses';

    /**
     * Execute the console command.
     *
     * @return int
     *
     * @throws Exception
     */
    public function handle()
    {
        #foreach($this->getOptions()
        #       $this->addOption($key)


        return CommandAlias::SUCCESS;
    }

    public function forceFlush()
    {

        $commands[] = [ $this->os_call('rm'), base_path('bootstrap' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . '*.php'), ];
        $commands[] = [ $this->os_call('rm'), storage_path('framework' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . '*.php'), ];
        try {
            foreach ($commands as $c) {
                Process::fromShellCommandline($c[0], $c[1])->run(function ($i, $m) use ($c) {
                    $this->line("returned from", $c, $i, $m);
                });
            }
        } catch (Exception $exception) {
            $this->line("Exception", $exception->getMessage());


        }
        /*
                ProcessRunner::make([ [ 'php', 'artisan', 'cache:clear' ], [ 'php', 'artisan', 'config:clear' ], [ 'php', 'artisan', 'coute:clear' ],
                                      [ 'php', 'artisan', 'view:clear' ], [ 'php', 'artisan', 'optimize' ], ]);
                */
        /*
        $this->call('cache:clear');
        $this->call('config:clear');
        $this->call('route:clear');
        $this->call('view:clear');
        $this->call('optimize');
        */
    }

    /**
     * return operating system independant terminal command
     * @param $cmd
     * @param $args
     * @return string
     */
    #[Pure] public function os_call($cmd): string

    {
        return match (trim($cmd)) {
            'rm'      => windows_os() ? 'del -Force ' : 'rm -rf ',
            'default' => 'echo unknown or missing in os_call function'
        };
    }

    function setProcessTitle(string $title): static
    {
        return parent::setProcessTitle("-> Artisan Assistant <-");
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [ [ 'force', 'f', InputOption::VALUE_OPTIONAL, 'force full reflush of all except migrations and seeders', false, ], ];
    }
}
