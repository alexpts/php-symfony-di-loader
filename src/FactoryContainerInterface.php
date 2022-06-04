<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader;

use Exception;
use Symfony\Component\DependencyInjection\ContainerBuilder;

interface FactoryContainerInterface
{
    /**
     * @param $builder $builder
     * @param string[] $configs
     *
     * @return ContainerBuilder
     * @throws Exception
     */
    public function build(ContainerBuilder $builder, array $configs): ContainerBuilder;

}