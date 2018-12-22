<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Exception\EnvParameterException;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class LoaderContainer implements LoaderContainerInterface
{
	/** @var array */
	protected $configFiles = [];

	/** @var FactoryContainer */
	protected $factory;
	/** @var ContainerInterface */
	protected $container;
	/** @var CacheWatcher */
	protected $cacheWatcher;
	/** @var ExtensionInterface[] */
	protected $extensions = [];

	/** @var string */
	protected $cacheFile = '';
	/** @var bool */
	protected $checkExpired = true;

	/** @var string */
	protected $classContainer = 'AppContainer';

	/**
	 * @param string[] $configFiles
	 * @param string $cacheFile
	 * @param FactoryContainer|null $factory
	 */
	public function __construct(array $configFiles, string $cacheFile, FactoryContainer $factory = null)
	{
		$this->configFiles = $configFiles;
		$this->cacheFile = $cacheFile;
		$this->factory = $factory ?? new FactoryContainer(YamlFileLoader::class, new FileLocator);
		$this->cacheWatcher = new CacheWatcher;
	}

	public function addExtension(ExtensionInterface $extension): self
    {
        $this->extensions[] = $extension;
        return $this;
    }

	public function setCheckExpired(bool $checkExpired = true): self
	{
		$this->checkExpired = $checkExpired;
		return $this;
	}

	/**
	 * @return ContainerInterface
	 * @throws \Exception
	 */
	public function getContainer(): ContainerInterface
	{
		if ($this->container === null) {
            $container = $this->tryGetContainerFromCache($this->cacheFile, $this->configFiles);
            $this->container = $container ?? $this->generateContainer();
		}

		return $this->container;
	}

	protected function generateContainer(): ContainerInterface
    {
        $container = $this->createContainer($this->configFiles, $this->extensions);
        $this->dump($this->cacheFile, $this->classContainer, $container);
        $this->dumpMeta($this->cacheFile . '.meta', $this->configFiles);

        return $container;
    }

	protected function createContainer(array $configs, array $extensions = []): ContainerBuilder
	{
		return $this->factory->create($configs, $extensions);
	}

	/**
	 * @param string $filePath
	 * @param string[] $configFiles
	 */
	protected function dumpMeta(string $filePath, array $configFiles): void
	{
		try {
			file_put_contents($filePath, serialize($configFiles));
		} catch (\Throwable $throwable) {
			throw new \RuntimeException('Can`t dump meta for DI container', 0, $throwable);
		}
	}

	/**
	 * @param string $filePath
	 * @param string $className
	 * @param ContainerBuilder $container
	 *
	 * @throws EnvParameterException
	 */
	protected function dump(string $filePath, string $className, ContainerBuilder $container): void
	{
		$dumper = new PhpDumper($container);

		try {
			file_put_contents($filePath, $dumper->dump([
				'class' => $className,
			]));
		} catch (\Throwable $throwable) {
			throw new \RuntimeException('Can`t dump cache for DI container', 0, $throwable);
		}
	}

	/**
	 * @param string $fileCache
	 * @param string[] $configs
	 *
	 * @return null|ContainerInterface
	 */
	protected function tryGetContainerFromCache(string $fileCache, array $configs): ?ContainerInterface
	{
		if (!file_exists($fileCache)) {
			return null;
		}

		if ($this->checkExpired && !$this->getWatcher()->isActualCache($fileCache, $configs)) {
			return null;
		}

		require_once $fileCache;
		return new $this->classContainer;
	}

	protected function getWatcher(): CacheWatcher
	{
		return $this->cacheWatcher;
	}
}
