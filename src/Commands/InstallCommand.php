<?php

namespace PatrikGrinsvall\XConsole\Commands;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PatrikGrinsvall\XConsole\Commands\BaseCommands\LaravelBaseCommand;
use Symfony\Component\Process\Process;

class InstallCommand extends LaravelBaseCommand
{


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'x:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installation command for z package';


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (!$this->check_requirements()) return $this::FAILURE;


        $this->info('----- FRESH INSTALLATION -----');
        $this->installHelpers();
        $this->installDatabase();
        $this->call('db:wipe');
        $this->call('migrate', [ '--seed' => true ]);
        $this->call('x:clean');

        return 0;
    }

    public function check_requirements()
    {

        return config('database.connections.mysql.database', 'not-set') !== 'not-set';
    }

    public function installHelpers()
    {
        if (!File::exists(base_path('z.bat'))) {
            File::replace(base_path('z.bat'), 'php artisan %*');
        }
        if (!File::exists(base_path('z.sh'))) {
            File::replace(base_path('z.sh'), '#!/bin/bash \n' . 'php artisan "$@";');
            #$this->call("chmod +x z.sh");
        }
    }

    public function installDatabase()
    {
        if ($this->ask('Do you want to create a database at: ' . config('database.connections.mysql.host') . ', named: ' . config('database.connections.mysql.database') . '(Y/n)', 'Y')) {
            if (!blank(config('database.connections.mysql.password'))) {
                $pw = ' -p' . config('database.connections.mysql.password');
            } else $pw = '';
            $cmd = 'mysql -u' . config('database.connections.mysql.username') . $pw . ' -h' . config('database.connections.mysql.host') . " -e 'create database if not exists " . config('database.connections.mysql.database') . ";'";

            Process::fromShellCommandline($cmd)->run(function ($type, $out2) use ($cmd) {

                if ($type === "out") {
                    $this->suprise("STDOUT: " . substr($out2, 0, 100));
                } else $this->error("STDERR: " . $out2);

            });
        }
    }

    public function printhelp()
    {
        $file   = File::lines(__DIR__ . '/../../../README.md');
        $header = sprintf("\n+<fg=blue>%s</>+\n", Str::padBoth(' +++ ', 85, '-'));

        $code    = false;
        $message = "";
        foreach ($file as $key => $f) {
            $end   = ($key == count($file) - 1) ? $header : '';
            $start = ($key == 0) ? $header : '';

            if (strpos($f, '```') !== false) {
                $code = !$code;
            }
            if ($code) {
                $message .= (sprintf('%s<fg=green;bg=black>| <fg=black;bg=bright-cyan>%s</>|</>%s' . "\n", $start, Str::padRight(trim(str_replace('```', '', $f)), 85, ' '), $end));
            } elseif (strpos($f, '#') !== false) {
                $message .= (sprintf('%s<fg=green;bg=black>| <fg=white;bg=black;options=bold>%s</>|</>%s' . "\n", $start, Str::padBoth(trim(str_replace('```', '', $f)), 85, ' '), $end));
            } else {
                $message .= (sprintf('%s<fg=green;bg=black;options=bold>| <fg=blue;bg=black>%s</>|</>%s' . "\n", $start, Str::padRight(trim(str_replace('```', '', $f)), 85, ' '), $end));
            }
        }
        $this->line($message);

        return $message;

    }
}
