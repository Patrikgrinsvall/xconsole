<?php

namespace PatrikGrinsvall\XConsole\Tests;


use PHPUnit\Framework\TestCase;

class NativePhpHelpersTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        require_once(__DIR__ . "/../helpers/native-php-helpers.php");
    }

    public function testGetOs()
    {
        $res = get_os();
    }

    public function testGetPHP()
    {
        $res = get_php();

        fwrite(STDERR, print_r($res));
    }
}
