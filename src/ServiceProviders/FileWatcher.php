<?php

namespace PatrikGrinsvall\XConsole\ServiceProviders;

use Exception;
use Illuminate\Support\ServiceProvider;
use Throwable;

class FileWatcher extends ServiceProvider
{

    private static self $i;
    public int          $graceTime;                                // seconds to at least wait before checking file, to save disk and maybe cpu
    public bool         $use_exceptions = false;                   // whethere or not to throw exceptions
    public null|object  $event          = null;                    // an event to dispatch or null / false if no event
    protected array     $paths;                                    // internal metadata, array with paths and their modified time
    private array       $excluded       = [ ".",
                                            "..",
                                            ".git",
    ];
    private int         $lastCheckTime  = 0;

    public function __construct($paths = null, $gracetime = 0)
    {
        $this->gracetime = $gracetime;
        if ($paths != null) $this->add($paths);
    }

    /**
     * Adds the path to a file or directory and a callback to run in case any file in path is changed
     * since last iteration.
     *
     * @param string|array  $paths
     * @param callable|null $callback
     * @return $this
     * @throws Exception
     */
    public function add(string|array $paths, callable $callback = null): static
    {

        $pathsToAdd = is_string($paths) ? [ $paths ] : $paths;


        foreach ($pathsToAdd as $path) {

            foreach ($this->get_files($path) as $file) {

                $this->paths[$file] = [ 'path'       => $file,
                                        'last_mtime' => 0,
                                        'callback'   => $callback,
                ];
            }
        }

        return $this;
    }

    /**
     * returns an array with absolut filenames
     * @param string|null $path
     * @return array
     * @throws Exception
     * @todo move to flysystem to support remote watching
     */
    public function get_files(string|null $path): array
    {
        if (is_dir($path)) {

            $files = array_filter(scandir($path), function ($file) {
                if (in_array($file, $this->excluded)) {
                    return false;
                }
                if (is_file($file)) return true;

                return false;
            });
        } elseif (is_file($path)) {
            $files[] = $path;
        } elseif (is_null($path)) {
            $files = [];
        } else $files = [];

        return $files;
    }

    /**
     * Create an instance of filewatcher with optional callback to run if any files are changed.
     * @param string|null   $path     - File or directory to watch.
     * @param callable|null $callback - callback to run if files are changed in $path
     * @return FileWatcher - returns an instance, run update to check if files are changed since last iteration
     * @throws Exception
     */
    public static function make(string $path = null, callable $callback = null): static
    {

        if (!isset(self::$i)) {
            self::$i            = new static();
            self::$i->graceTime = 0;
        }


        #return self::$i->add = new static($path, $callback);
        return self::$i->add($path, $callback);
    }

    public function reset()
    {
        unset($this->paths);
        $this->paths = [];
    }

    /**
     * @return array
     */
    public function stats(): array
    {
        $output = [];
        foreach ($this->paths as $path) {
            $output = [ 'path'       => $path['path'],
                        'last_mtime' => date("ymd h:i:s", $path['last_mtime']),
            ];
        }

        return $output;
    }

    /**
     * @param array $files
     */
    public function watch(string|array ...$paths)
    {
        if (is_string($paths)) {
            $this->add([ $paths ]);
        } else {
            $this->add($paths);
        }

        return $this;

    }

    public function update()
    {
        $this->count_changes();

        return $this;
    }

    public function count_changes()
    {
        if ($this->grace()) {
            $this->throw_dispatch($this->grace(), 'RuntimeException');

            return false;
        }
        $changes = 0;

        foreach ($this->paths as $path) {

            $changes = $changes + $this->changed($path['path']);
        }


        return $changes;
    }

    /**
     * returns true if we are in a graceperiod
     * @param int $graceTime - Time before we can check filesystem again, default to 5
     * @return bool true if filesystem is resting, false if its ok to check it
     */
    public function grace(): bool
    {

        if ($this->lastCheckTime == 0) {
            $this->lastCheckTime = time();

            return true;
        }

        $time = time();
        if (!isset($this->graceTime)) $this->graceTime = 0;

        return ($time + $this->graceTime > $time);
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
        if (is_object($this->event) && method_exists($this->event, 'dispatch')) {
            $this->event::dispatch($message);
        }
    }

    /**
     * Returns false if path is not registred or not changed, true if changed since last check and
     * if callback for that path is registred when file is added, also runs the callback.
     *
     * @param string $path
     * @return int
     */
    public function changed(string $path)
    {
        if (!isset($this->paths[$path])) {
            return 0;
        }

        $last = $this->paths[$path]['last_mtime'];

        ##$this->throw_dispatch(file_exists($path) == false, "File not readable, " . $path);
        if (file_exists($path)) {
            clearstatcache(true, $path);
            $now = filemtime($path);
        } else return 1;

        if ($last != $now) {
            $this->paths[$path]['last_mtime'] = $now;
            $cb                               = $this->paths[$path]['callback'] ?? function ($item) {
                    return 0;
                };

            $cb($path);

            return 1;
        }

        return 0;

    }

    public function get_changes()
    {
        $changes = [];
        foreach ($this->paths as $path) {
            if ($this->changed($path['path'])) {
                $changes[] = $path['path'];
            }
        }

        return $changes;

    }
}