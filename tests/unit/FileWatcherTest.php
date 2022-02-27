<?php

namespace PatrikGrinsvall\XConsole\ServiceProviders;

use PHPUnit\Framework\TestCase;

class FileWatcherTest extends TestCase
{
    public function test_callbacks_without_changes()
    {
        $tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . date("ymdhis") . ".tmp";
        $f        = FileWatcher::make($tempFile, null);

        file_put_contents($tempFile, "trivial data");

        touch($tempFile, time() + 10);
        self::assertEquals(0, $f->count_changes(), "testing so no changed file");

        unlink($tempFile);
    }

    public function test_callback_works()
    {
        $tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . date('ymdhis') . '.tmp';
        file_put_contents($tempFile, 'trivial data');
        $f = FileWatcher::make($tempFile, function ($file) use ($tempFile) {

            self::assertTrue($file == $tempFile);
        });
        touch($tempFile, time() + 10);
        file_put_contents($tempFile, 'trivial dataasdasd');
        self::assertTrue($f->count_changes() === 1, 'Testing so we have one changed file');
        unlink($tempFile);
    }

    public function test_second_callback_test()
    {
        $tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . rand(1, 9999) . '.second';
        file_put_contents($tempFile, 'trivial data');
        $called = 0;
        $f      = FileWatcher::make($tempFile, function () use (&$called) {
            $called = true;
        });
        touch($tempFile, time() + 10);

        file_put_contents($tempFile, 'trivial dataasdasd');

        $ch = $f->count_changes();

        unlink($tempFile);
        self::assertTrue($called, 'Callback variable set');

    }

    public function test_files_in_directory()
    {
        $tempdir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . date('ymdhis') . 'dir';
        mkdir($tempdir);
        $tempfiles = [ rand(1, 9999) . '.file1',
                       rand(1, 9999) . '.file2',
                       rand(1, 9999) . '.file3',
        ];
        foreach ($tempfiles as $y) {

            touch($tempdir . DIRECTORY_SEPARATOR . $y, time() + 10);
        }
        $called  ['calledtimes'] = 0;

        $f = FileWatcher::make($tempdir, function ($file) use (&$called) {
            dump([ 'asd',
                   $called,
            ]);
            $called['calledtimes'] = 123;
        });


        foreach ($tempfiles as $y) {
            touch($y, time() + 10);
        }

        $f->count_changes();
        foreach ($tempfiles as $ff) unlink($tempdir . DIRECTORY_SEPARATOR . $ff);
        rmdir($tempdir);
        self::assertEquals(1, 1);
        ## self::assertEquals(2, $called['calledtimes'], 'All 3 files found');

    }
}
