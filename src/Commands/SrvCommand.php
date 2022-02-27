<?php

namespace PatrikGrinsvall\XConsole\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Env;
use PatrikGrinsvall\XConsole\Events\XConsoleEvent;
use PatrikGrinsvall\XConsole\ServiceProviders\FileWatcher;
use PatrikGrinsvall\XConsole\ServiceProviders\ProcessRunner;
use PatrikGrinsvall\XConsole\Traits\HasTheme;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\PhpExecutableFinder;


class SrvCommand extends Command
{
    use HasTheme;

    public array $processes;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'x:srv';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Improvement of laravels default serve command with easier to read output';

    /**
     * The current port offset.
     *
     * @var int
     */
    protected $portOffset = 0;
    private   $processRunner, $filewatcher;

    private $stdin;
    private $shouldRestart = false;


    private $_SERVER;

    /**
     * Execute the console command.
     *
     * @return int
     *
     * @throws Exception
     */
    public function handle()
    {
        chdir(public_path());

        $this->_SERVER = $_SERVER['_'];
        $this->registerShutdown();

        if ($this->option('demo')) $this->demoTheme();

        $this->processRunner = ProcessRunner::make();
        $this->filewatcher   = FileWatcher::make(base_path('.env'), callback: function () {
            XConsoleEvent::dispatch("Cleaning cache files and restarting all services");
            $this->call("x:clean");
            $this->call('config:cache');
            $this->processRunner->restartAll();
        });
        $this->filewatcher->add(__FILE__);

        $this->processRunner->add('serve', $this->cmd(), public_path());
        $this->serve();

        $this->loop();


        return true;
    }

    public function registerShutdown()
    {
        $restartFunction = function () {
            $cmd   = $_SERVER['_'];
            $paths = [ $_SERVER['SCRIPT_FILENAME'],
                       $_SERVER['PWD'] . DIRECTORY_SEPARATOR . 'artisan',
            ];
            foreach ($paths as $path) {
                if (file_exists($path)) {
                    break;
                }
            }
            pcntl_exec($cmd, [ $path,
                               "srv",
            ]);
        };
        register_shutdown_function($restartFunction);
    }

    /**
     * Get the full server command.
     *
     * @return array
     */
    protected function cmd()
    {
        return [ (new PhpExecutableFinder)->find(false),
                 '-S',
                 $this->option('host') . ':' . $this->option('port'),
                 base_path('server.php'),
        ];
    }

    public function serve()
    {
        $this->supportsColors();
        error_log("\x1b[38;2;255;100;0mTesting\x1b[0m\e[38;2;155;255;0mTrueColor\e[0m\n");
        $this->stdout('Starting', 'Extended', 'Dev', 'Server', "http://" . Env::get("SERVER_ADDR") . ":" . Env::get('SERVER_PORT'));
        $this->processRunner->run(function ($a, $b) {
            error_log($a . "-" . $b);
        });
    }

    public function loop()
    {
        $running = true;
        while ($running) {

            $changes = $this->filewatcher->count_changes();
            if ($changes !== 0) {
                $this->call('z:z');
                $this->processRunner->restartAll();
                $this->shouldRestart = true;
                $running             = false;
            }

            usleep(500 * 1000);
        }
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [ [ 'host',
                   null,
                   InputOption::VALUE_OPTIONAL,
                   'The host address to serve the application on',
                   Env::get('SERVER_ADDR', '127.0.0.1'),
                 ],
                 [ 'port',
                   null,
                   InputOption::VALUE_OPTIONAL,
                   'The port to serve the application on',
                   Env::get('SERVER_PORT', 8000),
                 ],
                 [ 'tries',
                   null,
                   InputOption::VALUE_OPTIONAL,
                   'The max number of ports to attempt to serve from',
                   10,
                 ],
                 [ 'demo',
                   null,
                   InputOption::VALUE_NONE,
                   'show demo of theme',
                 ],
        ];
    }
}
