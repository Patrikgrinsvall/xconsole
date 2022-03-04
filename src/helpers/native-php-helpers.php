<?php

use PatrikGrinsvall\XConsole\Processer\ProcessRunner;


if (!function_exists('get_php')) {
    function get_php()
    {

        $cmds      = "";
        $process   = ProcessRunner::make([ 'nix' => [ 'which', 'php' ], 'cygwin' => [ 'where', 'php' ], 'windows' => [ 'command', 'php' ] ]);
        $processes = [];
        $result    = $process->run(function ($type, $msg) use (&$processes) {
            $processes[] = $msg;
        });

        return $result;
    }
}
if (!function_exists('get_os')) {
    function get_os()
    {
        return "win";
        $cmds = [ 'nix' => [ 'which', 'php' ], 'cygwin' => [ 'where', 'php' ], 'windows' => [ 'php' ] ];
        foreach ($cmds as $cmd) {
            echo $cmd . "not supported";
        }
    }
}
