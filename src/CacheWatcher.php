<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader;

use JsonException;
use RuntimeException;
use function json_decode;

class CacheWatcher
{
    /** @var string[] */
    protected array $watchFiles = [];
    protected bool $isWatchReflection = true;

    public function __construct(bool $isWatchReflection = true)
    {
        $this->setIsWatchReflection($isWatchReflection);
    }

    public function setIsWatchReflection(bool $isWatchReflection): static
    {
        $this->isWatchReflection = $isWatchReflection;
        return $this;
    }

    /**
     * @param string $fileCache
     * @param string[] $watchFiles
     *
     * @return bool
     * @throws JsonException
     */
    public function isActual(string $fileCache, array $watchFiles): bool
    {
        ['watch' => $watch, 'reflections' => $reflections] = $this->getMetaCache($fileCache . '.v2.meta');
        $watch = array_unique([...$watch, ...$this->getWatchFiles()]);

        if ($this->hasNewConfigFile($watchFiles, $watch)) {
            return false;
        }

        $lastUpdateTime = filemtime($fileCache);
        if ($this->isExpired($lastUpdateTime, $watch)) {
            return false;
        }
        if ($this->isWatchReflection && $this->isExpired($lastUpdateTime, $reflections)) {
            return false;
        }

        return true;
    }

    public function setWatchFiles(array $files): static
    {
        $this->watchFiles = $files;
        return $this;
    }

    public function addWatchFile(string $file): static
    {
        $this->watchFiles[] = $file;
        return $this;
    }

    public function getWatchFiles(): array
    {
        $this->watchFiles = array_unique($this->watchFiles);
        return array_filter($this->watchFiles, 'file_exists');
    }

    /**
     * @param string[] $watch
     * @param string[] $oldWatch
     *
     * @return bool
     */
    protected function hasNewConfigFile(array $watch, array $oldWatch): bool
    {
        foreach ($watch as $file) {
            if (!in_array($file, $oldWatch, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $fileMeta
     *
     * @return string[]
     * @throws JsonException
     */
    protected function getMetaCache(string $fileMeta): array
    {
        if (!file_exists($fileMeta)) {
            throw new RuntimeException('Can`t read meta for DI container');
        }

        $configs = file_get_contents($fileMeta);
        return json_decode($configs, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param int $cacheMtime
     * @param string[] $configs
     *
     * @return bool
     */
    public function isExpired(int $cacheMtime, array $configs): bool
    {
        foreach ($configs as $file) {
            $mTime = @filemtime($file);
            if ($mTime === false || $mTime > $cacheMtime) {
                return true;
            }
        }

        return false;
    }
}
