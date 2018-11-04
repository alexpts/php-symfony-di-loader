<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader;

use Symfony\Component\DependencyInjection\ContainerInterface;

interface LoaderContainerInterface
{
	public function getContainer(): ContainerInterface;
}