<?php

namespace PatrikGrinsvall\XConsole\ServiceProviders;

class FileWatcher
{
    private static  $i = null;
    protected array $paths;
    protected array $callables;

    public static function make(string $path = null, callable $callback = null)
    {
        if (self::$i !== null) {
            if ($path != null) {
                self::$i->add($path, $callback);
            }

            return self::$i;
        }
        self::$i = new static();
        self::$i->add($path, $callback);

        return self::$i;
    }

    public function add(string $path, ?callable $callback = null)
    {
        $stat = stat($path);

        $last_mtime         = $stat['mtime'];
        $this->paths[$path] = [
            'path'       => $path,
            'last_mtime' => $last_mtime,
            'callback'   => $callback,
        ];

        return $this;
    }

    /**
     * @return array
     */
    public function stats(): array
    {
        return $this->paths;
    }

    /**
     * @param array $files
     */
    public function watch(string ...$path)
    {
        foreach ($path as $item) $this->paths[] = $path;

        return $this;

    }

    public function get_changes()
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
     * @param string $path
     * @return int
     */
    public function changed(string $path)
    {
        if (!isset($this->paths[$path])) {

            return 0;
        }
        $last = $this->paths[$path]['last_mtime'];
        clearstatcache(false, $path);
        $stat = stat($path);
        $now  = $stat['mtime'];

        if ($last != $now) {
            $this->paths[$path]['last_mtime'] = $now;
            $cb                               = $this->paths[$path]['callback'] ?? function ($item) {
                    return false;
                };

            $cb($path);

            return 1;
        }

        return 0;

    }

    public function count_changes()
    {
        $changes = 0;
        foreach ($this->paths as $path) {
            $changes = $changes + $this->changed($path['path']);

        }


        return $changes;

    }
}