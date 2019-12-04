<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader\Unit\LoaderContainer;

use PHPUnit\Framework\TestCase;
use PTS\SymfonyDiLoader\LoaderContainer;
use PTS\SymfonyDiLoader\Unit\TestExtension;
use ReflectionProperty;

class AddExtensionTest extends TestCase
{

	public function testAddExtension(): void
	{
        $loader = new LoaderContainer([], '');
        $loader->addExtension(new TestExtension());
        $loader->addExtension(new TestExtension());

        $property = new ReflectionProperty(LoaderContainer::class, 'extensions');
        $property->setAccessible(true);
        $extensions = $property->getValue($loader);
        static::assertCount(2, $extensions);
	}
}
