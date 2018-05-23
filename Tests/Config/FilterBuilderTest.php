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

use Liip\ImagineBundle\Config\FilterBuilder;
use Liip\ImagineBundle\Config\FilterInterface;
use Liip\ImagineBundle\Factory\Config\FilterFactory;
use PHPUnit\Framework\TestCase;

class FilterBuilderTest extends TestCase
{
    /**
     * @var FilterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterFactoryMock;

    /**
     * @var FilterBuilder
     */
    private $model;

    protected function setUp()
    {
        $this->filterFactoryMock = $this->createMock(FilterFactory::class);
        $this->model = new FilterBuilder($this->filterFactoryMock);
    }

    public function testBuild()
    {
        $name = 'foo';
        $options = ['bar'];

        $filterMock = $this->createMock(FilterInterface::class);

        $this->filterFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($filterMock));

        $filterMock->expects($this->once())
            ->method('setName')
            ->with($name);

        $filterMock->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($name));

        $filterMock->expects($this->once())
            ->method('setOptions')
            ->with($options);

        $filterMock->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($options));

        $filter = $this->model->build($name, $options);
        $this->assertEquals($name, $filter->getName());
        $this->assertEquals($options, $filter->getOptions());
    }
}
