<?php
declare(strict_types=1);

use Demo\DemoService;
use PTS\SymfonyDiLoader\LoaderContainer;

require '../vendor/autoload.php';
require_once 'DemoService.php';

$loader = new LoaderContainer;
$container = $loader->getContainer([__DIR__ . '/config/di.yml'], __DIR__ . '/var/di.cache.php');
$service = $container->get(DemoService::class);
