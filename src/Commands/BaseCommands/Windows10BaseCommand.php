<?php

namespace PatrikGrinsvall\XConsole\Commands\BaseCommands;

trait Windows10BaseCommand
{
    public  $outputHandler;
    private $controlHandler;

    public function beforeBoot()
    {
        $this->outputHandler = function ($message) {
            error_log('default out :' . print_r($message, 1));
        };

    }


    public function setControlHandler(?array $controlhandler = null)
    {
        //@formatter:off
        $this->controlHandler = $controlhandler ??
            (function_exists('sapi_windows_set_ctrl_handler') ?
                [ Windows10BaseCommand::class, 'ctrl_handler' ] :
                function ($event) {

                });
        //@formatter:on
    }


    function ctrl_handler(int $event)
    {
        switch ($event) {
            case PHP_WINDOWS_EVENT_CTRL_C:
                $this->outputHandler("You pressed ctrl + c");
                break;
            case PHP_WINDOWS_EVENT_CTRL_BREAK:
                $this->outputHandler('You pressed ctrl + break');
                break;
            default:
                $this->outputHandler('You pressed ' . $event);
                break;
        }
    }

}