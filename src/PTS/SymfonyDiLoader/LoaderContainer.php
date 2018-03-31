<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Exception\EnvParameterException;

class LoaderContainer implements LoaderContainerInterface
{
    /** @var array */
    protected $configFiles = [];

    /** @var FactoryContainer */
    protected $factory;
    /** @var ContainerInterface */
    protected $container;

    /** @var string */
    protected $cacheFile;
    /** @var bool */
    protected $checkExpired = true;

    /** @var string */
    protected $classContainer = 'AppContainer';


    public function __construct(array $configFiles = [], string $cacheFile, FactoryContainer $factory)
    {
        $this->configFiles = $configFiles;
        $this->cacheFile = $cacheFile;
        $this->factory = $factory;
    }

    public function setCheckExpired($checkExpired = true): self
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
            $container = $this->createContainer();
            $this->setContainer($container);
        }

        return $this->container;
    }

    public function setContainer(ContainerInterface $container): self
    {
        $this->container = $container;
        return $this;
    }

    protected function getFactory(): FactoryContainer
    {
        return $this->factory;
    }

    /**
     * @return ContainerInterface
     * @throws \Exception
     */
    protected function createContainer(): ContainerInterface
    {
        $appContainer = $this->getContainerFromCache($this->cacheFile, $this->configFiles);

        if (!($appContainer instanceof ContainerInterface)) {
            $appContainer = $this->getFactory()->create($this->configFiles);
            $appContainer->compile(true);
            $this->flushContainerToFile($this->cacheFile, $this->classContainer, $appContainer);
        }

        return $appContainer;
    }

    /**
     * @param string $filePath
     * @param string $className
     * @param ContainerBuilder $container
     *
     * @throws EnvParameterException
     */
    protected function flushContainerToFile(string $filePath, string $className, ContainerBuilder $container): void
    {
        $dumper = new PhpDumper($container);
        file_put_contents($filePath, $dumper->dump([
            'class' => $className,
        ]));
    }

    protected function getContainerFromCache($fileCache, $configs): ?Container
    {
        if (!file_exists($fileCache)) {
             return null;
        }

        if ($this->checkExpired && $this->isExpired($fileCache, $configs)) {
            return null;
        }

        require_once $fileCache;
        return new $this->classContainer;
    }

    /**
     * @param string $containerPath
     * @param string[] $configs
     *
     * @return bool
     */
    protected function isExpired(string $containerPath, array $configs): bool
    {
        $cacheTime = filemtime($containerPath);

        foreach ($configs as $config) {
            if ($cacheTime < filemtime($config)) {
                return true;
            }
        }

        return false;
    }
}
