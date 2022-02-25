<?php

namespace PatrikGrinsvall\XConsole\Commands;

use Illuminate\Console\Concerns\HasParameters;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PatrikGrinsvall\XConsole\Traits\HasTheme;
use Symfony\Component\Console\Command\Command;

/**
 *
 */
class HelpCommand extends Command
{
    use HasTheme;
    use HasParameters;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'x:help';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'various tasks related to this starter package';


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        #if ( !$this->check_requirements() ) return Command::FAILURE;
        /*
                $this->info('----- Output; README.md -----');
                $this->helptext();
                $this->info("----- FRESH INSTALLATION -----");
                $this->installHelpers();
                $this->installDatabase();
        */

        return 0;
    }

    /**
     * @return void
     */
    public function helptext()
    {
        $file   = File::lines(__DIR__ . '/../../../README.md');
        $header = sprintf("\n+<fg=blue>%s</>+\n", Str::padBoth(' +++ ', 80, '-'));

        $code = false;
        foreach ($file as $key => $f) {


            $end   = ($key == count($file) - 1) ? $header : '';
            $start = ($key == 0) ? $header : '';


            if (strpos($f, '```') !== false) {
                $code = !$code;
            }


            if ($code) {
                $this->stdout(sprintf("%s<fg=green;bg=black>| <fg=black;bg=bright-cyan>%s</>|</>%s", $start, Str::padRight(trim(str_replace('```', '', $f)), 80, ' '), $end));
            } elseif (strpos($f, '#') !== false) {

                $this->stdout(sprintf("%s<fg=green;bg=black>| <fg=white;bg=black;options=bold>%s</>|</>%s", $start, Str::padBoth(trim($f), 80, ' '), $end));
            } else {
                $this->stdout(sprintf("%s<fg=green;bg=black;options=bold>| <fg=blue;bg=black>%s</>|</>%s", $start, Str::padRight(trim($f), 80, ' '), $end));
            }


        }
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
        if ($this->ask("Do you want to create a database at: " . config('database.connections.mysql.host') . ", named: " . config('database.connections.mysql.database') . '(Y/n)', 'Y')) {
            if (!blank(config('database.connections.mysql.password'))) {
                $pw = " -p" . config('database.connections.mysql.password');
            } else $pw = "";
            $cmd = "mysql -u" . config('database.connections.mysql.username') . $pw . " -h" . config('database.connections.mysql.host') . " 'create database if not exists " . config('database.connections.mysql.database') . ";'";

            /*Process::fromShellCommandline($cmd)->run(function ($out) {
                $this->suprise($out);
            });*/
        }
    }


    public function getName(): ?string
    {
        return "starter";
    }

    public function check_requirements()
    {

        if (config('database.connections.mysql.database', 'not-set') === 'not-set') {
            return false;
        }

        return true;
    }
}
