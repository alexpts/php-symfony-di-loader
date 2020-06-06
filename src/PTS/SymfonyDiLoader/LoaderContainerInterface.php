<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

interface LoaderContainerInterface
{
	public function getContainer(array $configFiles, string $cacheFile): ContainerInterface;

	public function addExtension(ExtensionInterface $extension): self;

	public function setCheckExpired(bool $checkExpired = true): self;
}