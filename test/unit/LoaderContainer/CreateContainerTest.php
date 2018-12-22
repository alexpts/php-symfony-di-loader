<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader\Unit\LoaderContainer;

use PHPUnit\Framework\TestCase;
use PTS\SymfonyDiLoader\FactoryContainer;
use PTS\SymfonyDiLoader\LoaderContainer;
use PTS\SymfonyDiLoader\Unit\TestExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CreateContainerTest extends TestCase
{
	public function testAddExtension(): void
	{
	    $configs = ['a.yml', 'b.yml'];
        $extensions = [new TestExtension];

        $factory = $this->getMockBuilder(FactoryContainer::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $factory->expects(static::once())->method('create')
            ->with($configs, $extensions)
            ->willReturn($this->createMock(ContainerBuilder::class));

        $loader = new LoaderContainer([], '', $factory);

        $method = new \ReflectionMethod(LoaderContainer::class, 'createContainer');
        $method->setAccessible(true);
        $container = $method->invoke($loader, $configs, $extensions);
        static::assertInstanceOf(ContainerBuilder::class, $container);
	}
}
