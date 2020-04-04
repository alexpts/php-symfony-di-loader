<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader;

use RuntimeException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Filesystem\Filesystem;
use Throwable;

class LoaderContainer implements LoaderContainerInterface
{

	protected CacheWatcher $cacheWatcher;
	protected ?Filesystem $fs = null;
	protected FactoryContainer $factory;
	protected ?ContainerInterface $container = null;
	/** @var ExtensionInterface[] */
	protected array $extensions = [];

	protected bool $checkExpired = true;

	protected string $classContainer = 'AppContainer';

	public function __construct(FactoryContainer $factory = null, CacheWatcher $cacheWatcher = null)
	{
		$this->factory = $factory ?? new FactoryContainer(YamlFileLoader::class, new FileLocator);
		$this->cacheWatcher = $cacheWatcher ?? new CacheWatcher;
		$this->fs = new Filesystem;
	}

	public function getContainer(array $configFiles, string $cacheFile): ContainerInterface
	{
		if ($this->container === null) {
			$this->container = $this->tryGetContainerFromCache($configFiles, $cacheFile)
				?? $this->generateContainer($configFiles, $cacheFile);
			$this->ready();
		}

		return $this->container;
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

	protected function generateContainer(array $configFiles, string $cacheFile): ContainerInterface
	{
		$container = $this->createContainer($configFiles, $this->extensions);
		$this->dump($cacheFile, $this->classContainer, $container);
		$this->dumpMeta($cacheFile . '.meta', $configFiles);

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
			$this->fs->dumpFile($filePath, serialize($configFiles));
		} catch (Throwable $throwable) {
			throw new RuntimeException('Can`t dump meta for DI container', 0, $throwable);
		}
	}

	protected function dump(string $filePath, string $className, ContainerBuilder $container): void
	{
		$dumper = new PhpDumper($container);

		try {
			$this->fs->dumpFile($filePath, $dumper->dump([
				'class' => $className,
			]));
		} catch (Throwable $throwable) {
			throw new RuntimeException('Can`t dump cache for DI container', 0, $throwable);
		}
	}

	/**
	 * @param string $fileCache
	 * @param string[] $configs
	 *
	 * @return null|ContainerInterface
	 */
	protected function tryGetContainerFromCache(array $configs, string $fileCache): ?ContainerInterface
	{
		if (!file_exists($fileCache)) {
			return null;
		}

		if ($this->checkExpired && !$this->cacheWatcher->isActualCache($fileCache, $configs)) {
			return null;
		}

		require_once $fileCache;
		return new $this->classContainer;
	}

	protected function ready(): void
	{
		unset($this->cacheWatcher, $this->factory, $this->fs);
		$this->extensions = [];
	}
}
