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
use Liip\ImagineBundle\Config\FilterSet;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Liip\ImagineBundle\Config\FilterSet
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
        $this->buildFilterSet([$filterMock]);
    }

    /**
     * @param array $filters
     */
    private function buildFilterSet(array $filters)
    {
        new FilterSet('filter_name', 'data_loader', 42, $filters);
    }
}
