<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader\Unit\LoaderContainer;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PTS\SymfonyDiLoader\CacheWatcher;
use PTS\SymfonyDiLoader\LoaderContainer;
use ReflectionException;
use ReflectionMethod;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TryGetContainerFromCacheTest extends TestCase
{
	protected string $cacheFile = 'cache.php';

	public function tearDown(): void
	{
		parent::tearDown();
		@unlink('vfs://temp-di-loader/cache.php');
	}

	/**
	 * @throws ReflectionException
	 */
	public function testCacheNotExist(): void
	{
		$configs = ['conf1.yml', '/some/conf2.yml'];
		$cacheFile = 'cache.php';
		$loader = new LoaderContainer($configs, $cacheFile);

		$method = new ReflectionMethod(LoaderContainer::class, 'tryGetContainerFromCache');
		$method->setAccessible(true);
		$actual = $method->invoke($loader, $cacheFile, $configs);

		self::assertNull($actual);
	}

	/**
	 * @param bool $isCheckExpired
	 * @param bool $isActual
	 * @param bool $isContainer
	 *
	 * @throws ReflectionException
	 *
	 * @dataProvider dataProvider
	 */
	public function testGetFromCache(bool $isCheckExpired, bool $isActual, bool $isContainer = false): void
	{
		$path = $this->createCacheFile();
		$configs = ['conf1.yml', '/some/conf2.yml'];
		$cacheFile = 'cache.php';

		$watcher = $this->getMockBuilder(CacheWatcher::class)->onlyMethods(['isActualCache'])->getMock();
		$watcher->method('isActualCache')->willReturn($isActual);

		/** @var MockObject|LoaderContainer $loader */
		$loader = $this->getMockBuilder(LoaderContainer::class)
			->setConstructorArgs([$configs, $cacheFile])
			->onlyMethods(['getWatcher'])
			->getMock();
		$loader->method('getWatcher')->willReturn($watcher);
		$loader->setCheckExpired($isCheckExpired);

		$method = new ReflectionMethod(LoaderContainer::class, 'tryGetContainerFromCache');
		$method->setAccessible(true);
		$actual = $method->invoke($loader, $path, $configs);

		$isContainer
			? static::assertInstanceOf(ContainerInterface::class, $actual)
			: static::assertNull($actual);

	}

	public function dataProvider(): array
	{
		return [
			'is actual' => [true, true, true],
			'is not actual cache' => [true, false, false],
			'not check cache' => [false, false, true],
			'not check cache #2' => [false, true, true],
		];
	}

	/**
	 * @throws ReflectionException
	 */
	protected function createCacheFile(): string
	{
		$class = 'AppContainer';
		$loader = $this->createMock(LoaderContainer::class);

		$container = $this->getMockBuilder(ContainerBuilder::class)
			->onlyMethods(['isCompiled'])
			->getMock();
		$container->method('isCompiled')->willReturn(true);

		$vfs = vfsStream::setup('temp-di-loader');
		$path = vfsStream::newFile($this->cacheFile)->at($vfs)->url();

		$method = new ReflectionMethod(LoaderContainer::class, 'dump');
		$method->setAccessible(true);
		$method->invoke($loader, $path, $class, $container);

		return $path;
	}
}
