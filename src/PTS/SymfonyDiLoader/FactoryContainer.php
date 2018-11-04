<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader;

use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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
	 *
	 * @return ContainerBuilder
	 * @throws \Exception
	 */
	public function create(array $configs): ContainerBuilder
	{
		$builder = $this->createBuilder();
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

	protected function createLoader(ContainerBuilder $builder, FileLocatorInterface $locator): LoaderInterface
	{
		$class = $this->classLoader;
		return new $class($builder, $locator);
	}
}