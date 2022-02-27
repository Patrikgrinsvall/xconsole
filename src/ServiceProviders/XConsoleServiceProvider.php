<?php
declare(strict_types=1);

namespace PatrikGrinsvall\XConsole\ServiceProviders;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use PatrikGrinsvall\XConsole\Commands\CleanCommand;
use PatrikGrinsvall\XConsole\Commands\HelpCommand;
use PatrikGrinsvall\XConsole\Commands\InstallCommand;
use PatrikGrinsvall\XConsole\Commands\SrvCommand;


/**
 * This package gives some more colors to default laravel commands and allows for customization of artisan serve
 *
 * @author Nuno Maduro <enunomaduro@gmail.com>
 */
class XConsoleServiceProvider extends ServiceProvider
{

    public function register()
    {


        if ($this->app->runningInConsole()) {
#            Artisan::add
            /*Artisan::command("x:tag", function () {
                ProcessRunner::make([ function () {
                    $composerFile = File::get(dirname(__FILE__) . DIRECTORY_SEPARATOR . "../composer.json");
                    if (array_key_exists('version', json_decode($composerFile))) {
                        $version = "0.0." . Str::afterLast(".", $composerFile['version']) + 1;
                    }

                    $data = json_encode($composerFile, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                    file_put_contents($composerFile, $data);
                },
                ], [ 'git',
                     'tag',
                ]);
            });*/
            log::debug('Registering service provider for presets');

            #$this->commands([ 'srv' => SrvCommand::class ]);
            $this->commands([ 'x:srv' => SrvCommand::class ]);
            $this->commands([ 'x' => HelpCommand::class ]);
            #$this->commands([ 'x:help' => HelpCommand::class ]);
            $this->commands([ 'x:clean' => CleanCommand::class ]);
            $this->commands([ 'x:install' => InstallCommand::class ]);


        } else {
            logger("not running in con");
        }


    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {

    }
}
