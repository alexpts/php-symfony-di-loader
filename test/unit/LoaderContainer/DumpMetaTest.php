<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader\Unit\LoaderContainer;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use PTS\SymfonyDiLoader\LoaderContainer;
use ReflectionException;
use ReflectionMethod;

class DumpMetaTest extends TestCase
{
	/**
	 * @throws ReflectionException
	 */
	public function testDumpMeta(): void
	{
		$configs = ['conf1.yml', '/some/conf2.yml'];
		$cacheFileName = 'cache.php';
		$loader = new LoaderContainer($configs, $cacheFileName);

		$vfs = vfsStream::setup('temp-di-loader');
		$path = vfsStream::newFile($cacheFileName . '.meta')->at($vfs)->url();

		$method = new ReflectionMethod(LoaderContainer::class, 'dumpMeta');
		$method->setAccessible(true);
		$method->invoke($loader, $path, $configs);

		$dump = file_get_contents($path);
		static::assertNotEmpty($dump);
		static::assertSame($configs, unserialize($dump));
	}

	/**
	 * @throws ReflectionException
	 */
	public function testDumpMetaNotPermission(): void
	{
		$configs = ['conf1.yml', '/some/conf2.yml'];
		$cacheFileName = 'cache.php';
		$loader = new LoaderContainer($configs, $cacheFileName);

		$vfs = vfsStream::setup('temp-di-loader');
		$path = vfsStream::newFile($cacheFileName . '.meta', 0444)->at($vfs)->url();

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Can`t dump meta for DI container');

		$method = new ReflectionMethod(LoaderContainer::class, 'dumpMeta');
		$method->setAccessible(true);
		$method->invoke($loader, $path, $configs);
	}
}
