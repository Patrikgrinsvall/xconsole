<?php

namespace PatrikGrinsvall\XConsole\ServiceProviders;

use PatrikGrinsvall\XConsole\Events\XConsoleEvent;
use PatrikGrinsvall\XConsole\Traits\HasTheme;
use Symfony\Component\Process\Process;

class ProcessRunner
{
    use HasTheme;

    /**
     * @var null
     */
    private static $i;
    public array   $processes;
    public         $app;
    public array   $processTemplates = [ 'process_index' => [ // title for presentation
                                                              'title'      => 'echo', 'category' => 'shell', // working dir
                                                              'cwd'        => ".", // path to executable
                                                              'executable' => 'echo', // contains out
                                                              'stdout'     => "", // contains stderr
                                                              'stderr'     => "", // exitcode
                                                              'code'       => null, // running, exited, suspended, errored, new
                                                              'state'      => null, // self explained
                                                              'pid'        => null, // this is the actually runned cmd after process started
                                                              'cmd'        => '', 'timeout' => 600, // paramters before process started, one entry per parameter, i think in reality one parameter is one space
                                                              'parameters' => [ 'this', 'is', 'parameters', ], ], ];
    public         $runningProcesses = 0;

    /**
     * @factory
     * @return ProcessRunner
     */
    public static function make(callable|Process|null|string $process = null): ProcessRunner
    {
        $cls = static::class;

        if (!isset(self::$i[$cls])) {
            self::$i[$cls] = new static();
        }

        if (isset($process)) {
            // an array with processes
            if (is_array($process) && is_array($process[0])) {
                error_log('Process is an array containing many commands');

            }
            if (is_array($process) && is_string($process[0])) {
                error_log("Process is an array containing single command");
                $process = [ $process ];
            }
            if (is_string($process)) {
                error_log('Process is single command string');
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

    public function add(string $label = null, object|array|string $process = null, string $cwd = ".", $timeout = 600)
    {
        $i = 0;

        $title = $label ?? match (gettype($process)) {
                "array"  => $process[0],
                "string" => explode(" ", $process)[0],
                "object" => 'object'
            };
        while (isset($this->processes[$title])) $title = $label . '[' . $i++ . ']';
        if (is_callable($process)) {
            die("NOT SUPPORTED TO ADD CLOSURES");
            ##$newProcess = new PhpProcess('<?php $function=' . $process . '; $function();');

            error_log("NOT SUPPORTED");

        }
        if (is_string($process)) {
            $process    = explode(" ", $process);
            $newProcess = new Process($process, $cwd);
            #$this->processes[$title] = [ 'title' => $label, 'parameters' => [], 'stderr' => '', 'stdout' => '', 'state' => 'new', 'cwd' => $cwd, 'executable' => $process, 'timeout' => $timeout, 'process' => null, ];
        }
        if (is_array($process)) {
            $newProcess = new Process($process, $cwd);
        }
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
            'timeout'=>600
        ];
        // @formatter:on

        ##$this->processes[$title] = [ 'title' => $label, 'parameters' => $process, 'stderr' => '', 'stdout' => '', 'state' => 'new', 'cwd' => $cwd, 'executable' => $process[0], 'timeout' => $timeout, 'process' => null, ];

        XConsoleEvent::dispatch("Process added: " . $title);

        return $this;
    }

    public function stopAll()
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

    public function each(iterable $items, callable $callback)
    {
        foreach ($items as $processIndex => $process) {
            if (is_callable($callback)) {
                $callback($process, $processIndex);
            }
        }
    }

    public function restartAll()
    {
        if ($this->runningProcesses > 0) {
            XConsoleEvent::dispatch('Restarting all ');
            $this->each($this->processes, function ($process, $processIndex) {
                $process['process']->stop();
                $process['process']->restart();
                unset($this->processes[$processIndex]['process']);
            });
        }
    }

    public function run(callable $loopCallback = null): void
    {
        $this->each($this->processes, function ($processItem, $processIndex) use ($loopCallback) {
            $process = $this->processes[$processIndex]['process'];

            $this->runningProcesses++;
            $this->processes[$processIndex]['state'] = $process?->getStatus() ?? 'new';
            if ($process == null) {
                dump("was null", $processItem);
                XConsoleEvent::dispatch("process was null! index:" . $processIndex);

                return;
            }

            $process->start(function ($type, $message) use ($process, $processIndex, $processItem, $loopCallback) {
                if ($type == Process::ERR || $type == Process::OUT) {
                    $this->processes[$processIndex]['last_sign'] = microtime();
                    $this->processes[$processIndex]['stderr']    .= "\n" . $process->getErrorOutput();
                    $this->processes[$processIndex]['stdout']    .= "\n" . $process->getOutput();
                    XConsoleEvent::dispatch('Recieved data:' . $type . ', data:' . $message);

                } else {
                    XConsoleEvent::dispatch("Recieved unknown data:" . $type . ", data:" . $message);
                }
                if ($loopCallback !== null) $loopCallback($type, $message, $process);
                if ($process->getExitCode() !== null) {
                    $this->processes[$processIndex]['exitcode'] = $process->getExitCode();
                    $this->processes[$processIndex]['state']    = $process->getStatus();
                    if ($process->isTerminated()) {
                        $this->runningProcesses--;
                        XConsoleEvent::dispatch('Terminated: ' . $processItem['title'] . ", processes: " . $this->runningProcesses);
                        if ($this->runningProcesses == 0) {
                            $this->end();
                        }
                    }
                }
            });

        });
    }

    public function end($msg = 'All processes ended')
    {
        XConsoleEvent::dispatch($msg);
        error_log($msg);
    }


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


}
