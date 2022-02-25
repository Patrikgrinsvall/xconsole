<?php /** @noinspection PhpClassNamingConventionInspection */

namespace PatrikGrinsvall\XConsole\TrivialTheme;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Nova;

class TrivialThemeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Nova::booted(function () {
            Nova::theme(asset('/trivial-theme/theme.css'));
        });

        $this->publishes([
            __DIR__ . '/resources/css' => public_path('trivial-theme'),
        ], 'public');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
