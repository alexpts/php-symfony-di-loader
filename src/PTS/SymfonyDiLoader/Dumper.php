<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader;

use ReflectionClass;
use RuntimeException;
use Symfony\Component\Config\Resource\ComposerResource;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Config\Resource\ReflectionClassResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\Filesystem\Filesystem;
use Throwable;

class Dumper
{
	protected Filesystem $fs;

	public function __construct(Filesystem $fs = null)
	{
		$this->fs = $fs ?? new Filesystem;
	}

	public function dump(string $filePath, string $className, ContainerBuilder $container): void
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

	public function dumpMeta(string $filePath, ContainerBuilder $container, CacheWatcher $cacheWatcher): void
	{
		$watched = $this->getAllWatchFiles($container, $cacheWatcher);

		try {
			$this->fs->dumpFile($filePath, json_encode($watched, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
		} catch (Throwable $throwable) {
			throw new RuntimeException('Can`t dump meta for DI container', 0, $throwable);
		}
	}

	protected function getAllWatchFiles(ContainerBuilder $container, CacheWatcher $cacheWatcher): array
	{
		$reflectionExtractor = fn(): ReflectionClass => $this->classReflector;

		$watch = [];
		$reflections = [];

		foreach ($container->getResources() as $resource) {
			if ($resource instanceof ReflectionClassResource) {
				$reflection = $reflectionExtractor->call($resource);
				$reflections[] = $reflection->getFileName();
				continue;
			}

			if ($resource instanceof FileResource) {
				$watch[] = (string)$resource;
				continue;
			}

			if ($resource instanceof ComposerResource) {
				foreach ($resource->getVendors() as $vendorDir) {
					$autoloadFiles = [
						$vendorDir . '/autoload.php',
						$vendorDir . '/composer/autoload_classmap.php',
						$vendorDir . '/composer/autoload_files.php',
						$vendorDir . '/composer/autoload_namespaces.php',
						$vendorDir . '/composer/autoload_psr4.php',
						$vendorDir . '/composer/autoload_real.php',
						$vendorDir . '/composer/autoload_static.php',
					];
					array_push($watch, ...$autoloadFiles);
				}
				continue;
			}
		}

		array_push($watch, ...$cacheWatcher->getWatchFiles());
		$watch = array_unique($watch);
		$reflections = array_unique($reflections);

		return [
			'watch' => $watch,
			'reflections' => $reflections
		];
	}
}
