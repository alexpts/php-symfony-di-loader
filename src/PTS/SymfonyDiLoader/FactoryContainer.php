<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader;

use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

class FactoryContainer
{
	/** @var FileLocatorInterface */
	protected $locator;
	/** @var string */
	protected $classLoader;

	public function __construct(string $classLoader, FileLocatorInterface $locator)
	{
		$this->classLoader = $classLoader;
		$this->locator = $locator;
	}

	/**
	 * @param string[] $configs
	 * @param ExtensionInterface[] $extensions
	 *
	 * @return ContainerBuilder
	 * @throws \Exception
	 */
	public function create(array $configs, array $extensions = []): ContainerBuilder
	{
		$builder = $this->createBuilder();
		$this->registerExtensions($builder, $extensions);
		$loader = $this->createLoader($builder, $this->locator);

		foreach ($configs as $config) {
			$loader->load($config);
		}

		$builder->compile(true);
		return $builder;
	}

	protected function createBuilder(): ContainerBuilder
	{
		return new ContainerBuilder;
	}

	protected function registerExtensions(ContainerBuilder $builder, array $extensions = []): void
    {
        array_map(function(ExtensionInterface $extension) use ($builder) {
            $builder->registerExtension($extension);
        }, $extensions);
    }

	protected function createLoader(ContainerBuilder $builder, FileLocatorInterface $locator): LoaderInterface
	{
		$class = $this->classLoader;
		return new $class($builder, $locator);
	}
}