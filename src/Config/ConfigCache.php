<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader\Config;

use Symfony\Component\Config\ConfigCache as SymfonyConfigCache;
use Symfony\Component\Config\ResourceCheckerConfigCache;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use function count;

class ConfigCache extends SymfonyConfigCache
{
    /** @var string[] */
    protected array $configs = [];

    /**
     * @param string $file
     * @param bool $debug
     * @param string[] $configs
     */
    public function __construct(string $file, bool $debug, array $configs = [])
    {
        parent::__construct($file, $debug);
        $this->configs = $configs;
    }

    public function write(string $content, array $metadata = null)
    {
        parent::write($content, $metadata);

        if (null !== $metadata) {
            $path = $this->getMetaListConfigsPath();
            $filesystem = new Filesystem;
            $filesystem->dumpFile($path, serialize($this->configs));

            try {
                $filesystem->chmod($path, 0666, umask());
            } catch (IOException) {
                // discard chmod failure (some filesystem may not support it)
            }
        }
    }

    /**
     * Detect new fresh configs
     *
     * @return bool
     */
    public function isFresh(): bool
    {
        if (parent::isFresh() === false) {
            return false;
        }

        $existConfigsMeta = file_exists($this->getMetaListConfigsPath());
        if (!$existConfigsMeta) {
            return false;
        }

        if (!$this->isDebug() && is_file($this->getPath())) {
            return true;
        }

        $oldConfigs = $this->getOldConfigList();
        return $this->isSameConfigList($oldConfigs, $this->configs);
    }

    protected function getMetaListConfigsPath(): string
    {
        return $this->getPath() . '.meta.configs';
    }

    protected function getOldConfigList(): array
    {
        $data = $this->safelyUnserialize($this->getMetaListConfigsPath());
        return is_array($data) ? $data : [];
    }

    protected function isSameConfigList(array $oldConfigs, array $configs): bool
    {
        if (count($oldConfigs) !== count($configs)) {
            return false;
        }

        foreach ($configs as $file) {
            if (!in_array($file, $oldConfigs, true)) {
                return false;
            }
        }

        return true;
    }

    # privates parent scope
    protected function safelyUnserialize(string $file)
    {
        $fn = fn($path) => $this->safelyUnserialize($path);
        $fn = \Closure::bind($fn, $this, ResourceCheckerConfigCache::class);
        return $fn($file);
    }

    # privates parent scope
    protected function isDebug(): bool
    {
        $fn = fn() => $this->debug;
        $fn = \Closure::bind($fn, $this, SymfonyConfigCache::class);
        return $fn();
    }
}