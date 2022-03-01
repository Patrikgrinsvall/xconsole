<?php

namespace PatrikGrinsvall\XConsole\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Support\Env;
use PatrikGrinsvall\XConsole\Events\XConsoleEvent;
use PatrikGrinsvall\XConsole\ServiceProviders\FileWatcher;
use PatrikGrinsvall\XConsole\ServiceProviders\ProcessRunner;
use PatrikGrinsvall\XConsole\Traits\HasTheme;
use Symfony\Component\Console\Cursor;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\PhpExecutableFinder;
use xconsole\helpers;
use const START;

class SrvCommand extends Command
{
    use HasTheme, InteractsWithIO;

    public array $processes;
    public       $lastColor  = 0;
    public       $colors     = [ "\e[38;2;255;100;0m", ];
    public       $processRunner, $filewatcher;
    public       $lastUpdate = 0.0;

    public $cursor, $cursorpos;
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
    protected $portOffset    = 0;
    private   $startTime     = 0;
    private   $stdin;
    private   $shouldRestart = false;
    private   $_SERVER;
    private   $stats         = [ 'uptime' => 0, 'last_output' => 0 ];

    public function __construct()
    {
        parent::__construct();
        for ( $x = 50; $x <= 255; $x += 50 ) {

            $this->colors[] = "\e[38;255;$x;$x;0m";
        }

    }

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
        $this->cursor    = new Cursor($this->getOutput()->getOutput());
        $this->cursorpos = $this->cursor->getCurrentPosition() ?? [ 0, 0 ];


        $this->registerShutdown();

        // if ($this->option('demo')) $this->demoTheme();

        $this->processRunner = ProcessRunner::make();
        $this->filewatcher   = FileWatcher::make(base_path('.env'), callback: function () {
            XConsoleEvent::dispatch("Cleaning cache files and restarting all services");
            $this->call("x:clean");
            $this->call('config:cache');
            $this->processRunner->restartAll();
        });
        $this->filewatcher->add(__FILE__);

        $this->processRunner->add('PHP local server', $this->cmd(), public_path());
        $this->processRunner->add('NPM WATCHER', "npm run watch");
        $this->processRunner->add('PAPERBITS WATCHER', 'npm run paper:build');
        $this->serve();

        $this->loop();


        return true;
    }

    public function registerShutdown()
    {

        $restartFunction = function () {
            $cmd   = "php " . $_SERVER[ 'SCRIPT_FILENAME' ];
            $paths = [ "" . $_SERVER[ 'SCRIPT_FILENAME' ], $_SERVER[ 'PWD' ] . DIRECTORY_SEPARATOR . 'artisan', ];
            /* foreach ( $paths as $path ) {
                 if ( file_exists($path) ) {
                     break;
                 }
             }*/
            if ( function_exists('pcntl_exec') ) {
                pcntl_exec($cmd, [ $path, "x:srv", ]);
            } else {
                error_log("pcntl extension not enabled, cannot register shutdown restart");
            }
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
        return [ ( new PhpExecutableFinder )->find(false), '-S', $this->option('host') . ':' . $this->option('port'), base_path('server.php'), ];
    }

    public function serve()
    {
        $this->startTime = microtime();
        $this->line('Starting  Extended Dev Server on: ', Env::get('SERVER_PROTO', 'http'), '://', Env::get("SERVER_ADDR"), ':', Env::get('SERVER_PORT'));
        $this->processRunner->run(function ($type, $msg) {
            error_log("errrlog, type:$type message: $msg");
            XConsoleEvent::dispatch($this->color(strtoupper($type)) . ' | ' . $msg);
        });
    }

    public function line(...$msg)
    {
        foreach ( $msg as $key => $m ) {
            $msg[ $key ] = $this->color($m);
        }
        parent::getOutput()->write($msg);
        if ( count($msg) - 1 == $key ) parent::getOutput()->write("\n");


    }

    public function color($msg)
    {
        return "\e[38;2;255;" . rand(50, 255) . ";" . rand(50, 255) . "m" . $msg . "\e[0m";
    }

    public function loop()
    {
        $running = true;
        while ( $running ) {
            $this->updatestats();
            $changes = $this->filewatcher->count_changes();
            if ( $changes !== 0 ) {
                $this->call('x:clean');
                $this->processRunner->restartAll();
                $this->shouldRestart = true;
                $running             = false;
            }

            usleep(500 * 1000);
        }
    }

    public function updatestats($print = true, $extended = false)
    {

        if ( !defined("LARAVEL_START") ) {
            define('START', microtime());
        } else if ( !defined('START') ) define('START', LARAVEL_START);

        $this->stats[ 'uptime' ] = round(microtime(true) - START, 2);
        if ( $this->stats[ 'last_output' ] - $this->stats[ 'uptime' ] <= 5 ) return;
        $this->stats[ 'last_output' ] = $this->stats[ 'uptime' ];

        if ( $print ) {
            $this->cursor->moveToPosition($this->cursorpos[ 0 ], $this->cursorpos[ 1 ]);
            $this->cursor->clearOutput();
            if ( $extended ) {
                $header = [ 'type', 'process', 'state', 'cmd', 'cwd', 'timeout', 'forever' ];
            } else $header = [ 'type', 'process', 'state', 'uptime' ];
            $rows   = [];
            $rows[] = [ 'status', 'xconsole', "processes:" . $this->processRunner->runningProcesses, $this->stats[ 'uptime' ] ];
            foreach ( $this->processRunner->processes as $p ) {
                if ( $extended ) {
                    $rows[] = [ 'process', $p[ 'title' ], $p[ 'state' ], $p[ 'cmd' ], $p[ 'cwd' ], $p[ 'timeout' ], 'false' ];
                } else $rows[] = [ 'process', $p[ 'title' ], $p[ 'state' ], $p[ 'last_sign' ] ];
            }
            foreach ( $this->filewatcher->paths as $p ) {
                $rows[] = $extended ? [ 'watched', $p[ 'path' ], '---', '---', date("Y-m-d h:i:s", $p[ 'last_mtime' ]), 'true' ] : [ "watched", basename($p[ 'path' ]), 'exists', $p[ 'last_mtime' ] ];
            }
            $this->table($header, $rows);
        }


        $this->lastUpdate = microtime();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [ [ 'host', null, InputOption::VALUE_OPTIONAL, 'The host address to serve the application on', Env::get('SERVER_ADDR', '127.0.0.1'), ], [ 'port', null, InputOption::VALUE_OPTIONAL, 'The port to serve the application on', Env::get('SERVER_PORT', 8000), ], [ 'tries', null, InputOption::VALUE_OPTIONAL, 'The max number of ports to attempt to serve from', 10, ], ];
    }
}
