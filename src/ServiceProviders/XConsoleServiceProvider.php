<?php
declare(strict_types=1);

namespace PatrikGrinsvall\XConsole\ServiceProviders;

use Illuminate\Support\ServiceProvider;
use PatrikGrinsvall\XConsole\Events\XConsoleEvent;


/**
 * This package gives some more colors to default laravel commands and allows for customization of artisan serve
 *
 * @author Nuno Maduro <enunomaduro@gmail.com>
 */
class XConsoleServiceProvider extends ServiceProvider
{


    private $registred = false;

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        if (!$this->registred) {
            XConsoleEvent::dispatch("Registrering xconsole stuff");

            $this->register();
        } else {
            XConsoleEvent::dispatch("boot was runned after already registred? did we crash");
        }
    }

    /***
     * @return void
     */
    public function register()
    {
        (new XConsoleLaravelServiceProvider)->register();

    }
}
