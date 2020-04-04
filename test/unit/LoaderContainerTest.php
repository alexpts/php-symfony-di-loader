<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader\Unit;

use PHPUnit\Framework\TestCase;
use PTS\SymfonyDiLoader\LoaderContainer;
use PTS\SymfonyDiLoader\LoaderContainerInterface;
use ReflectionProperty;

class LoaderContainerTest extends TestCase
{
	public function testConstructor(): void
	{
		$loader = new LoaderContainer;
		static::assertInstanceOf(LoaderContainerInterface::class, $loader);
	}

	public function testAddExtension(): void
	{
		$loader = new LoaderContainer;
		$prop = new ReflectionProperty($loader, 'extensions');
		$prop->setAccessible(true);

		static::assertCount(0, $prop->getValue($loader));

		$loader = $loader->addExtension(new TestExtension);
		static::assertInstanceOf(LoaderContainerInterface::class, $loader);
		static::assertCount(1, $prop->getValue($loader));
	}

	public function testSetCheckExpired(): void
	{
		$loader = new LoaderContainer;
		$prop = new ReflectionProperty($loader, 'checkExpired');
		$prop->setAccessible(true);

		static::assertTrue($prop->getValue($loader));

		$loader = $loader->setCheckExpired(false);
		static::assertInstanceOf(LoaderContainerInterface::class, $loader);
		static::assertFalse($prop->getValue($loader));

		$loader = $loader->setCheckExpired(true);
		static::assertTrue($prop->getValue($loader));
	}
}