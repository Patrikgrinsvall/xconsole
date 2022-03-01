<?php

namespace PatrikGrinsvall\XConsole\ServiceProviders;

use Exception;
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

    public function __construct($paths = null, $gracetime = 0)
    {
<<<<<<< HEAD
        if ( self::$i !== null ) {
            if ( $path != null ) {
                self::$i->add($path, $callback);
            }

            return self::$i;
        }
        self::$i = new static();
        self::$i->add($path, $callback);

        return self::$i;
=======
        $this->gracetime = $gracetime;
        if ($paths != null) $this->add($paths);
>>>>>>> 55979e9653c7a063a3805307043cbc4922343eae
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
    public function add(string|array|null $paths, null|callable $callback = null): static
    {
<<<<<<< HEAD
        #$stat = stat($path);
        clearstatcache(true, realpath($path));
        $last_mtime           = filemtime(realpath($path));
        $this->paths[ $path ] = [ 'path' => $path, 'last_mtime' => $last_mtime, 'callback' => $callback, ];
=======
        if (is_null($paths)) return $this;
        $pathsToAdd = is_string($paths) ? [ $paths ] : $paths;
        foreach ($pathsToAdd as $path) {
            foreach ($this->get_files($path) as $file) {
                $this->paths[$file] = [ 'path' => $file, 'last_mtime' => 0, 'callback' => $callback, ];
            }
        }
>>>>>>> 55979e9653c7a063a3805307043cbc4922343eae

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
        $info  = new SplFileInfo($path);
        $files = match ($info->getType()) {
            "dir"  => array_filter(scandir($path), function ($file) {
                if (in_array($file, $this->excluded)) {
                    return false;
                }
                if (is_file($file)) return true;

                return false;
            }),
            "file" => [ $path ],

        };

        return $files;
    }

    /**
     * Create an instance of filewatcher with optional callback to run if any files are changed.
     * @param string|null   $path     - File or directory to watch.
     * @param callable|null $callback - callback to run if files are changed in $path
     * @return FileWatcher - returns an instance, run update to check if files are changed since last iteration
     * @throws Exception
     */
    public static function make(string|array $path = null, callable $callback = null): static
    {
        if (!isset(self::$i)) {
            self::$i            = new static();
            self::$i->graceTime = 0;
        }

        return self::$i->add($path, $callback);
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
            $output = [ 'path' => $path['path'], 'last_mtime' => date('ymd h:i:s', $path['last_mtime']), ];
        }

        return $output;
    }

    /**
     * @param array $files
     */
    public function watch(string|array ...$paths)
    {
<<<<<<< HEAD
        foreach ( $path as $item ) $this->paths[] = $path;
=======
        foreach ($paths as $path) {
            $this->add($path);
        }
>>>>>>> 55979e9653c7a063a3805307043cbc4922343eae

        return $this;

    }

    public function update()
    {
<<<<<<< HEAD
        $changes = null;
        foreach ( $this->paths as $path ) {
            if ( $this->changed($path[ 'path' ]) ) {
                $changes[] = $path[ 'path' ];
            }

        }
        if ( !isset($changes) || count($changes) == 0 ) return [];

        return count($changes) == 1 ? $changes[ 0 ] : implode("\n", $changes);
=======
        $this->count_changes();
>>>>>>> 55979e9653c7a063a3805307043cbc4922343eae

        return $this;
    }

    /**
     * count changes since last check
     * @return false|int|mixed
     * @throws Throwable
     */
    public function count_changes()
    {
        if ($this->grace()) {
            $this->throw_dispatch($this->grace(), 'RuntimeException, graceperiod', $this->grace());

            return 0;
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
    public function grace(int $time = 5): bool
    {

        if ($time != 5) $this->graceTime = $time;
        if ($this->lastCheckTime == 0) {
            $this->lastCheckTime = time();

            return false;
        }


        return ($this->graceTime + $this->lastCheckTime > time());
    }

    /**
     * @throws Throwable
     */
    public function throw_dispatch($condition, $message)
    {

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
<<<<<<< HEAD
        if ( !isset($this->paths[ $path ]) ) {

            return 0;
        }
        $last = $this->paths[ $path ][ 'last_mtime' ];
        clearstatcache(false, $path);
        $stat = stat($path);
        $now  = $stat[ 'mtime' ];

        if ( $last != $now ) {
            $this->paths[ $path ][ 'last_mtime' ] = $now;
            $cb                                   = $this->paths[ $path ][ 'callback' ] ?? function ($item) {
                    return false;
=======
        if (!isset($this->paths[$path])) {
            return 0;
        }

        $last = $this->paths[$path]['last_mtime'];

        if (file_exists($path)) {
            clearstatcache(true, $path);
            $now = filemtime($path);
        } else return 1;

        if ($last != $now) {
            $this->paths[$path]['last_mtime'] = $now;
            $cb                               = $this->paths[$path]['callback'] ?? function ($item) {
                    return 0;
>>>>>>> 55979e9653c7a063a3805307043cbc4922343eae
                };

            $cb($path);

            return 1;
        }

        return 0;

    }

    public function getGrace()
    {
<<<<<<< HEAD
        $changes = 0;
        foreach ( $this->paths as $path ) {
            $changes = $changes + $this->changed($path[ 'path' ]);
=======
        return $this->graceTime;
    }
>>>>>>> 55979e9653c7a063a3805307043cbc4922343eae

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