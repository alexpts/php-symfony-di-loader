<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader\Unit\LoaderContainer;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PTS\SymfonyDiLoader\LoaderContainer;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GetContainerTest extends TestCase
{
    /**
	 * @param bool $fromCache
	 *
     * @throws \ReflectionException
     * @throws \Exception
	 *
	 * @dataProvider dataProvider
     */
    public function testGetContainer(bool $fromCache): void
    {
    	/** @var ContainerInterface $container */
        $container = $this->getMockForAbstractClass(ContainerInterface::class);

		$cacheFile = '../../temp/cache.php';
		$configs = ['a.yml', 'b.yml'];

        /** @var MockObject|LoaderContainer $loader */
        $loader = $this->getMockBuilder(LoaderContainer::class)
            ->setConstructorArgs([$configs, $cacheFile])
            ->setMethods(['createContainer', 'tryGetContainerFromCache'])
            ->getMock();
		$loader->expects(self::once())->method('tryGetContainerFromCache')->willReturn($fromCache ? $container : null);
        $loader->expects(self::exactly($fromCache ? 0 : 1))->method('createContainer')->willReturn($container);

        $container = $loader->getContainer();
        self::assertInstanceOf(ContainerInterface::class, $container);

		$fromProcessMemory = $loader->getContainer();
		self::assertInstanceOf(ContainerInterface::class, $fromProcessMemory);
    }

    public function dataProvider(): array
	{
		return [
			'fromCache' => [true],
			'create' => [false],
		];
	}
}
