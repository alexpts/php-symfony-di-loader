<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader\Unit\CacheWatcher;

use Exception;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use PTS\SymfonyDiLoader\CacheWatcher;

class IsExpiredTest extends TestCase
{

    protected vfsStreamDirectory $fs;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->fs = vfsStream::setup('temp-di-loader');
    }

    /**
     * @inheritdoc
     */
    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->fs);
    }

    /**
     * @param bool $expected
     * @param array $files
     *
     * @dataProvider dataProviderIsExpired
     * @throws Exception
     */
    public function testIsExpired(bool $expected, array $files): void
    {
        $cacheFilePath = null;
        $configs = [];

        foreach ($files as $i => $file) {
            $path = vfsStream::newFile(random_int(0, 9999) . '_' . $file)
                ->at($this->fs)
                ->setContent('file body')
                ->url();

            $file === 'cache.php'
                ? $cacheFilePath = $path
                : $configs[] = $path;

            $mtime = time() + $i;
            touch($path, $mtime);
        }

        $watcher = new CacheWatcher;
        $actual = $watcher->isExpired(filemtime($cacheFilePath), $configs);
        self::assertSame($expected, $actual);
    }

    public function dataProviderIsExpired(): array
    {
        return [
            [true, ['cache.php', 'a.yml', 'b.yml']],
            [true, ['a.yml', 'cache.php', 'b.yml']],
            [true, ['c.yml', 'cache.php', 'b.yml']],
            [false, ['c.yml', 'a.yml', 'cache.php']],
        ];
    }
}