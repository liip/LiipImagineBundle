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

use Liip\ImagineBundle\Config\FilterFactoryCollection;
use Liip\ImagineBundle\Config\FilterFactoryCollectionInterface;
use Liip\ImagineBundle\Config\FilterInterface;
use Liip\ImagineBundle\Config\FilterSetBuilder;
use Liip\ImagineBundle\Config\FilterSetInterface;
use Liip\ImagineBundle\Factory\Config\FilterFactoryInterface;
use Liip\ImagineBundle\Factory\Config\FilterSetFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Liip\ImagineBundle\Config\FilterSetBuilder
 */
class FilterSetBuilderTest extends TestCase
{
    /**
     * @var FilterSetFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterSetFactoryMock;

    /**
     * @var FilterFactoryCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterFactoryCollectionMock;

    /**
     * @var FilterSetBuilder
     */
    private $model;

    protected function setUp()
    {
        $this->filterSetFactoryMock = $this->createMock(FilterSetFactory::class);
        $this->filterFactoryCollectionMock = $this->createMock(FilterFactoryCollectionInterface::class);
        $this->model = new FilterSetBuilder($this->filterSetFactoryMock, $this->filterFactoryCollectionMock);
    }

    public function testBuildWithEmptyFilters()
    {
        $name = 'foo';
        $dataLoader = 'bar';
        $quality = 42;
        $filters = [];

        $filterSetMock = $this->createMock(FilterSetInterface::class);

        $this->filterSetFactoryMock->expects($this->once())
            ->method('create')
            ->with($name, $dataLoader, $quality, $filters)
            ->will($this->returnValue($filterSetMock));

        $this->filterFactoryCollectionMock->expects($this->never())
            ->method('getFilterFactoryByName');

        $filterSet = $this->model->build($name, [
            'data_loader' => $dataLoader,
            'quality' => $quality,
            'filters' => $filters,
        ]);
        $this->assertSame($filterSetMock, $filterSet);
    }

    public function testBuildWithFilters()
    {
        $name = 'foo';
        $dataLoader = 'bar';
        $quality = 42;

        $filterCode = 'foo_filter';
        $filterData = ['foo_data'];
        $filters = [
            $filterCode => $filterData,
        ];

        $filterMock = $this->createMock(FilterInterface::class);
        $filterFactoryMock = $this->createMock(FilterFactoryInterface::class);
        $filterSetMock = $this->createMock(FilterSetInterface::class);

        $filterFactoryMock->expects($this->once())
            ->method('create')
            ->with($filterData)
            ->will($this->returnValue($filterMock));

        $this->filterSetFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($filterSetMock));

        $this->filterFactoryCollectionMock->expects($this->once())
            ->method('getFilterFactoryByName')
            ->with($filterCode)
            ->will($this->returnValue($filterFactoryMock));

        $filterSet = $this->model->build($name, [
            'data_loader' => $dataLoader,
            'quality' => $quality,
            'filters' => $filters,
        ]);
        $this->assertSame($filterSetMock, $filterSet);
    }
}
