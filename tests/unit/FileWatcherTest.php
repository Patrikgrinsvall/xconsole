<?php

namespace PatrikGrinsvall\XConsole\Tests;

use PatrikGrinsvall\XConsole\ServiceProviders\FileWatcher;
use PHPUnit\Framework\TestCase;

class FileWatcherTest extends TestCase
{
    public function test_callbacks_without_changes()
    {
        $tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . date("ymdhis") . ".tmp";
        touch($tempFile, time() + 10);
        $f = FileWatcher::make($tempFile, null);

        file_put_contents($tempFile, "trivial data");
        self::assertEquals(0, $f->count_changes(), "testing so no changed file");

        unlink($tempFile);
    }

    public function test_callback_works()
    {
        $tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . date('ymdhis') . '.tmp';
        file_put_contents($tempFile, 'trivial data');
        $f = FileWatcher::make($tempFile, function ($file) use ($tempFile) {
            self::assertTrue($file == $tempFile);
        }, 0);
        touch($tempFile, time() + 10);
        file_put_contents($tempFile, 'trivial dataasdasd');

        self::assertEquals(1, count($f->get_changes()), 'Testing so we have one changed file');
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
        $tempfiles = [ $tempdir . DIRECTORY_SEPARATOR . rand(1, 9999) . '.file1',
                       $tempdir . DIRECTORY_SEPARATOR . rand(1, 9999) . '.file2',
                       $tempdir . DIRECTORY_SEPARATOR . rand(1, 9999) . '.file3' ];
        foreach ($tempfiles as $tempfile) {
            touch($tempfile, time() + 10);
        }


        $f = FileWatcher::make();
        $f->reset();

        self::assertEmpty($f->get_watched(), "Test so it works to remove all watched files");
        $called = 0;
        $f->add($tempdir, function () use (&$called) {
            error_log("-:" . $called);
            $called = 123;
        });
        self::assertIsArray($f->get_watched(), 'Test so it works to remove all watched files');
        foreach ($tempfiles as $tempfile) {
            touch($tempfile, time() + 10);
            file_put_contents($tempfile, "another data set", FILE_APPEND);
        }
        error_log(print_R($f->get_changes(), 1));
        error_log(print_R($tempfiles, 1));
        $f->grace(0);
        self::assertEquals(2, $f->count_changes(), "test so we detected changes");

        self::assertCount(3, $f->get_changes(), "tests so files arrays are equal");
        foreach ($tempfiles as $ff) unlink($tempdir . DIRECTORY_SEPARATOR . $ff);
        rmdir($tempdir);
        ## self::assertEquals(2, $called['calledtimes'], 'All 3 files found');

    }

    public function setUp(): void
    {
        // todo add logic
        parent::setUp();
    }
}
