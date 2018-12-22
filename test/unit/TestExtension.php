<?php

namespace PTS\SymfonyDiLoader\Unit;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

class TestExtension implements ExtensionInterface
{

    /**
     * @inheritdoc
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $container->setParameter('test', true);
    }

    /**
     * @inheritdoc
     */
    public function getNamespace()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getXsdValidationBasePath()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getAlias()
    {
        return 'test';
    }
}