<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class FactoryContainer
{
	protected FileLocatorInterface $locator;

	protected array $loaders = [];
	protected array $mapLoader = [
		'yml' => YamlFileLoader::class,
		'xml' => XmlFileLoader::class,
		'php' => PhpFileLoader::class,
	];

	public function __construct()
	{
		$this->locator = new FileLocator;
	}

	/**
	 * @param string[] $configs
	 * @param ExtensionInterface[] $extensions
	 *
	 * @return ContainerBuilder
	 * @throws Exception
	 */
	public function create(array $configs, array $extensions = []): ContainerBuilder
	{
		$builder = $this->createBuilder();
		$this->registerExtensions($builder, $extensions);

		foreach ($configs as $config) {
			$loader = $this->getLoader($config, $builder);
			$loader->load($config);
		}

		$builder->compile(true);
		$this->resetLoaders();
		return $builder;
	}

	protected function resetLoaders(): void
	{
		$this->loaders = [];
	}

	protected function getLoader(string $config, ContainerBuilder $builder)
	{
		$ext = pathinfo($config, PATHINFO_EXTENSION);
		$ext = $ext === 'yaml' ? 'yml' : $ext;

		$classLoader = $this->mapLoader[$ext];
		$this->loaders[$classLoader] ??= $this->createLoader($classLoader, $builder, $this->locator);
		return $this->loaders[$classLoader];
	}

	protected function createBuilder(): ContainerBuilder
	{
		return new ContainerBuilder;
	}

	protected function registerExtensions(ContainerBuilder $builder, array $extensions = []): void
	{
		array_map(static function (ExtensionInterface $extension) use ($builder) {
			$builder->registerExtension($extension);
		}, $extensions);
	}

	protected function createLoader(
		string $classLoader,
		ContainerBuilder $builder,
		FileLocatorInterface $locator
	): LoaderInterface {
		return new $classLoader($builder, $locator);
	}
}