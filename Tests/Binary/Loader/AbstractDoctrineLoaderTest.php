<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Binary\Loader;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Liip\ImagineBundle\Binary\Loader\AbstractDoctrineLoader;

/**
 * @covers \Liip\ImagineBundle\Binary\Loader\AbstractDoctrineLoader<extended>
 */
class AbstractDoctrineLoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ObjectRepository
     */
    private $om;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|AbstractDoctrineLoader
     */
    private $loader;

    public function setUp()
    {
        if (!interface_exists(ObjectManager::class)) {
            $this->markTestSkipped('Requires the doctrine/orm package.');
        }

        $this->om = $this
            ->getMockBuilder(ObjectManager::class)
            ->getMock();

        $this->loader = $this
            ->getMockBuilder(AbstractDoctrineLoader::class)
            ->setConstructorArgs(array($this->om))
            ->getMockForAbstractClass();
    }

    public function testFindWithValidObjectFirstHit()
    {
        $image = new \stdClass();

        $this->loader
            ->expects($this->atLeastOnce())
            ->method('mapPathToId')
            ->with('/foo/bar')
            ->will($this->returnValue(1337));

        $this->loader
            ->expects($this->atLeastOnce())
            ->method('getStreamFromImage')
            ->with($image)
            ->will($this->returnValue(fopen('data://text/plain,foo', 'r')));

        $this->om
            ->expects($this->atLeastOnce())
            ->method('find')
            ->with(null, 1337)
            ->will($this->returnValue($image));

        $this->assertEquals('foo', $this->loader->find('/foo/bar'));
    }

    public function testFindWithValidObjectSecondHit()
    {
        $image = new \stdClass();

        $this->loader
            ->expects($this->atLeastOnce())
            ->method('mapPathToId')
            ->will($this->returnValueMap(array(
                array('/foo/bar.png', 1337),
                array('/foo/bar', 4711),
            )));

        $this->loader
            ->expects($this->atLeastOnce())
            ->method('getStreamFromImage')
            ->with($image)
            ->will($this->returnValue(fopen('data://text/plain,foo', 'r')));

        $this->om
            ->expects($this->atLeastOnce())
            ->method('find')
            ->will($this->returnValueMap(array(
                array(null, 1337, null),
                array(null, 4711, $image),
            )));

        $this->assertEquals('foo', $this->loader->find('/foo/bar.png'));
    }

    /**
     * @expectedException \Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException
     */
    public function testFindWithInvalidObject()
    {
        $this->loader
            ->expects($this->atLeastOnce())
            ->method('mapPathToId')
            ->with('/foo/bar')
            ->will($this->returnValue(1337));

        $this->loader
            ->expects($this->never())
            ->method('getStreamFromImage');

        $this->om
            ->expects($this->atLeastOnce())
            ->method('find')
            ->with(null, 1337)
            ->will($this->returnValue(null));

        $this->loader->find('/foo/bar');
    }
}
