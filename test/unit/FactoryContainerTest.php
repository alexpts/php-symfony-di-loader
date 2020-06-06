<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader\Unit;

use PHPUnit\Framework\TestCase;
use PTS\SymfonyDiLoader\FactoryContainer;
use ReflectionMethod;
use ReflectionProperty;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FactoryContainerTest extends TestCase
{

    public function testConstructor(): void
    {
        $factory = new FactoryContainer;
        self::assertInstanceOf(FactoryContainer::class, $factory);

        $locator = new ReflectionProperty(FactoryContainer::class, 'locator');
        $locator->setAccessible(true);
        self::assertInstanceOf(FileLocatorInterface::class, $locator->getValue($factory));
    }

    public function testCreateBuilder(): void
    {
        $factory = new FactoryContainer;
        $createBuilder = new ReflectionMethod(FactoryContainer::class, 'createBuilder');

        $createBuilder->setAccessible(true);
        $actual = $createBuilder->invoke($factory);

        self::assertInstanceOf(ContainerBuilder::class, $actual);
    }
}
