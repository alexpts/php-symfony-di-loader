<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader\Unit\CacheWatcher;

use PHPUnit\Framework\TestCase;
use PTS\SymfonyDiLoader\CacheWatcher;

class GetWatchFilesTest extends TestCase
{
    public function testGetWatchFiles(): void
    {
        $dir = __DIR__;
        $watcher = (new CacheWatcher)
            ->setWatchFiles([__FILE__, $dir . '/IsExpiredTest.php'])
            ->addWatchFile($dir . '/GetMetaCacheTest.php')
            ->addWatchFile(__FILE__) // duplicate file
            ->addWatchFile('a'); // not exist file

        $expected = [
            $dir . '/GetWatchFilesTest.php',
            $dir . '/IsExpiredTest.php',
            $dir . '/GetMetaCacheTest.php',
        ];
        static::assertSame($expected, $watcher->getWatchFiles());
    }

}