<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader\Unit\CacheWatcher;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PTS\SymfonyDiLoader\CacheWatcher;

class IsActualCacheTest extends TestCase
{

	/**
     * @param bool $expected
     * @param string[] $configs
     * @param string[] $meta
     * @param bool|null $isExpired
     *
     * @dataProvider dataProviderIsExpired
     * @throws \Exception
     */
    public function testIsActualCache(bool $expected, array $configs, array $meta, bool $isExpired = null): void
    {
    	/** @var MockObject|CacheWatcher $watcher */
    	$watcher = $this->getMockBuilder(CacheWatcher::class)
			->setMethods(['getMetaCache', 'isExpired'])
			->getMock();
		$watcher->method('getMetaCache')->willReturn($meta);
		$watcher->expects(self::exactly($isExpired === null ? 0 : 1))->method('isExpired')->willReturn($isExpired);

		$actual = $watcher->isActualCache('pathToFileCache', $configs);
        self::assertSame($expected, $actual);
    }

    public function dataProviderIsExpired(): array
    {
        return [
            'different config count' => [
            	false,
				['conf1.yml', 'new.yml'],
				['conf1.yml'],
			],
			'different config count #2' => [
				false,
				['conf1.yml'],
				['conf1.yml', 'conf2.yml'],
			],
			'different configs' => [
				false,
				['conf1.yml', 'conf3.yml'],
				['conf1.yml', 'conf2.yml'],
			],
			'different order configs (fresh)' => [
				true,
				['conf2.yml', 'conf1.yml'],
				['conf1.yml', 'conf2.yml'],
				false
			],
			'different order configs and (expired)' => [
				false,
				['conf2.yml', 'conf1.yml'],
				['conf1.yml', 'conf2.yml'],
				true
			],
        ];
    }
}