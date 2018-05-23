<?php
/**
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
use Liip\ImagineBundle\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

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

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unknown filter provided.
     */
    public function testSetFiltersWithInvalidFilterThrowsException()
    {
        $this->model->setFilters(['not_a_filter']);
    }

    public function testSetFiltersWithValidFilterSuccess()
    {
        $filterMock = $this->createMock(FilterInterface::class);
        $this->model->setFilters([$filterMock]);
    }
}
