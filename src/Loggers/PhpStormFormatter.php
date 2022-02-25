<?php

namespace PatrikGrinsvall\XConsole\Loggers;

use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;

class PhpStormFormatter implements FormatterInterface
{


    public function __invoke($logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new LineFormatter("%datetime% | %channel% | %level_name% | %message% | %context% \n", "ymd h:i:s"));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function formatBatch(array $records)
    {
        foreach ($records as $key => $record) {
            $records[$key] = $this->format($record);
        }

        return $records;
    }

    public function format(array $record)
    {
        return $this->normalize($record);
    }

    public function normalize(array $record)
    {
        return implode(" |p| ", $record);
    }
}