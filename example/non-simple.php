<?php
declare(strict_types=1);

use Demo\DemoService;
use PTS\SymfonyDiLoader\LoaderContainer;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;

require '../vendor/autoload.php';
require_once 'DemoService.php';

$locator = new FileLocator([
    __DIR__,
    __DIR__ . '/config'
]);

$configs = [
    './config/di.yml', // 1 locator dir
    'parameters.yml', // 2 locator dir
];

$builder = new ContainerBuilder; // builder from any application
$builder->setParameter('symfony', 'flex');
// $builder->registerExtension(...); // custom params, bundles config, example: router, validator, etc

$loader = new LoaderContainer($locator, $builder);
$cacheFile = __DIR__ . '/var/di.php';

$container = $loader->getContainer($configs, $cacheFile, true);
$demoService = $container->get(DemoService::class);
$param = $container->getParameter('symfony');
