<?php
/**
 * @param $basedir
 * @return void
 */
function repair_cache_directories($basedir = null)
{
    $dirs = [
        'storage\app',
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
        'storage\logs',
    ];
    if ( !file_exists($basedir) ) throw new  Error("Missing base directory");
    foreach ( $dirs as $dir ) {
        $basedir = rtrim($basedir, "/\\");
        $mkdir = $basedir . '/' . $dir;

        if ( !is_dir($mkdir) && !mkdir($mkdir) ) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $mkdir));
        }
        file_put_contents($mkdir . '/.gitignore', "*\\n!.gitignore");
    }
}
