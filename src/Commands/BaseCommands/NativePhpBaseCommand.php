<?php

namespace PatrikGrinsvall\XConsole\Commands\BaseCommands;

trait NativePhpBaseCommand
{
    public \Closure $outputHandler;

    public function setOutputHandler(callable $out)
    {
        $this->outputHandler = $out;
    }
}