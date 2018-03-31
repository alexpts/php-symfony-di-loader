<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class FactoryContainerTest extends TestCase
{

    /**
     * @throws \ReflectionException
     */
    public function testConstructor(): void
    {
        $arg1 = YamlFileLoader::class;
        $arg2 = new FileLocator;

        $factory = new FactoryContainer($arg1, $arg2);
        self::assertInstanceOf(FactoryContainer::class, $factory);

        $classLoader = new \ReflectionProperty(FactoryContainer::class, 'classLoader');
        $classLoader->setAccessible(true);
        self::assertSame($arg1, $classLoader->getValue($factory));

        $locator = new \ReflectionProperty(FactoryContainer::class, 'locator');
        $locator->setAccessible(true);
        self::assertSame($arg2, $locator->getValue($factory));
    }

    /**
     * @throws \ReflectionException
     */
    public function testCreateBuilder(): void
    {
        $factory = new FactoryContainer(YamlFileLoader::class, new FileLocator);
        $createBuilder = new \ReflectionMethod(FactoryContainer::class, 'createBuilder');
        $createBuilder->setAccessible(true);
        $actual = $createBuilder->invoke($factory);

        self::assertInstanceOf(ContainerBuilder::class, $actual);
    }

    /**
     * @throws \ReflectionException
     */
    public function testCreateLoader(): void
    {
        $locator = new FileLocator;
        $builder = new ContainerBuilder;
        $factory = new FactoryContainer(YamlFileLoader::class, $locator);

        $createLoader = new \ReflectionMethod(FactoryContainer::class, 'createLoader');
        $createLoader->setAccessible(true);
        $actual = $createLoader->invoke($factory, $builder, $locator);

        self::assertInstanceOf(YamlFileLoader::class, $actual);
    }

    /**
     * @param array $configs
     *
     * @throws \Exception
     *
     * @dataProvider dataProviderCreate
     */
    public function testCreate(array $configs): void
    {
        $locator = new FileLocator;
        $classLoader = YamlFileLoader::class;

        $loaderMock = $this->getMockBuilder(YamlFileLoader::class)
            ->disableOriginalConstructor()
            ->setMethods(['load'])
            ->getMock();
        $loaderMock->expects(self::exactly(\count($configs)))->method('load');

        /** @var MockObject|FactoryContainer $factory */
        $factory = $this->getMockBuilder(FactoryContainer::class)
            ->setMethods(['createLoader'])
            ->setConstructorArgs([$classLoader, $locator])
            ->getMock();
        $factory->expects(self::once())->method('createLoader')->willReturn($loaderMock);

        $actual = $factory->create($configs);
        self::assertInstanceOf(ContainerBuilder::class, $actual);
    }

    public function dataProviderCreate(): array
    {
        return [
            [
                []
            ],
            [
                ['a.yaml']
            ],
            [
                ['a.yaml', 'b.yaml']
            ],
        ];
    }
}