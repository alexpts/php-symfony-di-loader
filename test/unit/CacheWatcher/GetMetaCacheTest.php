<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader\Unit\CacheWatcher;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use PTS\SymfonyDiLoader\CacheWatcher;
use ReflectionException;
use ReflectionMethod;

class GetMetaCacheTest extends TestCase
{

	/**
	 * @inheritdoc
	 */
	public function tearDown(): void
	{
		parent::tearDown();
		unset($this->fs);
	}

	public function testNotFound(): void
	{
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Can`t read meta for DI container');

		$watcher = new CacheWatcher;
		$watcher->isActualCache('unknownCachePath.php', ['conf1']);
	}

	/**
	 * @param array $expected
	 * @param array $configs
	 *
	 * @throws ReflectionException
	 *
	 * @dataProvider dataProvider
	 */
	public function testGet(array $expected, array $configs): void
	{
		$watcher = new CacheWatcher;
		$filePath = $this->createMetaCache($configs, 'pathToFileCache');

		$method = new ReflectionMethod(CacheWatcher::class, 'getMetaCache');
		$method->setAccessible(true);
		$actual = $method->invoke($watcher, $filePath);

		static::assertSame($expected, $actual);
	}

	/**
	 * @param string[] $configs
	 * @param string $filePath
	 *
	 * @return string
	 */
	protected function createMetaCache(array $configs, string $filePath): string
	{
		$fs = vfsStream::setup('/temp/di-loader');

		$filePathInMemory = vfsStream::newFile($filePath)
			->at($fs)
			->setContent(serialize($configs))
			->url();

		return $filePathInMemory;
	}

	public function dataProvider(): array
	{
		return [
			'same config' => [
				['/some/conf1.yml', '/some/conf2.yml'],
				['/some/conf1.yml', '/some/conf2.yml'],
			]
		];
	}
}
