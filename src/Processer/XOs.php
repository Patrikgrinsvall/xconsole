<?php

namespace PatrikGrinsvall\XConsole\Processer;

class XOs
{
    static private $i;
    private        $os;
    private        $php_path;

    public function __construct($os)
    {
        self::$i     = new $this;
        self::$i->os = $os;
    }

    static public function php_path()
    {
        if (empty(self::$php_path)) get_php();


    }

    public function get_os()
    {
        print_r($_SERVER);
        #if ($_SERVER)
    }

}