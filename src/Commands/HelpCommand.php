<?php

namespace PatrikGrinsvall\XConsole\Commands;

use Illuminate\Console\Concerns\HasParameters;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PatrikGrinsvall\XConsole\Traits\HasTheme;

/**
 *
 */
class HelpCommand extends XCommand
{
    use HasTheme;
    use HasParameters;

    public    $signature = 'x:help';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name      = 'x:help';
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

        $this->info('----- Output; README.md -----');
        $this->printhelp();

        return 0;
    }

    public function printhelp()
    {
        $file   = File::lines(__DIR__ . '/../../README.md');
        $header = sprintf("\n+<fg=blue>%s</>+\n", Str::padBoth(' +++ ', 85, '-'));

        $code    = false;
        $message = '';
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
