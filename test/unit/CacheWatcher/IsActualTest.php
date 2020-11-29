<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader\Unit\CacheWatcher;

use JsonException;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use PTS\SymfonyDiLoader\CacheWatcher;

class IsActualTest extends TestCase
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
     * @param int $cacheCrAt
     * @param array $watches
     * @param array $reflections
     * @param bool $isActual
     *
     * @throws JsonException
     * @dataProvider dataProviderIsActual
     */
    public function testIsActual(int $cacheCrAt, array $watches, array $reflections, bool $isActual): void
    {
        $cacheFiles = [
            'watch' => ['vfs://temp-di-loader/a.config.yml'],
            'reflections' => ['vfs://temp-di-loader/autoload.php'],
        ];
        $json = json_encode($cacheFiles, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $pathCache = vfsStream::newFile('cache.php.v2.meta')->at($this->fs)->setContent($json)->url();
        touch($pathCache, $cacheCrAt);
        $pathCache = vfsStream::newFile('cache.php')->at($this->fs)->setContent($json)->url();
        touch($pathCache, $cacheCrAt);

        $files = [];
        foreach ($watches as $name => $ctime) {
            $path = vfsStream::newFile($name)->at($this->fs)->setContent('file body')->url();
            touch($path, $ctime);
            $files[] = $path;
        }
        foreach ($reflections as $name => $ctime) {
            $path = vfsStream::newFile($name)->at($this->fs)->setContent('file body')->url();
            touch($path, $ctime);
        }

        $watcher = new CacheWatcher;
        $actual = $watcher->isActual($pathCache, $files);
        self::assertSame($isActual, $actual);
    }

    public function dataProviderIsActual(): array
    {
        return [
            'not modify' => [
                1001,
                ['a.config.yml' => 1000],
                ['autoload.php' => 1000],
                true,
            ],
            'add new config' => [
                1001,
                ['a.config.yml' => 1000, 'b.yml' => 400],
                ['autoload.php' => 1000],
                false,
            ],
            'remove config' => [
                1001,
                [],
                ['autoload.php' => 1000],
                false,
            ],
            'update config' => [
                1001,
                [],
                ['autoload.php' => 2000],
                false,
            ],
            'update reflection file' => [
                1001,
                ['a.config.yml' => 1000],
                ['autoload.php' => 2000],
                false,
            ],
            'remove reflection file' => [
                1001,
                ['a.config.yml' => 1000],
                [],
                false,
            ],
        ];
    }
}