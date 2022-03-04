<?php

namespace PatrikGrinsvall\XConsole\Commands;

use Exception;
use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Support\Env;
use PatrikGrinsvall\XConsole\Commands\BaseCommands\LaravelBaseCommand;
use PatrikGrinsvall\XConsole\Events\XConsoleEvent;
use PatrikGrinsvall\XConsole\Processer\FileWatcher;
use PatrikGrinsvall\XConsole\Processer\ProcessRunner;
use PatrikGrinsvall\XConsole\Traits\HasTheme;
use Symfony\Component\Console\Cursor;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Terminal;
use Symfony\Component\Process\PhpExecutableFinder;


class SrvCommand extends LaravelBaseCommand
{
    use HasTheme, InteractsWithIO;

    public array $processes;
    public       $lastColor  = 0;
    public       $colors     = [ "\e[38;2;255;100;0m", ];
    public       $processRunner, $filewatcher;
    public       $lastUpdate = 0.0;
    public       $cursor, $cursorpos;
    public       $proc_res   = null;
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
    private   $event;
    private   $startTime     = 0;
    private   $stdin;
    private   $shouldRestart = false;
    private   $_SERVER;
    private   $stats         = [ 'uptime' => 0, 'last_output' => 0 ];

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        for ($x = 50; $x <= 255; $x += 50) {

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
        $maxX            = new Terminal();
        $this->cursor    = new Cursor($this->output->getOutput());
        $this->cursorpos = $this->cursor->getCurrentPosition() ?? [ 0, 0 ];
        $this->registerShutdown();

        // if ($this->option('demo')) $this->demoTheme();

        $this->processRunner = ProcessRunner::make();

        $this->filewatcher = FileWatcher::make(base_path('.env'), callback: function () {
            XConsoleEvent::dispatch("Cleaning cache files and restarting all services");
            $this->call("x:clean");
            $this->call('config:cache');
            $this->processRunner->restartAll();
        });
        $this->filewatcher->add(__FILE__);
        $this->processRunner->add('PHP local server', [ (new PhpExecutableFinder)->find(false) . " -S",
                                                        "localhost:8000",
                                                        "-t",
                                                        "server.php" ], dirname(base_path("server.php")));
        $this->processRunner->add('NPM WATCHER', "npm run watch");
        $this->processRunner->add('PAPERBITS WATCHER', 'npm run paper:build');
        $this->serve();
        $this->loop();

        return true;
    }


    public function serve()
    {
        $this->startTime = microtime();
        $this->processRunner->run(function ($type, $msg) {
            XConsoleEvent::dispatch($this->color(strtoupper($type)) . ' | ' . $msg);
        });
    }

    public function color($msg)
    {
        return "\e[38;2;255;" . random_int(50, 255) . ";" . random_int(50, 255) . "m" . $msg . "\e[0m";
    }

    /**
     * @return void
     */
    public function loop()
    {
        $running = true;
        while ($running) {
            $this->updatestats();
            #if ($stats != false) XConsoleEvent::dispatch("->>" . $stats);

            $changes = $this->filewatcher->count_changes();
            if ($changes !== 0) {

                $this->call('x:clean');
                $this->processRunner->restartAll();
                $this->shouldRestart = true;
                $running             = false;
            }

            usleep(500 * 1000);
        }
    }

    /**
     * @param $print
     * @param $extended
     * @return false|void
     */
    public function updatestats($print = true, $extended = false)
    {

        if (!defined("LARAVEL_START")) {
            define('START', round(microtime(false) / 1000));
        } else if (!defined('START')) define('START', LARAVEL_START / 1000);

        $this->stats['uptime'] = date('s') - date("s", START);

        if ($this->stats['uptime'] % 5 != 1) {
            return false;
            sleep(1);
        }
        $this->stats['last_output'] = $this->stats['uptime'];

        if ($print) {
            #$this->cursor->moveToPosition($this->cursorpos[0], $this->cursorpos[1]);
            $this->cursor->moveToPosition(0, 0);
            $this->cursor->clearOutput();
            if ($extended) {
                $header = [ 'type', 'process', 'state', 'cmd', 'cwd', 'timeout', 'forever' ];
            } else $header = [ 'type', 'process', 'state', 'uptime' ];
            $rows   = [];
            $rows[] = [ 'status', 'xconsole', "processes:" . $this->processRunner->runningProcesses, $this->stats['uptime'] ];
            foreach ($this->processRunner->processes as $p) {
                if ($extended) {
                    $rows[] = [ 'process', $p['title'], $p['state'], $p['cmd'], $p['cwd'], $p['timeout'], 'false' ];
                } else $rows[] = [ 'process', $p['title'], $p['state'], $p['last_sign'] ];
            }

            foreach ($this->filewatcher->get_watched() as $p) {
                $rows[] = $extended ? [ 'watched',
                                        $p['path'],
                                        '---',
                                        '---',
                                        date("Y-m-d h:i:s", $p['last_mtime']),
                                        'true' ] : [ "watched",
                                                     basename($p['path']),
                                                     'exists',
                                                     date('Y-m-d h:i:s', $p['last_mtime']) ];

            }
            $this->table($header, $rows);
        }

        /*        $this->lastUpdate = microtime();

                $out = " + " . implode("  ", $header ?? []) . " + \n";
                foreach ($rows as $row) $out .= " | " . implode("  ", $row ?? []) . " | \n";

                return $out;
        */

        #  return $out;
    }


    /**
     * @throws Throwable
     */
    public function throw_dispatch($condition, $message)
    {
        error_log($message);
        throw_if($condition && $this->use_exceptions, new Exception($message));
        $this->dispatch_event_if($condition && $this->event !== null, $message);
    }

    public function dispatch_event_if($condition, $message)
    {
        if ($condition == true) {
            if (is_object($this->event) && method_exists($this->event, 'dispatch')) {
                $this->event::dispatch($message);
            }
        }
    }

    /**
     * Get the full server command.
     *
     * @return array
     */
    protected function cmd()
    {
        return [ (new PhpExecutableFinder)->find(false), '-S', $this->option('host') . ':' . $this->option('port'), base_path('server.php'), ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [ [ 'host', null, InputOption::VALUE_OPTIONAL, 'The host address to serve the application on', Env::get('SERVER_ADDR', '127.0.0.1'), ],
                 [ 'run', null, InputOption::VALUE_OPTIONAL, 'a file with existing processes to run, like a recepie', '../.xconsole.lastrun.yml', ],
                 [ 'port', null, InputOption::VALUE_OPTIONAL, 'The port to serve the application on', Env::get('SERVER_PORT', 8000), ],
                 [ 'tries', null, InputOption::VALUE_OPTIONAL, 'The max number of ports to attempt to serve from', 10, ], ];
    }
}
