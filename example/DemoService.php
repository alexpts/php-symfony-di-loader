<?php
declare(strict_types=1);

namespace Demo;

use PTS\SymfonyDiLoader\CacheWatcher;

class DemoService
{
	/** @required-own */
	protected CacheWatcher $watcher;
}