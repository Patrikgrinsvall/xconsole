<?php

namespace PatrikGrinsvall\XConsole\Processer;

use Closure;
use PatrikGrinsvall\XConsole\Events\XConsoleEvent;
use RuntimeException;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;
use Throwable;

class ProcessRunner
{
    private static object $i;
    public array          $processes;
    public object         $app;
    public string         $mainRunnerScript = "";
    public array          $processTemplates = [ 'process_index' => [ // title for presentation
                                                                     'title'      => 'echo',
                                                                     // category not used anywhere
                                                                     'category'   => 'shell',
                                                                     // working dir of process
                                                                     'cwd'        => ".",
                                                                     // path to executable (should be full)
                                                                     'executable' => 'echo',
                                                                     // contains out
                                                                     'stdout'     => "",
                                                                     // contains stderr
                                                                     'stderr'     => "",
                                                                     // exit code
                                                                     'code'       => null,
                                                                     // running, exited, suspended, errored, new
                                                                     'state'      => null,
                                                                     // self explained
                                                                     'pid'        => null,
                                                                     // this is the actually runned cmd after process started
                                                                     'cmd'        => '',
                                                                     'timeout'    => 600,
                                                                     // paramters before process started, one entry per parameter, i think in reality one parameter is one space
                                                                     'parameters' => [ 'this', 'is', 'parameters', ], ], ];
    public int            $runningProcesses = 0;
    public Closure|string $shutdownFunction;
    public string         $mainCwd;
    public Closure        $afterEndedCallback;

    /**
     * @factory
     * @param callable|Process|string|null $process
     * @return ProcessRunner
     * @throws Throwable
     */
    public static function make(callable|Process|null|string $process = null): ProcessRunner
    {
        $cls = static::class;

        if (!isset(self::$i[$cls])) {
            self::$i[$cls] = new static();
        }
        self::$i[$cls]->resolveMainRunnerScript();

        if (isset($process)) {
            if (is_string($process)) {
                if (file_exists($process)) {
                    self::$i[$cls]->loadRunFile($process);

                    return self::$i[$cls];
                }

                $process = [ explode(' ', $process) ];
            }

            if (is_array($process) && is_string($process[0])) {

                $process = [ $process ];
            }

            if (is_string($process)) {

                $process = [ explode(" ", $process) ];
            }
            if (is_callable($process)) {

                $process = [ $process ];
            }
            foreach ($process as $proc) {
                self::$i[$cls]->add(null, $proc);
            }
        }

        return self::$i[$cls];
    }

    /**
     * @param $file
     * @return bool
     */
    /*
    public function loadRunFile(string $file = "../.xconsole.lastrun.yml"): bool
    {
        if (file_exists($file)) {
            $$this->processlist['processlist'] = Yaml::parse(file_get_contents($file));

        }
        foreach ($this->processlist['processlist'] as $p) {
            $out[] = [ $p['title'], $p['cmd'], $p['cwd'] ];
            self::$i[static::class]->add($p['title'], $p['cmd'], $p['cwd']);
        }

        return true;
    }
*/

    /**
     * @param string $script
     * @return void
     */
    public function resolveMainRunnerScript(string $script = ""): void
    {
        if ($script === "" && $this->mainRunnerScript === "") {
            $this->mainRunnerScript = $_SERVER['SCRIPT_FILENAME'] ?? xdir('/../../artisan');
        } elseif (!empty($script)) {
            $this->mainRunnerScript = $script;
        } elseif ($this->mainRunnerScript === '') {
            throw new RuntimeException("couldnt resolve script filename? inside :" . __FILE__);
        }
        XConsoleEvent::dispatch('Mainrunner: ' . $this->mainRunnerScript);

    }

    /**
     * Add a new program, closure, executable, an array with cmd arguments or a symfony process to execution queue.
     *
     * @param string|null              $label
     * @param object|array|string|null $process
     * @param string                   $cwd
     * @param int                      $timeout
     * @param bool                     $restart
     * @return $this|void
     * @throws Throwable
     */
    public function add(string $label = null, object|array|string $process = null, string $cwd = ".", int $timeout = 600, bool|int $restart = false)
    {
        $i = 0;
        if ($label === null) {
            return $this;
        }
        $newProcess = null;
        $title      = $label ?? match (gettype($process)) {
                "array"  => $process[0],
                "string" => explode(" ", $process)[0],
                "object" => 'object'
            };
        while (isset($this->processes[$title])) {
            $title = $label . '[' . $i++ . ']';
        }
        if (is_callable($process)) {
            die("NOT SUPPORTED TO ADD CLOSURES");
            ##$newProcess = new PhpProcess('<?php $function=' . $process . '; $function();');

        }
        if (is_string($process)) {
            $process    = explode(" ", $process);
            $newProcess = new Process($process, $cwd);
            #$this->processes[$title] = [ 'title' => $label, 'parameters' => [], 'stderr' => '', 'stdout' => '', 'state' => 'new', 'cwd' => $cwd, 'executable' => $process, 'timeout' => $timeout, 'process' => null, ];
        }
        if (is_array($process)) {
            $newProcess = new Process($process, $cwd);
        }
        throw_if(!isset($newProcess), "Unknown new process in add");

        // @formatter:off
        $this->processes[$title] = [
            'cmd' => $newProcess->getCommandLine(),
            'process' => $newProcess,
            'cwd' => $cwd,
            'last_sign' => 0,
            'status' => $newProcess->getStatus(),
            'title' => $title,
            'parameters' => array_slice($process,1),
            'stderr' =>'',
            'stdout'=>'',
            'executable' => $newProcess->getCommandLine(),
            'timeout'=>$timeout,
            'pid'=>0,
            'restart' => $restart // @todo - add support for 0=forever, x=amount of times

        ];
        // @formatter:on

        ##$this->processes[$title] = [ 'title' => $label, 'parameters' => $process, 'stderr' => '', 'stdout' => '', 'state' => 'new', 'cwd' => $cwd, 'executable' => $process[0], 'timeout' => $timeout, 'process' => null, ];

        XConsoleEvent::dispatch("Process added: " . $title);

        return $this;
    }

    /**
     * @param Closure|null $shutdownFunction
     * @return void
     */
    public function registerShutdown(Closure $shutdownFunction = null): void
    {
        if (!is_null($shutdownFunction)) {
            $this->shutdownFunction = $shutdownFunction;
        }

        if (!isset($this->shutdownFunction)) {
            $this->shutdownFunction = function () {
                $cmd   = 'php ' . $this->mainRunnerScript;
                $pwd   = $_SERVER['PWD'] ?? dirname(__FILE__ . '/../../');
                $paths = [ $_SERVER['SCRIPT_FILENAME'], $pwd . DIRECTORY_SEPARATOR . 'artisan', ];
                foreach ($paths as $path) {
                    if (file_exists($path)) {
                        break;
                    }
                }
                /*
                if (function_exists('pcntl_exec')) {
                    pcntl_exec(, [ base_path('artisan'), 'x:srv', ]);
                } else {
                $this->proc_res = proc_open($cmd,);
                    }
                */
                // todo rebuild for windows
                $this->mainCwd = $cmd;

            };
        }


        register_shutdown_function([ __CLASS__, 'shutdownFunction' ]);
    }

    /**
     * @param $filename
     * @return void
     */
    public function saveProcessList($filename): void
    {
        $processes = $this->processes;
        $yaml      = Yaml::dump($processes);
        file_put_contents(__DIR__ . "./../$filename.yaml", $yaml);


    }

    /**
     * @return void
     */
    public function stopAll(): void
    {
        $this->each($this->processes, function ($process) {
            XConsoleEvent::dispatch('Stopping: ' . $process['title']);
            $process['process']->stop();
        });
        do {
            XConsoleEvent::dispatch('Waiting for processes to terminate ');
            foreach ($this->processes as $i => $p) {
                /** @var Process $process */
                $process = $p['process'];
                if (isset($process) && $process->isRunning() === false) {
                    XConsoleEvent::dispatch('Terminated: ' . $process['title']);
                    $this->runningProcesses--;
                    unset($this->processes[$i]['process']);
                }

            }
            sleep(1);
        } while ($this->runningProcesses > 0);
    }

    /**
     * @param iterable $items
     * @param callable $callback
     * @return void
     */
    public function each(iterable $items, callable $callback): void
    {
        foreach ($items as $processIndex => $process) {
            if (is_callable($callback)) {
                $callback($process, $processIndex);
            }
        }
    }

    /**
     * @return void
     */
    public function restartAll(): void
    {

        $this->dumpProcesses();
        if ($this->runningProcesses > 0) {
            XConsoleEvent::dispatch('Restarting all ');
            $this->each($this->processes, function ($process, $processIndex) {
                $this->restart($processIndex);
            });
        }
    }

    /**
     * @return void
     */
    public function dumpProcesses(): void
    {
        $filename = "../.xconsole.lastrun.yaml";
        $yaml     = Yaml::dump([ 'processlist' => $this->processes ]);
        file_put_contents($filename, $yaml);
    }

    /**
     * @param int|object|callable|array $process
     * @return false|void
     */
    public function restart(int|object|callable|array $process)
    {
        $shouldRestart = false;
        $newProcess    = null;
        if (is_array($process)) {
            // assume its going to be a symfony process
            if (!isset($process['process'])) {
                $newProcess['process'] = new Process($process);

            }
            die("NOT SUPPORTED TO RESTART ARRAYS!");

        }
        if (is_int($process)) {
            $process = $this->processes[$process];
        }
        if (is_object($process)) {
            $process = [ /* should be more here*/
                         'process' => $process, ];
        }
        if (is_callable($process)) {
            $process = [ 'process' => $process, 'title' => 'Closure' ];
        }


        if (isset($process['restart']) && $process['restart'] !== false) {
            if ($process['restart'] === 0) {
                $shouldRestart = true;
            }
            $process['restart_times'] = isset($process['restart_times']) ? $process['restart_times'] + 1 : 1;
            if ($process['restart_times'] === $process['restart']) {
                XConsoleEvent::dispatch('We already restarted process max times' . $process['title']);

                return false;
            }
        }
        if ($shouldRestart) {
            $process['process']->stop();
            $process['process']->restart();
            XConsoleEvent::dispatch("Restarted:" . $process['title']);
        }

        return $process['process']->isRunning();
    }

    /**
     * @param callable|null $loopCallback
     * @return void
     */
    public function run(callable $loopCallback = null): void
    {
        $this->each($this->processes, function ($processItem, $processIndex) use ($loopCallback) {
            $process = $this->processes[$processIndex]['process'];

            $this->runningProcesses++;
            $this->processes[$processIndex]['state'] = $process?->getStatus() ?? 'new';
            if ($process === null) {

                XConsoleEvent::dispatch("process was null! index:" . $processIndex);

                return;
            }
            /** @var Process $process */
            $process->start(function ($type, $message) use ($process, $processIndex, $processItem, $loopCallback) {
                $this->processes[$processIndex]['last_mtime'] = $process->getLastOutputTime();
                XConsoleEvent::dispatch('Recieved data:' . $type . ', data:' . $message);
                $this->processes[$processIndex]['pid'] = $process->getPid();

                $this->dumpProcesses();
                if ($type === Process::ERR || $type === Process::OUT) {
                    $this->processes[$processIndex]['last_sign'] = microtime();
                    $this->processes[$processIndex]['stderr']    .= "\n" . $process->getErrorOutput();
                    $this->processes[$processIndex]['stdout']    .= "\n" . $process->getOutput();
                }

                if ($loopCallback !== null) {
                    $loopCallback($type, $message, $process);
                }
                if ($process->getExitCode() !== null) {
                    $this->processes[$processIndex]['exitcode'] = $process->getExitCode();
                    $this->processes[$processIndex]['state']    = $process->getStatus();
                    if ($process->isTerminated()) {
                        $this->runningProcesses--;
                        XConsoleEvent::dispatch('Terminated: ' . $processItem['title'] . ", processes: " . $this->runningProcesses);
                        if ($this->runningProcesses === 0) {
                            $this->end();
                        }
                    }
                }
            });

        });
    }

    /**
     * @param string $msg
     * @return void
     */
    private function end(string $msg = 'All processes ended'): void
    {

        if (!is_callable($this->afterEndedCallback)) {
            $this->afterEndedCallback($msg);
        }
        XConsoleEvent::dispatch($msg);
    }

    /**
     * runs all processes in array and returns array with result
     * @param array $processes
     * @return array
     */
    public function runOnceAndWait(array $processes): array
    {
        $output = [];
#        $this->each($processes, function ($item) use (&$output){
        foreach ($processes as $item) {
            /** @var $item Process */
            $item->run(function ($type, $msg) use (&$output, $item) {
                XConsoleEvent::dispatch("ran: " . $item->getCommandLine() . ",pid:" . $item->getPid());
                $output[] = [ $type => $msg ];
            });
        }

        return $output;
    }

    /*
        public function printstats()
        {
            $stats = [];
            foreach ($this->processes as $process) {
                foreach ($process as $key => $string) {
                    if (is_string($string)) {
                        $stats[$key] = $string;
                    }
                }
            }

            return $stats;
        }
    */

    /**
     * @param Closure $afterEndedCallback
     * @return $this
     */
    public function setAfterEndedCallback(Closure $afterEndedCallback): static
    {
        $this->afterEndedCallback = $afterEndedCallback;

        return $this;
    }


}
