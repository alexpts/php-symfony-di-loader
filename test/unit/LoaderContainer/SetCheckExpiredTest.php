<?php
declare(strict_types=1);

namespace PTS\SymfonyDiLoader\Unit\LoaderContainer;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PTS\SymfonyDiLoader\LoaderContainer;
use ReflectionException;
use ReflectionProperty;

class SetCheckExpiredTest extends TestCase
{
    /**
     * @param bool $expected
     * @param bool $value
     *
     * @throws ReflectionException
     *
     * @dataProvider dataProviderCheckExpired
     */
    public function testSetCheckExpired(bool $expected, bool $value): void
    {
        /** @var MockObject|LoaderContainer $loader */
        $loader = $this->getMockBuilder(LoaderContainer::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['setCheckExpired'])
            ->getMock();

        $actual = $loader->setCheckExpired($value);
        self::assertInstanceOf(LoaderContainer::class, $actual);

        $checkExpired = new ReflectionProperty(LoaderContainer::class, 'checkExpired');
        $checkExpired->setAccessible(true);
        $actual = $checkExpired->getValue($loader);
        self::assertSame($expected, $actual);
    }

    public function dataProviderCheckExpired(): array
    {
        return [
            [true, true],
            [false, false]
        ];
    }
}
