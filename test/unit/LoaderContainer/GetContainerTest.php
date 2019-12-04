<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader\Unit\LoaderContainer;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PTS\SymfonyDiLoader\LoaderContainer;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class GetContainerTest extends TestCase
{
	public function testGenerate(): void
	{
	    /** @var LoaderContainer|MockObject $loader */
        $loader = $this->getMockBuilder(LoaderContainer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['tryGetContainerFromCache', 'generateContainer'])
            ->getMock();
        $loader->expects(static::once())->method('generateContainer')
            ->willReturn($this->createMock(ContainerBuilder::class));
        $loader->expects(static::once())->method('tryGetContainerFromCache');

        $container = $loader->getContainer();
        $container2 = $loader->getContainer();
        static::assertInstanceOf(ContainerBuilder::class, $container);
        static::assertInstanceOf(ContainerBuilder::class, $container2);
	}

    public function testCache(): void
    {
        /** @var LoaderContainer|MockObject $loader */
        $loader = $this->getMockBuilder(LoaderContainer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['tryGetContainerFromCache', 'generateContainer'])
            ->getMock();
        $loader->expects(static::never())->method('generateContainer');
        $loader->expects(static::once())->method('tryGetContainerFromCache')
            ->willReturn($this->createMock(ContainerBuilder::class));

        $container = $loader->getContainer();
        $container2 = $loader->getContainer();
        static::assertInstanceOf(ContainerBuilder::class, $container);
        static::assertInstanceOf(ContainerBuilder::class, $container2);
    }
}
