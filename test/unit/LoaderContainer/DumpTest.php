<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader\Unit\LoaderContainer;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use PTS\SymfonyDiLoader\LoaderContainer;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DumpTest extends TestCase
{
	/**
	 * @throws \ReflectionException
	 */
	public function testDump(): void
	{
		$configs = ['conf1.yml', '/some/conf2.yml'];
		$cacheFileName = 'cache.php';
		$class = 'AppContainer';
		$loader = new LoaderContainer($configs, $cacheFileName);

		$container = $this->getMockBuilder(ContainerBuilder::class)
			->setMethods(['isCompiled'])
			->getMock();
		$container->method('isCompiled')->willReturn(true);

		$vfs = vfsStream::setup('temp-di-loader');
		$path = vfsStream::newFile($cacheFileName)->at($vfs)->url();

		$method = new \ReflectionMethod(LoaderContainer::class, 'dump');
		$method->setAccessible(true);
		$method->invoke($loader, $path, $class, $container);

		$dump = file_get_contents($path);
		static::assertNotEmpty($dump);
		static::assertContains("class {$class} extends Container", $dump);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testDumpNotPermission(): void
	{
		$configs = ['conf1.yml', '/some/conf2.yml'];
		$cacheFileName = 'cache.php';
		$class = 'AppContainer';
		$loader = new LoaderContainer($configs, $cacheFileName);

		$container = $this->getMockBuilder(ContainerBuilder::class)
			->setMethods(['isCompiled'])
			->getMock();
		$container->method('isCompiled')->willReturn(true);

		$vfs = vfsStream::setup('temp-di-loader');
		$path = vfsStream::newFile($cacheFileName, 0444)->at($vfs)->url();

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Can`t dump cache for DI container');

		$method = new \ReflectionMethod(LoaderContainer::class, 'dump');
		$method->setAccessible(true);
		$method->invoke($loader, $path, $class, $container);
	}
}
