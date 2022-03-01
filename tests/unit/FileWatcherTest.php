<?php

namespace PatrikGrinsvall\XConsole\Tests;

use PatrikGrinsvall\XConsole\ServiceProviders\FileWatcher;
use PHPUnit\Framework\TestCase;

class FileWatcherTest extends TestCase
{
    public function test_callbacks_without_changes()
    {
<<<<<<< HEAD
        $tempFile = sys_get_temp_dir() . "/a" . date("ymdhis") . ".tmp";
        $f        = FileWatcher::make($tempFile, null);

        file_put_contents($tempFile, "trivial data");

=======
        $tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . date("ymdhis") . ".tmp";
>>>>>>> 55979e9653c7a063a3805307043cbc4922343eae
        touch($tempFile, time() + 10);
        $f = FileWatcher::make($tempFile, null);
        $f->reset();
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

        $f = FileWatcher::make($tempFile, function () use (&$called) {
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
        $tempdir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . date('ymdhis') . 'dir/';
        mkdir($tempdir);
<<<<<<< HEAD
        $tempfiles = [ $tempdir . rand(1, 9999) . '.file1', $tempdir . rand(1, 9999) . '.file2', $tempdir . rand(1, 9999) . '.file3', ];
        foreach ( $tempfiles as $y ) {

            file_put_contents($y, time() + 10);
        }
        $called  [ 'calledtimes' ] = 0;

        $f = FileWatcher::make($tempdir, function ($file) use (&$called) {
            $called[ 'calledtimes' ] = 123;
        });


        foreach ( $tempfiles as $y ) {
            file_put_contents($y, time() + 10);
        }

        $f->count_changes();
        foreach ( $tempfiles as $ff ) unlink($tempdir . DIRECTORY_SEPARATOR . $ff);
=======
        $tempfiles = [ $tempdir . DIRECTORY_SEPARATOR . rand(1, 9999) . '.file1',
                       $tempdir . DIRECTORY_SEPARATOR . rand(1, 9999) . '.file2',
                       $tempdir . DIRECTORY_SEPARATOR . rand(1, 9999) . '.file3' ];
        foreach ($tempfiles as $tempfile) {
            file_put_contents($tempfile, random_bytes(10));
        }


        $f = FileWatcher::make();
        $f->reset();
        $f->grace(1);
        self::assertEmpty($f->get_watched(), "Test so it works to remove all watched files");
        $called = 0;
        $f->add($tempdir, function () use (&$called) {
            $called = 123;
        });
        self::assertIsArray($f->get_watched(), 'Test so it works to remove all watched files');
        foreach ($tempfiles as $tempfile) {
            file_put_contents($tempfile, "another data set", FILE_APPEND);
        }

        sleep(2);

        #self::assertIsArray($f->get_changes(), 'tests so files arrays are equal');
        self::assertEquals(2, $f->count_changes(), "test so we detected changes");


        foreach ($tempfiles as $ff) unlink($ff);
>>>>>>> 55979e9653c7a063a3805307043cbc4922343eae
        rmdir($tempdir);
        ## self::assertEquals(2, $called['calledtimes'], 'All 3 files found');

    }

    public function setUp(): void
    {
        // todo add logic
        parent::setUp();
    }
}
