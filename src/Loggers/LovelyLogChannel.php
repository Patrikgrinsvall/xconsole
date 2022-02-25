<?php

namespace PatrikGrinsvall\XConsole\Loggers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Log\LogManager;
use Monolog\Logger;


class LovelyLogChannel extends LogManager
{

    /**
     * @param array $config
     *
     * @return Logger
     * @throws BindingResolutionException
     */
    public function __invoke(array $config): Logger
    {
        $this->dateFormat = "Y-m-d h:i:s";

        $handler = new LovelyLogHandler($config['level'] ?? Logger::DEBUG, $config['bubble'] ?? true, true, true);


        return new Logger('Loggers', [
            $this->prepareHandler($handler, $config),
        ]);
    }


}