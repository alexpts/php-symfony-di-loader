<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader\Unit;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use PHPUnit\Framework\TestCase;
use PTS\SymfonyDiLoader\CacheWatcher;
use PTS\SymfonyDiLoader\LoaderContainer;
use PTS\SymfonyDiLoader\LoaderContainerInterface;
use ReflectionProperty;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class LoaderContainerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        vfsStream::setup('/temp/');
    }

    protected function tearDown(): void
    {
        vfsStreamWrapper::unregister();
        parent::tearDown();
    }

    public function testConstructor(): void
    {
        $loader = new LoaderContainer;
        static::assertInstanceOf(LoaderContainerInterface::class, $loader);
    }

    public function testAddExtension(): void
    {
        $loader = new LoaderContainer;
        $prop = new ReflectionProperty($loader, 'extensions');
        $prop->setAccessible(true);

        static::assertCount(0, $prop->getValue($loader));

        $loader = $loader->addExtension(new TestExtension);
        static::assertInstanceOf(LoaderContainerInterface::class, $loader);
        static::assertCount(1, $prop->getValue($loader));
    }

    public function testSetCheckExpired(): void
    {
        $loader = new LoaderContainer;
        $prop = new ReflectionProperty($loader, 'checkExpired');
        $prop->setAccessible(true);

        static::assertTrue($prop->getValue($loader));

        $loader = $loader->setCheckExpired(false);
        static::assertInstanceOf(LoaderContainerInterface::class, $loader);
        static::assertFalse($prop->getValue($loader));

        $loader = $loader->setCheckExpired(true);
        static::assertTrue($prop->getValue($loader));
    }

    public function testGetWatcher(): void
    {
        $loader = new LoaderContainer;
        static::assertInstanceOf(CacheWatcher::class, $loader->getWatcher());
    }

    public function testGetContainer(): void
    {
        $loader = new LoaderContainer;
        $container = $loader->getContainer([__DIR__ . '/config/di.yml'], 'vfs://temp/cache.php');
        static::assertInstanceOf(ContainerBuilder::class, $container);
        static::assertInstanceOf(Container::class, $container);
        static::assertFileExists('vfs://temp/cache.php');
        static::assertFileExists('vfs://temp/cache.php.v2.meta');
    }

    public function testGetContainerFromCacheFile(): void
    {
        // build case
        $loader = new LoaderContainer;
        $container = $loader->getContainer([__DIR__ . '/config/di.yml'], 'vfs://temp/cache.php');
        static::assertInstanceOf(ContainerBuilder::class, $container);

        // file cache case
        $loader->clearProcessCache();
        $container2 = $loader->getContainer([__DIR__ . '/config/di.yml'], 'vfs://temp/cache.php');
        static::assertNotInstanceOf(ContainerBuilder::class, $container2);
        static::assertInstanceOf(Container::class, $container2);
    }

    public function testGetContainerFromCacheProcess(): void
    {
        // Build
        $loader = new LoaderContainer;
        $loader->getContainer([__DIR__ . '/config/di.yml'], 'vfs://temp/cache.php');

        // Process cache
        $container = $loader->getContainer([__DIR__ . '/config/di.yml'], 'vfs://temp/cache.php');
        static::assertInstanceOf(ContainerBuilder::class, $container);

        // file cache
        $loader->clearProcessCache();
        $container = $loader->getContainer([__DIR__ . '/config/di.yml'], 'vfs://temp/cache.php');
        static::assertNotInstanceOf(ContainerBuilder::class, $container);
        static::assertInstanceOf(Container::class, $container);

        // Process cache
        unlink('vfs://temp/cache.php');
        $container = $loader->getContainer([__DIR__ . '/config/di.yml'], 'vfs://temp/cache.php');
        static::assertNotInstanceOf(ContainerBuilder::class, $container);
        static::assertInstanceOf(Container::class, $container);
    }
}