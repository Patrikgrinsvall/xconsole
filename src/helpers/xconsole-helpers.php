<?php
/**
 * @param $basedir
 * @return void
 */
function repair_cache_directories($basedir = null)
{
    $dirs = [ 'storage\app',
              'storage\app\public',
              'storage\app',
              'storage\framework ',
              'storage\framework\cache',
              'storage\framework\cache\data',
              'storage\framework\cache',
              'storage\framework',
              'storage\framework\sessions',
              'storage\framework',
              'storage\framework\testing',
              'storage\framework',
              'storage\framework\views',
              'storage\framework',
              'storage\logs', ];
    if (!file_exists($basedir)) throw new  Error("Missing base directory");
    foreach ($dirs as $dir) {
        $basedir = rtrim($basedir, "/\\");
        $mkdir   = $basedir . '/' . $dir;

        if (!is_dir($mkdir) && !mkdir($mkdir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $mkdir));
        }
        file_put_contents($mkdir . '/.gitignore', "*\\n!.gitignore");
    }
}

if (!function_exists('public_dir')) {
    function public_dir($dir = "")
    {
        if (empty($dir)) {
            $dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "public";
        }

        return $dir;
    }
}
if (!function_exists('basepath')) {
    function base_path($path = "")
    {
        if (empty($path)) {
            $path = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "..");
        }

        return $path;
    }
}
if (!function_exists('dump')) {
    function dump($msg = '')
    {

        error_log(print_r($msg, 1));
    }
}