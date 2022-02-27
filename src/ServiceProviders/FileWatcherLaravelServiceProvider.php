<?php

namespace PatrikGrinsvall\XConsole\ServiceProviders;

use Illuminate\Support\ServiceProvider;

class FileWatcherLaravelServiceProvider extends ServiceProvider
{

    public function __debugInfo(): ?array
    {
        error_log("debuginfo");

        return "debug";
    }

    public function provides(): array
    {
        return [ FileWatcher::class ];
    }
}