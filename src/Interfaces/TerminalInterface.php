<?php

namespace src\Interfaces;
/**
 * Interface for support between different terminal emulators
 */
interface TerminalInterface
{
    /**
     *
     * @param string $jailRoot  - Default to be jailed to 4 subdirectories below, this should be one directory above the vendor folder as a default.
     * @param string $title     - If we need to display the name of this terminal somewhere, this is the title used.
     * @return mixed
     */
    public function register(string $jailRoot = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..',
                             string $cwd = "",
                             string $title="Local Development Server",
                             array $env = null, );
    public function boot(string $cwd = "");
}