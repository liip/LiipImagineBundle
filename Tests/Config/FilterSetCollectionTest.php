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

use Liip\ImagineBundle\Config\StackBuilderInterface;
use Liip\ImagineBundle\Config\StackCollection;
use Liip\ImagineBundle\Config\StackInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Liip\ImagineBundle\Config\StackCollection
 */
class FilterSetCollectionTest extends TestCase
{
    public function testGetFilterSets()
    {
        $filterSetName = 'foo';
        $filterSetData = ['bar'];

        $filterSetMock = $this->createMock(StackInterface::class);

        $stackBuilderMock = $this->createMock(StackBuilderInterface::class);
        $stackBuilderMock->expects($this->once())
            ->method('build')
            ->with($filterSetName, $filterSetData)
            ->willReturn($filterSetMock);

        $model = new StackCollection($stackBuilderMock, [$filterSetName => $filterSetData]);
        $this->assertSame([$filterSetMock], $model->getStacks());
        $this->assertSame([$filterSetMock], $model->getStacks());
    }
}
