<?php

namespace Liip\ImagineBundle\Tests\Binary\Loader;

use Doctrine\Common\Persistence\ObjectRepository;
use Liip\ImagineBundle\Binary\Loader\AbstractDoctrineLoader;

/**
 * @covers Liip\ImagineBundle\Binary\Loader\AbstractDoctrineLoader<extended>
 */
class AbstractDoctrineLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectRepository
     */
    private $om;

    /**
     * @var AbstractDoctrineLoader
     */
    private $loader;

    public function setUp()
    {
        $this->om = $this->getMock('Doctrine\Common\Persistence\ObjectManager');

        $this->loader = $this->getMockBuilder('Liip\ImagineBundle\Binary\Loader\AbstractDoctrineLoader')->setConstructorArgs(array($this->om))->getMockForAbstractClass();
    }

    public function testFindWithValidObjectFirstHit()
    {
        $image = new \stdClass();

        $this->loader->expects($this->atLeastOnce())->method('mapPathToId')->with('/foo/bar')->will($this->returnValue(1337));
        $this->loader->expects($this->atLeastOnce())->method('getStreamFromImage')->with($image)->will($this->returnValue(fopen('data://text/plain,foo', 'r')));

        $this->om->expects($this->atLeastOnce())->method('find')->with(null, 1337)->will($this->returnValue($image));

        $this->assertEquals('foo', $this->loader->find('/foo/bar'));
    }

    public function testFindWithValidObjectSecondHit()
    {
        $image = new \stdClass();

        $this->loader->expects($this->atLeastOnce())->method('mapPathToId')->will($this->returnValueMap(array(
            array('/foo/bar.png', 1337),
            array('/foo/bar', 4711),
        )));

        $this->loader->expects($this->atLeastOnce())->method('getStreamFromImage')->with($image)->will($this->returnValue(fopen('data://text/plain,foo', 'r')));

        $this->om->expects($this->atLeastOnce())->method('find')->will($this->returnValueMap(array(
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
        $this->loader->expects($this->atLeastOnce())->method('mapPathToId')->with('/foo/bar')->will($this->returnValue(1337));
        $this->loader->expects($this->never())->method('getStreamFromImage');

        $this->om->expects($this->atLeastOnce())->method('find')->with(null, 1337)->will($this->returnValue(null));

        $this->loader->find('/foo/bar');
    }
}
