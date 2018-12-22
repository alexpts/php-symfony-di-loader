<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader\Unit\LoaderContainer;

use PHPUnit\Framework\TestCase;
use PTS\SymfonyDiLoader\LoaderContainer;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class GenerateContainerTest extends TestCase
{
	public function testAddExtension(): void
	{
        $loader = $this->getMockBuilder(LoaderContainer::class)
            ->disableOriginalConstructor()
            ->setMethods(['createContainer', 'dump', 'dumpMeta'])
            ->getMock();
        $loader->expects(static::once())->method('createContainer')
            ->willReturn($this->createMock(ContainerBuilder::class));
        $loader->expects(static::once())->method('dump');
        $loader->expects(static::once())->method('dumpMeta');

        $method = new \ReflectionMethod(LoaderContainer::class, 'generateContainer');
        $method->setAccessible(true);
        $container = $method->invoke($loader);
        static::assertInstanceOf(ContainerBuilder::class, $container);
	}
}
