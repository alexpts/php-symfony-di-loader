<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader\Unit\CacheWatcher;

use JsonException;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use PTS\SymfonyDiLoader\CacheWatcher;
use ReflectionMethod;
use RuntimeException;

class GetMetaCacheTest extends TestCase
{

	public function testNotFound(): void
	{
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Can`t read meta for DI container');

		$watcher = new CacheWatcher;
		$watcher->isActual('unknownCachePath.php', ['conf1']);
	}

	/**
	 * @param array $expected
	 * @param array $configs
	 *
	 * @dataProvider dataProvider
	 * @throws JsonException
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
	 * @throws JsonException
	 */
	protected function createMetaCache(array $configs, string $filePath): string
	{
		$fs = vfsStream::setup('/temp/di-loader');

		$content = json_encode($configs, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
		return vfsStream::newFile($filePath)
			->at($fs)
			->setContent($content)
			->url();
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
