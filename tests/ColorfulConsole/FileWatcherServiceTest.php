<?php

namespace PatrikGrinsvall\XConsole\ServiceProviders;

use PHPUnit\Framework\TestCase;

class FileWatcherServiceTest extends TestCase
{
    public function test_callbacks_without_changes()
    {
        $tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . date("ymdhis") . ".tmp";
        file_put_contents($tempFile, "trivial data");
        $f = FileWatcher::make($tempFile);
        self::assertTrue($f->update() === 0, "testing so no changed file");

        unlink($tempFile);
    }

    public function test_callback_works()
    {
        $tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . date('ymdhis') . '.tmp';
        file_put_contents($tempFile, 'trivial data');
        $f = FileWatcherService::make($tempFile, function ($file) use ($tempFile) {

            self::assertTrue($file == $tempFile);
        });
        sleep(2);
        file_put_contents($tempFile, 'trivial dataasdasd');
        self::assertTrue($f->update() === 1, 'Testing so we have one changed file');
        unlink($tempFile);
    }


}
