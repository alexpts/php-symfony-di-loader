<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\ComposerResource;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class FactoryContainer implements FactoryContainerInterface
{
    protected FileLocatorInterface $locator;

    /**
     * @var LoaderInterface[]
     */
    protected array $loaders = [];

    public function __construct(FileLocatorInterface $locator = null)
    {
        $this->locator = $locator ?? new FileLocator;
    }

    /**
     * @param $builder $builder
     * @param string[] $configs
     *
     * @return ContainerBuilder
     * @throws Exception
     */
    public function build(ContainerBuilder $builder, array $configs): ContainerBuilder
    {
        foreach ($configs as $config) {
            $loader = $this->getLoader($config, $builder);
            $loader->load($config);
        }

        $builder->compile(true);

        $this->addVendorsResources($builder);
        $this->resetLoaders();

        return $builder;
    }

    protected function addVendorsResources(ContainerBuilder $builder): void
    {
        $vendors = (new ComposerResource)->getVendors();
        foreach ($vendors as $vendor) {
            $builder->addResource(new FileResource($vendor . '/composer/installed.json'));
        }
    }

    protected function resetLoaders(): void
    {
        $this->loaders = [];
    }

    protected function getLoader(string $config, ContainerBuilder $builder): LoaderInterface
    {
        $fileExt = pathinfo($config, PATHINFO_EXTENSION);

        $class = match ($fileExt) {
            'php' => PhpFileLoader::class,
            'xml' => XmlFileLoader::class,
            default => YamlFileLoader::class,
        };

        $this->loaders[$class] ??= new $class($builder, $this->locator);
        return $this->loaders[$class];
    }
}