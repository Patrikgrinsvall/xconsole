<?php

namespace PatrikGrinsvall\XConsole\Contracts;

interface RunnableProcess
{
    public function __construct(string $label, array $process);


}