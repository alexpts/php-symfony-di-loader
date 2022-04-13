<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader\Unit;

use PHPUnit\Framework\TestCase;
use PTS\SymfonyDiLoader\CacheWatcher;
use PTS\SymfonyDiLoader\Dumper;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DumperTest extends TestCase
{

    public function testDumpBadPath(): void
    {
        $dumper = new Dumper;
        $container = new ContainerBuilder;
        $container->compile();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Can`t dump cache for DI container');
        $dumper->dump('/bad/path/', 'AppContainer', $container);
    }

    public function testDumpMetaBadPath(): void
    {
        $dumper = new Dumper;
        $container = new ContainerBuilder;
        $container->compile();
        $watcher = new CacheWatcher;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Can`t dump meta for DI container');
        $dumper->dumpMeta('/bad/path/', $container, $watcher);
    }
}