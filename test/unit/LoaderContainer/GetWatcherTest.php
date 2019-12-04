<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader\Unit\LoaderContainer;

use PHPUnit\Framework\TestCase;
use PTS\SymfonyDiLoader\CacheWatcher;
use PTS\SymfonyDiLoader\LoaderContainer;
use ReflectionMethod;

class GetWatcherTest extends TestCase
{

	/**
	 * @throws \ReflectionException
	 */
	public function testGetWatcher(): void
    {
		$cacheFile = '../../temp/cache.php';
		$configs = ['a.yml', 'b.yml'];
		$loader = new LoaderContainer($configs, $cacheFile);

		$method = new ReflectionMethod(LoaderContainer::class, 'getWatcher');
		$method->setAccessible(true);
		$actual = $method->invoke($loader);

		self::assertInstanceOf(CacheWatcher::class, $actual);
    }
}
