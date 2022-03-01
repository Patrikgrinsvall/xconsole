<?php
declare( strict_types=1 );

namespace PatrikGrinsvall\XConsole\ServiceProviders;

use Illuminate\Support\ServiceProvider;
use PatrikGrinsvall\XConsole\Commands\CleanCommand;
use PatrikGrinsvall\XConsole\Commands\HelpCommand;
use PatrikGrinsvall\XConsole\Commands\InstallCommand;
use PatrikGrinsvall\XConsole\Commands\RecordCommand;
use PatrikGrinsvall\XConsole\Commands\SrvCommand;
use PatrikGrinsvall\XConsole\Events\XConsoleEvent;


/**
 * This package gives some more colors to default laravel commands and allows for customization of artisan serve
 *
 * @author Nuno Maduro <enunomaduro@gmail.com>
 */
class XConsoleServiceProvider extends ServiceProvider
{

    public function register()
    {
        if ( $this->app->runningInConsole() ) {
            /*
             * WIP
                        Artisan::command('x:tag', function () {
                            ProcessRunner::make(function () {
                                $composerFilename = dirname(__FILE__) . DIRECTORY_SEPARATOR . '/../../composer.json';
                                $composerJson     = json_decode(file_get_contents($composerFilename), true);
                                if (array_key_exists('version', $composerJson)) {
                                    $version = '0.0.' . (int)Str::afterLast('.', $composerJson['version']) + 1;
                                }
                                File::copy($composerFilename, $composerFilename . date("his") . ".bak");
                                $data = json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                                file_put_contents($composerFilename, $data);
                            })->run();
                        });
            */
            $this->commands([ 'x:srv' => SrvCommand::class ]);
            $this->commands([ 'x:help' => HelpCommand::class ]);
            $this->commands([ 'x:clean' => CleanCommand::class ]);
            $this->commands([ 'x:install' => InstallCommand::class ]);
            $this->commands([ 'x:record' => RecordCommand::class ]);
            $this->app->register(ProcessRunner::class);
            $this->app->register(FileWatcher::class)
        } else {
            logger("not running in con");
        }


    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        XConsoleEvent::dispatch("Booting Xconsole");
    }
}
