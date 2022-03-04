<?php

namespace PatrikGrinsvall\XConsole\Tests;

use PatrikGrinsvall\XConsole\Processer\FileWatcher;
use PHPUnit\Framework\TestCase;

class FileWatcherTest extends TestCase
{
    public  $tempDir;
    private $tempFiles;

    public function test_callbacks_without_changes()
    {

        $tempFile = $this->tempFiles[0];

        file_put_contents($tempFile, 'trivial data');
        $f = FileWatcher::make($tempFile, null);
        $f->reset();
        file_put_contents($tempFile, "trivial data");
        self::assertEquals(0, $f->count_changes(), "testing so no changed file");

    }

    public function testTestNullValues()
    {

        $f = FileWatcher::make();
        self::assertInstanceOf(FileWatcher::class, $f);

    }

    public function test_callback_works()
    {
        $this->setUp();

        $f = FileWatcher::make($this->tempFiles[0], static function ($file) {
            self::assertEquals($file, $this->tempFiles[0]);
        },                     0);

        #touch($this->tempFiles[0], time() + 10);
        file_put_contents($this->tempFiles[0], 'trivial dataasdasd', FILE_APPEND);

        $changed = $f->get_watched();
        sleep(1);

        self::assertEquals(1, count($changed), 'Testing so we have one changed file');

    }

    public function setUp(): void
    {
        parent::setUp();
        require_once(__DIR__ . "/../../src/ServiceProviders/native_xconsole.php");
        //(new XConsoleServiceProvider(App::loadEnvironmentFrom(".env.test")))->boot();

        $this->tempDir = xdir(__FILE__) . DIRECTORY_SEPARATOR . 'dir-' . date('ymdhis') . DIRECTORY_SEPARATOR;
        mkdir($this->tempDir);

        $this->tempFiles = [ $this->tempDir . 'test.file1', $this->tempDir . 'test.file2', $this->tempDir . 'test.file3', ];

        foreach ($this->tempFiles as $file) {
            clearstatcache(true, $file);
            file_put_contents($file, time() + 10, FILE_APPEND);
            touch($file, time() - 1, time() - 1);

        }
        sleep(1);
    }

    public function test_second_callback_test()
    {
        $this->setUp();
        $called = (string)"";
        file_put_contents($this->tempFiles[0], 'trivial dataasdasd');

        $f = FileWatcher::make($this->tempFiles[0], function () use (&$called) {
            die("asd");
            $called = "lalal";

            return $called;
        },                     0);


        clearstatcache(true, $this->tempFiles[0]);


        file_put_contents($this->tempFiles[0], 'trivial dataasdasd', FILE_APPEND);
        sleep(2);
        $ch = $f->count_changes();


        //self::assertEquals("lalal", $called);

    }

    public function test_files_in_directory()
    {
        $this->setUp();
        $called  ['calledtimes'] = 0;

        $f = FileWatcher::make($this->tempDir, function ($file) use (&$called) {
            $called['calledtimes'] .= random_int(1, 999) . "-";
        });
        sleep(1);

        foreach ($this->tempFiles as $y) {
            file_put_contents($y, time() + 10, FILE_APPEND);
        }
        $changedFiles = $f->get_changes();

        self::assertIsArray($changedFiles, 'testing so we have changed filed:' . print_r($changedFiles, 1));


        $this->tearDown();

    }

    public function tearDown(): void
    {
        parent::tearDown();
        foreach ($this->tempFiles as $tempFile) {
            unlink($tempFile);
        }
        rmdir($this->tempDir);
    }

    public function testTestGracePeriod()
    {
        /*
        $f = FileWatcher::make();
        $f->reset();
        $f->grace(1);
        self::assertEmpty($f->get_watched(), 'Test so it works to remove all watched files');
        $called = 0;
        $f->add($tempdir, function () use (&$called) {
            $called = 123;
        });
        self::assertIsArray($f->get_watched(), 'Test so it works to remove all watched files');
        foreach ($this->$tempFiles[0]s as $tempfile) {
            file_put_contents($tempfile, 'another data set', FILE_APPEND);
        }

        sleep(2);

        #self::assertIsArray($f->get_changes(), 'tests so files arrays are equal');
        self::assertEquals(2, $f->count_changes(), 'test so we detected changes');
*/

    }


}
