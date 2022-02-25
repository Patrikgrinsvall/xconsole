<?php

namespace PatrikGrinsvall\XConsole\Loggers;

use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;

class XConsoleFormatter implements FormatterInterface
{
    private $logger;

    /**
     * @param Logger $logger
     * @return void
     */
    public function __invoke($logger)
    {
        $this->logger = $logger;

        foreach ($logger->getHandlers() as $handler) {

            $handler->setFormatter(new LineFormatter("%datetime% | %channel% | %level_name% | %message% | %context% \n"));
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
        return 'from fol console: ' . implode(" :<->: ", $record);
    }
}