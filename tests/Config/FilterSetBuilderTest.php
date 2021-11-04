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
use Liip\ImagineBundle\Config\FilterInterface;
use Liip\ImagineBundle\Config\StackBuilder;
use Liip\ImagineBundle\Config\StackInterface;
use Liip\ImagineBundle\Factory\Config\FilterFactoryInterface;
use Liip\ImagineBundle\Factory\Config\StackFactory;
use Liip\ImagineBundle\Factory\Config\StackFactoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Liip\ImagineBundle\Config\StackBuilder
 */
class FilterSetBuilderTest extends TestCase
{
    /**
     * @var StackFactory|MockObject
     */
    private $filterSetFactoryMock;

    /**
     * @var FilterFactoryCollection|MockObject
     */
    private $filterFactoryCollectionMock;

    /**
     * @var StackBuilder
     */
    private $model;

    protected function setUp(): void
    {
        $this->filterSetFactoryMock = $this->createMock(StackFactoryInterface::class);
        $this->filterFactoryCollectionMock = $this->createMock(FilterFactoryCollection::class);
        $this->model = new StackBuilder($this->filterSetFactoryMock, $this->filterFactoryCollectionMock);
    }

    public function testBuildWithEmptyFilters(): void
    {
        $name = 'foo';
        $dataLoader = 'bar';
        $quality = 42;
        $filters = [];

        $filterSetMock = $this->createMock(StackInterface::class);

        $this->filterSetFactoryMock->expects($this->once())
            ->method('create')
            ->with($name, $dataLoader, $quality, $filters)
            ->willReturn($filterSetMock);

        $this->filterFactoryCollectionMock->expects($this->never())
            ->method('getFilterFactoryByName');

        $filterSet = $this->model->build($name, [
            'data_loader' => $dataLoader,
            'quality' => $quality,
            'filters' => $filters,
        ]);
        $this->assertSame($filterSetMock, $filterSet);
    }

    public function testBuildWithFilters(): void
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
        $filterSetMock = $this->createMock(StackInterface::class);

        $filterFactoryMock->expects($this->once())
            ->method('create')
            ->with($filterData)
            ->willReturn($filterMock);

        $this->filterSetFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($filterSetMock);

        $this->filterFactoryCollectionMock->expects($this->once())
            ->method('getFilterFactoryByName')
            ->with($filterCode)
            ->willReturn($filterFactoryMock);

        $filterSet = $this->model->build($name, [
            'data_loader' => $dataLoader,
            'quality' => $quality,
            'filters' => $filters,
        ]);
        $this->assertSame($filterSetMock, $filterSet);
    }
}
