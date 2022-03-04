<?php

namespace PatrikGrinsvall\XConsole\Processer;

use Exception;
use PatrikGrinsvall\XConsole\Events\XConsoleEvent;
use SplFileInfo;
use Throwable;

class FileWatcher
{

    private static self $i;
    public int          $graceTime;                                // seconds to at least wait before checking file, to save disk and maybe cpu
    public bool         $use_exceptions = false;                   // whethere or not to throw exceptions
    public null|object  $event          = null;                    // an event to dispatch or null / false if no event
    protected array     $paths;                                    // internal metadata, array with paths and their modified time
    private array       $excluded       = [ '.', '..', '.git', ];
    private int         $lastCheckTime  = 0;

    /*
        public function __construct($paths = null, $gracetime = 0)
        {

            if ( self::$i !== null ) {
                if ( $path != null ) {
                    self::$i->add($path, $callback);
                }

                return self::$i;
            }
            self::$i = new static();


            $this->gracetime = $gracetime;
            if ( $paths != null ) $this->add($paths);

            return self::$i;
        }
        */

    /**
     * Create an instance of filewatcher with optional callback to run if any files are changed.
     * REMOVES ALL OLD PATHS IF RUNNED A SECOND TIME!
     * @param string|array|null $path     - File or directory to watch.
     * @param callable|null     $callback - callback to run if files are changed in $path
     * @param int               $grace_time
     * @return FileWatcher - returns an instance, run update to check if files are changed since last iteration
     * @throws Exception|Throwable
     */
    public static function make(string|array $path = null, callable $callback = null, int $grace_time = 5): static
    {
        if (isset(self::$i) && !empty(self::$i->paths)) {
            foreach (self::$i->paths as $key => $p) {
                unset(self::$i->paths[$key]);
            }
            self::$i->paths = [];
        }
        if (!isset(self::$i)) {
            self::$i            = new static();
            self::$i->graceTime = 0;
        }

        return self::$i->add($path, $callback, $grace_time);
    }

    /**
     * Adds the path to a file or directory and a callback to run in case any file in path is changed
     * since last iteration.
     *
     * @param string|array|null $paths
     * @param callable|null     $callback   - function to call when file or files are changed, applies only to these files
     * @param int               $grace_time - in seconds, the time between checks of this file, longer value is nicer to disk but shorter is more precise
     * @return $this
     * @throws Throwable
     */
    public function add(string|array|null $paths, null|callable $callback = null, int $grace_time = 5): static
    {
        if (is_null($paths)) return $this;

        $pathsToAdd = is_string($paths) ? [ $paths ] : $paths;

        foreach ($pathsToAdd as $path) {
            if (!file_exists($path)) {
                $path = __DIR__ . DIRECTORY_SEPARATOR . $path;
                if (!file_exists($path)) {
                    $path = ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . $path;
                    if (!file_exists($path)) {
                        XConsoleEvent::dispatch("file not found:" . $path);
                        die();
                    }
                }
            }
            $filetype = is_file($path) ?? is_dir($path) ?? 'unknown';


            $this->throw_dispatch($filetype === 'unknown', "Unknown filetype cannot be added. Use try/catch around this statement to bypass");


            clearstatcache(false, $path);
            $files = $this->get_files($path);
            foreach ($files as $file) {
                $this->paths[$file] = [ 'path'       => $file,
                                        'last_mtime' => filemtime($file),
                                        'grace_time' => $grace_time,
                                        'callback'   => $callback, ];
            }
        }

        return $this;
    }

    /**
     * throws eception and dispatches registred event if condition is true
     *
     * @param mixed            $condition
     * @param string|Throwable $exception
     * @throws Throwable
     */
    public function throw_dispatch(mixed $condition, string|Throwable $exception)
    {
        if (is_string($exception)) $exception = new Exception($exception);
        throw_if($condition && $this->use_exceptions, $exception->getMessage());
        $this->dispatch_event_if($condition && $this->event !== null, $exception->getMessage());

    }

    /**
     * Dispatches an event if condition is true
     *
     * @param $condition
     * @param $message
     * @return void
     */
    public function dispatch_event_if($condition, $message)
    {
        if (is_object($this->event) && method_exists($this->event, 'dispatch')) {
            $this->event::dispatch($message);
        }
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
        $files = [];
        if (is_string($path)) {
            $info  = new SplFileInfo($path);
            $files = match ($info->getType()) {
                "dir"  => array_filter(scandir($path), function ($file) {
                    if (in_array($file, $this->excluded)) {
                        return false;
                    }
                    if (is_file($file) && is_readable($file) && !is_dir($file)) return true;

                    return false;
                }),
                "file" => [ $path ],

            };


        }

        return $files;

    }

    public function str2arr(...$string)
    {
        $args   = func_get_args();
        $return = [];
        if (is_array($string)) {
            $args = $string;
        }
        if (count($args) !== 1) {
            #$i =
            foreach ($string as $s) {
                $return[] = $s;
            }
        }
        if (is_string($string)) {
            $return[] = $string;
        }


    }

    /**
     * Same as remove function
     * @return void
     * @see FileWatcher->remove() string|array $path
     */
    public function unwatch(string|array $path)
    {
        $this->remove($path);

    }

    /**
     * Removes path(s) from internal watcher array
     * @param string|array $path
     * @return void
     */
    public function remove(string|array $path)
    {
        if (is_string($path)) $paths = [ $path ];


        foreach ($this->paths as $key => $value) {
            foreach ($paths as $arrkey) {
                if (isset($this->paths[$arrkey])) unset($this->paths[$arrkey]);
            }
        }
    }

    public function get_watched()
    {
        return $this->paths;
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
            $output = [ 'path' => $path['path'], 'last_unixtime' => $path['last_mtime'], 'last_mtime' => date('ymd h:i:s', $path['last_mtime']), ];
        }

        return $output;
    }

    /**
     * @param array $files
     */
    public function watch(string|array ...$paths)
    {


        foreach ($paths as $path) {
            $this->add($path);
        }

        return $this;

    }

    public function update()
    {

        $changes = null;
        foreach ($this->paths as $path) {
            if ($this->changed($path['path'])) {
                $changes[] = $path['path'];
            }

        }
        if (!isset($changes) || count($changes) == 0) return [];


        return count($changes) == 1 ? $changes[0] : implode("\n", $changes);

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
        $last                        = $this->paths[$path]['last_mtime'];
        $grace_time                  = $this->paths[$path]['grace_time'] ?? 0;
        $this->paths[$path]['debug'] = time();

        if ($last + $grace_time < time()) return 0;

        clearstatcache(true, $path);
        $stat = stat($path);
        $now  = $stat['mtime'];


        $last = $this->paths[$path]['last_mtime'];

        if (file_exists($path)) {
            clearstatcache(true, $path);

            $now = filemtime($path);
        } else {
            XConsoleEvent::dispatch("file was deleted");
            $cb($path);

            return 1;
        }

        if ($last != $now) {
            $this->paths[$path]['last_mtime'] = $now;
            if (!isset($this->paths[$path]['total_changes'])) $this->paths[$path]['total_changes'] = 0;
            $this->paths[$path]['total_changes']++;

            $cb = $this->paths[$path]['callback'] ?? function ($item) {
                    return false;

                };

            $cb($path);

            return 1;
        }

        return 0;

    }

    public function getGrace()
    {


        return $this->graceTime;

    }

    /**
     * Returns changed files as an array and resets changed files.
     * @return array
     */
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

    /**
     * count changes since last check
     * @return false|int|mixed
     * @throws Throwable
     */
    public function count_changes()
    {

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
    /*
    public function grace(string $path): bool
    {
        return false;
        if ($time != 5) $this->graceTime = $time;
        if ($this->lastCheckTime == 0) {
            $this->lastCheckTime = time();

            return false;
        }


        return ($this->graceTime + $this->lastCheckTime > time());
    }
    */
}