<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader\Unit\LoaderContainer;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PTS\SymfonyDiLoader\FactoryContainer;
use PTS\SymfonyDiLoader\LoaderContainer;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CreateContainerTest extends TestCase
{
	/**
	 * @throws \ReflectionException
	 */
	public function testCreateContainer(): void
	{
		$configs = ['conf1.yml'];
		$cacheFile = 'someCacheFile.php';
		$class = 'AppContainer';

		$container = $this->createMock(ContainerBuilder::class);

		$factory = $this->getMockBuilder(FactoryContainer::class)
			->disableOriginalConstructor()
			->setMethods(['create'])
			->getMock();
		$factory->expects(self::once())->method('create')->with($configs)->willReturn($container);

		/** @var MockObject | LoaderContainer $loader */
		$loader = $this->getMockBuilder(LoaderContainer::class)
			->setConstructorArgs([$configs, $cacheFile, $factory])
			->setMethods(['dump', 'dumpMeta'])
			->getMock();
		$loader->expects(self::once())->method('dump')->with($cacheFile, $class, $container);
		$loader->expects(self::once())->method('dumpMeta')->with($cacheFile . '.meta', $configs);

		$method = new \ReflectionMethod(LoaderContainer::class, 'createContainer');
		$method->setAccessible(true);
		$actual = $method->invoke($loader, $configs, $cacheFile, $class);

		static::assertInstanceOf(ContainerInterface::class, $actual);
	}
}
