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

use Liip\ImagineBundle\Config\FilterSetBuilderInterface;
use Liip\ImagineBundle\Config\FilterSetCollection;
use Liip\ImagineBundle\Config\FilterSetInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Liip\ImagineBundle\Config\FilterSetCollection
 */
class FilterSetCollectionTest extends TestCase
{
    public function testGetFilterSets()
    {
        $filterSetName = 'foo';
        $filterSetData = ['bar'];

        $filterSetMock = $this->createMock(FilterSetInterface::class);

        $filterSetBuilderMock = $this->createMock(FilterSetBuilderInterface::class);
        $filterSetBuilderMock->expects($this->once())
            ->method('build')
            ->with($filterSetName, $filterSetData)
            ->will($this->returnValue($filterSetMock));

        $model = new FilterSetCollection($filterSetBuilderMock, [$filterSetName => $filterSetData]);
        $this->assertSame([$filterSetMock], $model->getFilterSets());
        $this->assertSame([$filterSetMock], $model->getFilterSets());
    }
}
