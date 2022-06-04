<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader;

use PTS\SymfonyDiLoader\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\ResourceCheckerConfigCache;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;

class LoaderContainer implements LoaderContainerInterface
{
    protected string $classContainer = 'AppContainer';
    protected FactoryContainerInterface $factory;

    public function __construct(
        protected FileLocatorInterface $locator = new FileLocator,
        protected ContainerBuilder $builder = new ContainerBuilder,
        FactoryContainerInterface $factory = null,
    ) {
        $this->factory = $factory ?? new FactoryContainer($this->locator);
    }

    public function getBuilder(): ContainerBuilder
    {
        return $this->builder;
    }

    public function getContainer(array $configFiles, string $cacheFile, bool $isDebug = false): ContainerInterface
    {
        $configCache = $this->getCacheConfig($configFiles, $cacheFile, $isDebug);

        if ($configCache->isFresh()) {
            require_once $cacheFile;
            return new $this->classContainer;
        }

        $container = $this->factory->build($this->builder, $configFiles);
        $this->dumpCache($configCache, $container);

        return $container;
    }

    protected function getCacheConfig(array $configFiles, string $cacheFile, bool $isDebug): ResourceCheckerConfigCache
    {
        return new ConfigCache($cacheFile, $isDebug, $configFiles);
    }

    protected function dumpCache(ResourceCheckerConfigCache $configCache, ContainerBuilder $container, bool $isDebug = false)
    {
        $phpDumper = new PhpDumper($container);
        $dump = $phpDumper->dump([
            'class' => $this->classContainer,
            'debug' => $isDebug,
        ]);

        $configCache->write($dump, $container->getResources());
    }
}
