<?php
declare(strict_types=1);

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function(ContainerConfigurator $configurator) {
    $configurator->parameters()
        ->set('php', 'php');

    $services = $configurator->services()
        ->defaults()
        ->autowire(true)
        ->autoconfigure(false);

    $services->set(FileLocator::class);
};