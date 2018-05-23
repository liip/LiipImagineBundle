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
    /**
     * @var FilterSet
     */
    private $model;

    protected function setUp()
    {
        $this->model = new FilterSet();
    }

    public function testSetFiltersWithInvalidFilterThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown filter provided.');

        $this->model->setFilters(['not_a_filter']);
    }

    public function testSetFiltersWithValidFilterSuccess()
    {
        $filterMock = $this->createMock(FilterInterface::class);
        $this->model->setFilters([$filterMock]);
    }
}
