<?php
declare(strict_types=1);

use Demo\DemoService;
use PTS\SymfonyDiLoader\LoaderContainer;

require '../vendor/autoload.php';
require_once 'DemoService.php';

$loader = new LoaderContainer;

$configs =  [__DIR__ . '/config/di.yml', __DIR__ . './config/parameters.yml'];
$cacheFile = __DIR__ . '/var/di.php';

$container = $loader->getContainer($configs, $cacheFile, true);
$demoService = $container->get(DemoService::class);
