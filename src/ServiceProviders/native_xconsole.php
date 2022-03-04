<?php
/**
 * returns absolut path from the xconsole root directory
 * @param $file
 * @return string
 */
function xdir($file = '')
{
    $dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . $file;
    if (is_dir($dir)) return $dir;
    if (is_file($dir)) return dirname($file);

    return realpath($dir);
}

/**
 * Main
 */

// require all deps
/*$files = [ xdir('ConfigParser.php'),
           xdir('Processer/FileWatcher.php'),
           xdir('Processer/ProcessRunner.php'),
           xdir('Processer/XOs.php'),


];
foreach ($files as $file) {
    if (file_exists($file)) require_once($file);
}
*/