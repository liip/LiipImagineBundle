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

use Liip\ImagineBundle\Config\FilterBuilder;
use Liip\ImagineBundle\Config\FilterBuilderInterface;
use Liip\ImagineBundle\Config\FilterInterface;
use Liip\ImagineBundle\Config\FilterSetBuilder;
use Liip\ImagineBundle\Config\FilterSetInterface;
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
     * @var FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterBuilderMock;

    /**
     * @var FilterSetBuilder
     */
    private $model;

    protected function setUp()
    {
        $this->filterSetFactoryMock = $this->createMock(FilterSetFactory::class);
        $this->filterBuilderMock = $this->createMock(FilterBuilderInterface::class);
        $this->model = new FilterSetBuilder($this->filterSetFactoryMock, $this->filterBuilderMock);
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
            ->will($this->returnValue($filterSetMock));

        $filterSetMock->expects($this->once())
            ->method('setName')
            ->with($name);
        $filterSetMock->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($name));

        $filterSetMock->expects($this->once())
            ->method('setDataLoader')
            ->with($dataLoader);
        $filterSetMock->expects($this->once())
            ->method('getDataLoader')
            ->will($this->returnValue($dataLoader));

        $filterSetMock->expects($this->once())
            ->method('setQuality')
            ->with($quality);
        $filterSetMock->expects($this->once())
            ->method('getQuality')
            ->will($this->returnValue($quality));

        $this->filterBuilderMock->expects($this->never())
            ->method('build');

        $filterSet = $this->model->build($name, [
            'data_loader' => $dataLoader,
            'quality' => $quality,
            'filters' => $filters,
        ]);
        $this->assertSame($name, $filterSet->getName());
        $this->assertSame($dataLoader, $filterSet->getDataLoader());
        $this->assertSame($quality, $filterSet->getQuality());
        $this->assertSame($filters, $filterSet->getFilters());
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
        $filterSetMock = $this->createMock(FilterSetInterface::class);

        $this->filterSetFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($filterSetMock));

        $filterSetMock->expects($this->once())
            ->method('setName')
            ->with($name);
        $filterSetMock->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($name));

        $filterSetMock->expects($this->once())
            ->method('setDataLoader')
            ->with($dataLoader);
        $filterSetMock->expects($this->once())
            ->method('getDataLoader')
            ->will($this->returnValue($dataLoader));

        $filterSetMock->expects($this->once())
            ->method('setQuality')
            ->with($quality);
        $filterSetMock->expects($this->once())
            ->method('getQuality')
            ->will($this->returnValue($quality));

        $filterSetMock->expects($this->once())
            ->method('setFilters')
            ->with([$filterMock]);
        $filterSetMock->expects($this->once())
            ->method('getFilters')
            ->will($this->returnValue([$filterMock]));

        $this->filterBuilderMock->expects($this->once())
            ->method('build')
            ->with($filterCode, $filterData)
            ->will($this->returnValue($filterMock));

        $filterSet = $this->model->build($name, [
            'data_loader' => $dataLoader,
            'quality' => $quality,
            'filters' => $filters,
        ]);
        $this->assertSame($name, $filterSet->getName());
        $this->assertSame($dataLoader, $filterSet->getDataLoader());
        $this->assertSame($quality, $filterSet->getQuality());
        $this->assertSame([$filterMock], $filterSet->getFilters());
    }
}
