<?php

namespace PatrikGrinsvall\XConsole\Loggers;

use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Throwable;


class LovelyLogHandler extends AbstractProcessingHandler
{


    /**
     * @var FormatterInterface The formatter to use for the logs generated via handleBatch()
     */
    protected $batchFormatter;


    /**
     * @param HubInterface $hub
     * @param int          $level  The minimum logging level at which this handler will be triggered
     * @param bool         $bubble Whether the messages that are handled can bubble up the stack or not
     * @param bool         $reportExceptions
     * @param bool         $useFormattedMessage
     */
    public function __construct($level = Logger::DEBUG, bool $bubble = true, bool $reportExceptions = true, bool $useFormattedMessage = false)
    {
        parent::__construct($level, $bubble);
    }


    /**
     * {@inheritdoc}
     * @suppress PhanTypeMismatchArgument
     */
    protected function write(array $record): void
    {
        $exception   = $record['context']['exception'] ?? null;
        $isException = $exception instanceof Throwable;
        /** @noinspection ForgottenDebugOutputInspection */
        #dd("writing log from handler");

    }
}