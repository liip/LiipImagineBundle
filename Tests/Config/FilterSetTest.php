<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Config;

use Liip\ImagineBundle\Config\FilterInterface;
use Liip\ImagineBundle\Config\Stack;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Liip\ImagineBundle\Config\Stack
 */
class FilterSetTest extends TestCase
{
    public function testSetFiltersWithInvalidFilterThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown filter provided.');

        $this->buildFilterSet(['not_a_filter']);
    }

    public function testSetFiltersWithValidFilterSuccess()
    {
        $filterMock = $this->createMock(FilterInterface::class);
        $stack = $this->buildFilterSet([$filterMock]);

        $this->assertInstanceOf(Stack::class, $stack);
        $this->assertSame('filter_name', $stack->getName());
        $this->assertSame('data_loader', $stack->getDataLoader());
        $this->assertSame(42, $stack->getQuality());
        $this->assertSame([$filterMock], $stack->getFilters());
    }

    /**
     * @param array $filters
     */
    private function buildFilterSet(array $filters): Stack
    {
        return new Stack('filter_name', 'data_loader', 42, $filters);
    }
}
