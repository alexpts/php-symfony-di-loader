<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class LoaderContainerTest extends TestCase
{

    /**
     * @throws \ReflectionException
     */
    public function testConstructor(): void
    {
        $arg1 = ['a.yml', 'b.yml'];
        $arg2 = 'container.cache.php';
        $arg3 = new FactoryContainer(YamlFileLoader::class, new FileLocator);

        $factory = new LoaderContainer($arg1, $arg2, $arg3);
        self::assertInstanceOf(LoaderContainer::class, $factory);

        $classLoader = new \ReflectionProperty(LoaderContainer::class, 'configFiles');
        $classLoader->setAccessible(true);
        self::assertSame($arg1, $classLoader->getValue($factory));

        $locator = new \ReflectionProperty(LoaderContainer::class, 'cacheFile');
        $locator->setAccessible(true);
        self::assertSame($arg2, $locator->getValue($factory));

        $locator = new \ReflectionProperty(LoaderContainer::class, 'factory');
        $locator->setAccessible(true);
        self::assertSame($arg3, $locator->getValue($factory));
    }

    /**
     * @param bool $expected
     * @param bool $value
     *
     * @throws \ReflectionException
     *
     * @dataProvider dataProviderCheckExpired
     */
    public function testSetCheckExpired(bool $expected, bool $value): void
    {
        /** @var MockObject|LoaderContainer $loader */
        $loader = $this->getMockBuilder(LoaderContainer::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['setCheckExpired'])
            ->getMock();

        $actual = $loader->setCheckExpired($value);
        self::assertInstanceOf(LoaderContainer::class, $actual);

        $checkExpired = new \ReflectionProperty(LoaderContainer::class, 'checkExpired');
        $checkExpired->setAccessible(true);
        $actual = $checkExpired->getValue($loader);
        self::assertSame($expected, $actual);
    }

    public function dataProviderCheckExpired(): array
    {
        return [
            [true, true],
            [false, false]
        ];
    }

    /**
     * @throws \ReflectionException
     */
    public function testSetContainer(): void
    {
        $container = $this->getMockForAbstractClass(ContainerInterface::class);

        /** @var MockObject|LoaderContainer $loader */
        $loader = $this->getMockBuilder(LoaderContainer::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['setContainer'])
            ->getMock();

        $actual = $loader->setContainer($container);
        self::assertInstanceOf(LoaderContainer::class, $actual);

        $checkExpired = new \ReflectionProperty(LoaderContainer::class, 'container');
        $checkExpired->setAccessible(true);
        $actual = $checkExpired->getValue($loader);
        self::assertInstanceOf(ContainerInterface::class, $actual);
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function testGetContainer(): void
    {
        $container = $this->getMockForAbstractClass(ContainerInterface::class);

        /** @var MockObject|LoaderContainer $loader */
        $loader = $this->getMockBuilder(LoaderContainer::class)
            ->disableOriginalConstructor()
            ->setMethods(['createContainer'])
            ->getMock();
        $loader->expects(self::once())->method('createContainer')->willReturn($container);

        $created = $loader->getContainer();
        self::assertInstanceOf(ContainerInterface::class, $created);

        $fromCache = $loader->getContainer();
        self::assertInstanceOf(ContainerInterface::class, $fromCache);
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function testGetFactory(): void
    {
        $arg1 = ['a.yml', 'b.yml'];
        $arg2 = 'container.cache.php';
        $arg3 = new FactoryContainer(YamlFileLoader::class, new FileLocator);

        $loader = new LoaderContainer($arg1, $arg2, $arg3);
        $getFactory = new \ReflectionMethod(LoaderContainer::class, 'getFactory');
        $getFactory->setAccessible(true);
        $actual = $getFactory->invoke($loader);

        self::assertInstanceOf(FactoryContainer::class, $actual);
    }

    /**
     * @param bool $expected
     * @param int $cacheIndex
     * @param array $files
     *
     * @throws \ReflectionException
     *
     * @dataProvider dataProviderIsExpired
     * @throws \Exception
     */
    public function testIsExpired(bool $expected, int $cacheIndex, array $files): void
    {
        $root = vfsStream::setup('/temp');

        foreach ($files as $i => $file) {
            $path = vfsStream::newFile(random_int(0, 9999) . '_' . $files[$i])->at($root)->setContent('cache')->url();

            $i === $cacheIndex
                ? $cacheFilePath = $path
                : $configs[] = $path;


            (\count($files)-1) !== $i && $this->sleepToNextSec(); // last modify time file minimal sec
        }

        /** @var MockObject|LoaderContainer $loader */
        $loader = $this->createMock(LoaderContainer::class);

        $isExpired = new \ReflectionMethod(LoaderContainer::class, 'isExpired');
        $isExpired->setAccessible(true);
        $actual = $isExpired->invoke($loader, $cacheFilePath, $configs);
        self::assertSame($expected, $actual);
    }

    protected function sleepToNextSec(): void
    {
        [$uSec] = explode(' ', microtime());
        $waitTime = 1000000 - floor($uSec * 1000000);
        usleep((int)$waitTime);
    }

    public function dataProviderIsExpired(): array
    {
        return [
            [true, 0, ['cache.php', 'a.yml', 'b.yml']],
            [true, 1, ['a.yml', 'cache.php', 'b.yml']],
            [true, 1, ['c.yml', 'cache.php', 'b.yml']],
            [false, 2, ['c.yml', 'a.yml', 'cache.php']],
        ];
    }

    /**
     * @throws \ReflectionException
     */
    public function testCreateContainerFromCache(): void
    {
        $arg1 = ['a.yml', 'b.yml'];
        $arg2 = 'container.cache.php';
        $arg3 = new FactoryContainer(YamlFileLoader::class, new FileLocator);

        $container = $this->createMock(Container::class);

        /** @var MockObject|LoaderContainer $loader */
        $loader = $this->getMockBuilder(LoaderContainer::class)
            ->setMethods(['getContainerFromCache', 'flushContainerToFile', 'getFactory'])
            ->setConstructorArgs([$arg1, $arg2, $arg3])
            ->getMock();
        $loader->expects(self::once())->method('getContainerFromCache')->with($arg2, $arg1)->willReturn($container);
        $loader->expects(self::never())->method('getFactory');
        $loader->expects(self::never())->method('flushContainerToFile');

        $createContainer = new \ReflectionMethod(LoaderContainer::class, 'createContainer');
        $createContainer->setAccessible(true);
        $actual = $createContainer->invoke($loader);

        self::assertInstanceOf(ContainerInterface::class, $actual);
    }

    /**
     * @throws \ReflectionException
     */
    public function testCreateContainerFromFactory(): void
    {
        $arg1 = ['a.yml', 'b.yml'];
        $arg2 = 'container.cache.php';
        $arg3 = new FactoryContainer(YamlFileLoader::class, new FileLocator);

        $builder = $this->createMock(ContainerBuilder::class);

        $factory = $this->getMockBuilder(FactoryContainer::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $factory->expects(self::once())->method('create')->with($arg1)->willReturn($builder);

        /** @var MockObject|LoaderContainer $loader */
        $loader = $this->getMockBuilder(LoaderContainer::class)
            ->setMethods(['getContainerFromCache', 'flushContainerToFile', 'getFactory'])
            ->setConstructorArgs([$arg1, $arg2, $arg3])
            ->getMock();
        $loader->expects(self::once())->method('getContainerFromCache')->with($arg2, $arg1)->willReturn(null);
        $loader->expects(self::once())->method('getFactory')->willReturn($factory);
        $loader->expects(self::once())->method('flushContainerToFile')->willReturn($arg2);

        $createContainer = new \ReflectionMethod(LoaderContainer::class, 'createContainer');
        $createContainer->setAccessible(true);
        $actual = $createContainer->invoke($loader);

        self::assertInstanceOf(ContainerInterface::class, $actual);
    }

    /**
     * @throws \ReflectionException
     */
    public function testFlushContainerToFile(): void
    {
        $root = vfsStream::setup('/temp');
        $filePath = vfsStream::newFile('cache.php')->at($root)->url();

        $className = 'AppContainer';
        $container = new ContainerBuilder;
        $container->compile();

        $loader = $this->createMock(LoaderContainer::class);
        $flushContainerToFile = new \ReflectionMethod(LoaderContainer::class, 'flushContainerToFile');
        $flushContainerToFile->setAccessible(true);
        $flushContainerToFile->invoke($loader, $filePath, $className, $container);

        $fileContent = file_get_contents($filePath);
        self::assertTrue((bool)\strlen($fileContent));
        self::assertTrue((bool)\strpos($fileContent, 'class AppContainer extends Container'));
    }

    /**
     * @param bool $hasCache
     * @param bool $fileExist
     * @param int $isExpiredInvokeCount
     * @param bool $isExpired
     * @param bool $checkExpired
     *
     * @throws \ReflectionException
     *
     * @dataProvider dataProviderGetContainerFromCache
     */
    public function testGetContainerFromCache(
        bool $hasCache,
        bool $fileExist,
        int $isExpiredInvokeCount,
        bool $isExpired = true,
        bool $checkExpired = true
    ): void {
        $configs = [];

        $root = vfsStream::setup('/temp');
        $file = $this->createCacheFile($root);

        if (!$fileExist) {
            unlink($file);
        }

        /** @var MockObject|LoaderContainer $loader */
        $loader = $this->getMockBuilder(LoaderContainer::class)
            ->disableOriginalConstructor()
            ->setMethods(['isExpired'])
            ->getMock();
        $loader->expects(self::exactly($isExpiredInvokeCount))->method('isExpired')
            ->with($file, $configs)->willReturn($isExpired);

        $loader->setCheckExpired($checkExpired);

        $getContainerFromCache = new \ReflectionMethod(LoaderContainer::class, 'getContainerFromCache');
        $getContainerFromCache->setAccessible(true);
        $actual = $getContainerFromCache->invoke($loader, $file, $configs);

        $hasCache
            ? self::assertInstanceOf(ContainerInterface::class, $actual)
            : self::assertNull($actual);
    }

    public function dataProviderGetContainerFromCache(): array
    {
        return [
            'file not exist' => [false, false, 0, false],
            'cache expired' => [false, true, 1, true],
            'skip check expired' => [true, true, 0, true, false],
            'cache hit' => [true, true, 1, false],
        ];
    }

    protected function createCacheFile(vfsStreamDirectory $root): string
    {
        $container = new ContainerBuilder;
        $container->compile();
        $dumper = new PhpDumper($container);

        $content = $dumper->dump([
            'class' => 'AppContainer',
        ]);

        return vfsStream::newFile('cache.php')->at($root)->setContent($content)->url();
    }
}