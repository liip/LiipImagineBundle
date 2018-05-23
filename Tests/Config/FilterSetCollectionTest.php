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

use Liip\ImagineBundle\Config\FilterSetBuilderInterface;
use Liip\ImagineBundle\Config\FilterSetCollection;
use Liip\ImagineBundle\Config\FilterSetInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use PHPUnit\Framework\TestCase;

class FilterSetCollectionTest extends TestCase
{
    /**
     * @var FilterConfiguration|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterConfigurationMock;

    /**
     * @var FilterSetBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterSetBuilderMock;

    /**
     * @var FilterSetCollection
     */
    private $model;

    protected function setUp()
    {
        $this->filterConfigurationMock = $this->createMock(FilterConfiguration::class);
        $this->filterSetBuilderMock = $this->createMock(FilterSetBuilderInterface::class);

        $this->model = new FilterSetCollection($this->filterConfigurationMock, $this->filterSetBuilderMock);
    }

    public function testGetFilterSets()
    {
        $filterSetName = 'foo';
        $filterSetData = ['bar'];

        $filterSetMock = $this->createMock(FilterSetInterface::class);

        $this->filterConfigurationMock->expects($this->once())
            ->method('all')
            ->will($this->returnValue([$filterSetName => $filterSetData]));

        $this->filterSetBuilderMock->expects($this->once())
            ->method('build')
            ->with($filterSetName, $filterSetData)
            ->will($this->returnValue($filterSetMock));

        $this->assertEquals([$filterSetMock], $this->model->getFilterSets());
        $this->assertEquals([$filterSetMock], $this->model->getFilterSets());
    }
}
