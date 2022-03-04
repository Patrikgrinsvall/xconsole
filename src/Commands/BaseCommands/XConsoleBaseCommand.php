<?php

namespace PatrikGrinsvall\XConsole\Commands\BaseCommands;

use Closure;

class NativeXConsoleBaseCommand
{
    public Closure $outputHandler;

    public function setOutputHandler(callable $out)
    {
        $this->outputHandler = $out;
    }
}