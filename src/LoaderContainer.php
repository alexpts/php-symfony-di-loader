<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader;

use JsonException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

class LoaderContainer implements LoaderContainerInterface
{
    protected CacheWatcher $cacheWatcher;
    protected FactoryContainer $factory;
    protected Dumper $dumper;
    protected ?ContainerInterface $container = null;
    /** @var ExtensionInterface[] */
    protected array $extensions = [];
    protected bool $checkExpired = true;
    protected string $classContainer = 'AppContainer';

    public function __construct(FactoryContainer $factory = null, CacheWatcher $cacheWatcher = null)
    {
        $this->factory = $factory ?? new FactoryContainer;
        $this->cacheWatcher = $cacheWatcher ?? new CacheWatcher;
        $this->dumper = new Dumper;
    }

    public function getWatcher(): CacheWatcher
    {
        return $this->cacheWatcher;
    }

    public function getContainer(array $configFiles, string $cacheFile): ContainerInterface
    {
        if ($this->container === null) {
            $this->container = $this->tryGetContainerFromCache($configFiles, $cacheFile)
                ?? $this->generateContainer($configFiles, $cacheFile);
        }

        return $this->container;
    }

    public function addExtension(ExtensionInterface $extension): static
    {
        $this->extensions[] = $extension;
        return $this;
    }

    public function setCheckExpired(bool $checkExpired = true): static
    {
        $this->checkExpired = $checkExpired;
        return $this;
    }

    public function clearProcessCache(): static
    {
        $this->container = null;
        return $this;
    }

    protected function generateContainer(array $configFiles, string $cacheFile): ContainerInterface
    {
        $container = $this->factory->create($configFiles, $this->extensions);
        $this->dumper->dump($cacheFile, $this->classContainer, $container);
        $this->dumper->dumpMeta($cacheFile . '.v2.meta', $container, $this->getWatcher());

        return $container;
    }

    /**
     * @param string $fileCache
     * @param string[] $configs
     *
     * @return null|ContainerInterface
     * @throws JsonException
     */
    protected function tryGetContainerFromCache(array $configs, string $fileCache): ?ContainerInterface
    {
        if (!file_exists($fileCache)) {
            return null;
        }

        if ($this->checkExpired && !$this->cacheWatcher->isActual($fileCache, $configs)) {
            return null;
        }

        require_once $fileCache;
        return new $this->classContainer;
    }
}
