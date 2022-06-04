<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader\Unit;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use PHPUnit\Framework\TestCase;
use PTS\SymfonyDiLoader\LoaderContainer;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class LoaderContainerTest extends TestCase
{
    protected string $diCache = 'vfs://temp/cache.php';
    protected string $diConfig =  __DIR__ . '/config/di.yml';

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

    public function testGetContainer(): void
    {
        $loader = new LoaderContainer;
        $container = $loader->getContainer([$this->diConfig], $this->diCache);

        static::assertInstanceOf(ContainerBuilder::class, $container);
        static::assertInstanceOf(Container::class, $container);
        static::assertFileExists('vfs://temp/cache.php');
        static::assertFileExists('vfs://temp/cache.php.meta');

        $fileLocator = $container->get(FileLocator::class);
        static::assertInstanceOf(FileLocator::class, $fileLocator);
    }

    public function testGetContainerFromCacheFile(): void
    {
        // build case
        $loader = new LoaderContainer;
        $container = $loader->getContainer([$this->diConfig], $this->diCache, true);
        static::assertInstanceOf(ContainerBuilder::class, $container);
        static::assertInstanceOf(Container::class, $container);
        // static::assertNotInstanceOf('AppContainer', $container);

        // file cache case
        $loader = new LoaderContainer;
        $container = $loader->getContainer([$this->diConfig], $this->diCache, true);
        static::assertNotInstanceOf(ContainerBuilder::class, $container);
        static::assertInstanceOf(Container::class, $container);
        // static::assertInstanceOf('AppContainer', $container);

        // Remove file cache
        unlink('vfs://temp/cache.php');
        $loader = new LoaderContainer;
        $container = $loader->getContainer([$this->diConfig], $this->diCache, true);
        static::assertInstanceOf(ContainerBuilder::class, $container);
        static::assertInstanceOf(Container::class, $container);
        // static::assertNotInstanceOf('AppContainer', $container);

        // Remove file cache config list
        unlink('vfs://temp/cache.php.meta.configs');
        $loader = new LoaderContainer;
        $container = $loader->getContainer([$this->diConfig], $this->diCache, true);
        static::assertInstanceOf(ContainerBuilder::class, $container);
        static::assertInstanceOf(Container::class, $container);
        // static::assertNotInstanceOf('AppContainer', $container);

        // Remove file cache meta
        unlink('vfs://temp/cache.php.meta');
        $loader = new LoaderContainer;
        $container = $loader->getContainer([$this->diConfig], $this->diCache, true);
        static::assertInstanceOf(ContainerBuilder::class, $container);
        static::assertInstanceOf(Container::class, $container);
        // static::assertNotInstanceOf('AppContainer', $container);
    }

    public function testAnyFormatConfigs(): void
    {
        $configs = [
            __DIR__ . '/config/di.xml',
            __DIR__ . '/config/di.php',
            __DIR__ . '/config/di.yml',
        ];

        $loader = new LoaderContainer;
        $container = $loader->getContainer($configs, $this->diCache);

        static::assertSame('php',  $container->getParameter('php'));
        static::assertSame('xml',  $container->getParameter('xml'));
        static::assertSame('yml',  $container->getParameter('yml'));
    }

    public function testAnyBaseConfig(): void
    {
        $configs = [__DIR__ . '/config/di.xml'];

        $loader = new LoaderContainer;
        $container = $loader->getContainer($configs, $this->diCache);
        static::assertInstanceOf(ContainerBuilder::class, $container);

        $loader = new LoaderContainer;
        $container = $loader->getContainer([$this->diConfig], $this->diCache, true);
        // cache not use
        static::assertInstanceOf(ContainerBuilder::class, $container);
    }

    public function testAddConfig(): void
    {
        $loader = new LoaderContainer;
        $container = $loader->getContainer([$this->diConfig], $this->diCache);
        static::assertInstanceOf(ContainerBuilder::class, $container);

        $loader = new LoaderContainer;
        $container = $loader->getContainer([$this->diConfig, __DIR__ . '/config/di.xml'], $this->diCache, true);
        // cache not use
        static::assertInstanceOf(ContainerBuilder::class, $container);
    }

    public function testRemoveConfig(): void
    {
        $loader = new LoaderContainer;
        $container = $loader->getContainer([$this->diConfig, __DIR__ . '/config/di.xml'], $this->diCache, true);
        static::assertInstanceOf(ContainerBuilder::class, $container);

        $loader = new LoaderContainer;
        $container = $loader->getContainer([$this->diConfig], $this->diCache, true);
        // cache not use
        static::assertInstanceOf(ContainerBuilder::class, $container);
    }

    public function testBuilderWork(): void
    {
        $loader = new LoaderContainer;
        $loader->getBuilder()->setParameter('hello', 'world');
        $container = $loader->getContainer([$this->diConfig], $this->diCache);

        static::assertInstanceOf(ContainerBuilder::class, $container);
        static::assertSame('world', $container->getParameter('hello'));
    }

    public function testProductionMode(): void
    {
        $loader = new LoaderContainer;
        $container = $loader->getContainer([$this->diConfig], $this->diCache, true);
        static::assertInstanceOf(ContainerBuilder::class, $container);

        $loader = new LoaderContainer;
        $container = $loader->getContainer([$this->diConfig, __DIR__ . '/config/di.xml'], $this->diCache);
        // cache reuse in not debug mode
        static::assertNotInstanceOf(ContainerBuilder::class, $container);
    }

}