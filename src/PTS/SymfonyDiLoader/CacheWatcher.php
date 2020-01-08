<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader;

use RuntimeException;
use function count;

class CacheWatcher
{
	/**
	 * @param string $fileCache
	 * @param string[] $configs
	 *
	 * @return bool
	 */
	public function isActualCache(string $fileCache, array $configs): bool
	{
		$oldConfigs = $this->getMetaCache($fileCache . '.meta');
        $configs = array_unique($configs);

		if (count($oldConfigs) !== count($configs)) {
			return false;
		}

		return array_diff($oldConfigs, $configs)
            ? false
            : !$this->isExpired($fileCache, $configs);
	}

	/**
	 * @param string $fileMeta
	 *
	 * @return string[]
	 */
	protected function getMetaCache(string $fileMeta): array
	{
		if (!file_exists($fileMeta)) {
			throw new RuntimeException('Can`t read meta for DI container');
		}

		$configs = file_get_contents($fileMeta);
		return unserialize($configs);
	}

	/**
	 * @param string $fileCache
	 * @param string[] $configs
	 *
	 * @return bool
	 */
	public function isExpired(string $fileCache, array $configs): bool
	{
		$cacheTime = filemtime($fileCache);

		foreach ($configs as $config) {
			if ($cacheTime < filemtime($config)) {
				return true;
			}
		}

		return false;
	}
}
