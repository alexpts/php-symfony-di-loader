<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader\Unit\LoaderContainer;

use PHPUnit\Framework\TestCase;
use PTS\SymfonyDiLoader\FactoryContainer;
use PTS\SymfonyDiLoader\LoaderContainer;
use ReflectionException;
use ReflectionProperty;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ConstructorTest extends TestCase
{
	/**
	 * @throws ReflectionException
	 */
	public function testConstructor(): void
	{
		$configs = ['a.yml', 'b.yml'];
		$cacheFile = 'container.cache.php';
		$factory = new FactoryContainer(YamlFileLoader::class, new FileLocator);

		$loader = new LoaderContainer($configs, $cacheFile, $factory);
		self::assertInstanceOf(LoaderContainer::class, $loader);

		$classLoader = new ReflectionProperty(LoaderContainer::class, 'configFiles');
		$classLoader->setAccessible(true);
		self::assertSame($configs, $classLoader->getValue($loader));

		$locator = new ReflectionProperty(LoaderContainer::class, 'cacheFile');
		$locator->setAccessible(true);
		self::assertSame($cacheFile, $locator->getValue($loader));

		$locator = new ReflectionProperty(LoaderContainer::class, 'factory');
		$locator->setAccessible(true);
		self::assertSame($factory, $locator->getValue($loader));
	}
}
