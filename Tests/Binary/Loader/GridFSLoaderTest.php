<?php

namespace Liip\ImagineBundle\Tests\Binary\Loader;

use Doctrine\MongoDB\GridFSFile;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Liip\ImagineBundle\Binary\Loader\GridFSLoader;

/**
 * @covers Liip\ImagineBundle\Binary\Loader\GridFSLoader<extended>
 */
class GridFSLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DocumentRepository
     */
    private $repo;

    /**
     * @var GridFSLoader
     */
    private $loader;

    public function setUp()
    {
        if (!extension_loaded('mongodb')) {
            $this->markTestSkipped('ext/mongodb is not installed');
        }
        if (!class_exists('Doctrine\MongoDB\GridFSFile')) {
            $this->markTestSkipped('doctrine mongo odm is not installed');
        }
        $this->repo = $this->getMockBuilder('Doctrine\ODM\MongoDB\DocumentRepository')->disableOriginalConstructor()->getMock();

        $dm = $this->getMockBuilder('Doctrine\ODM\MongoDB\DocumentManager')->disableOriginalConstructor()->getMock();
        $dm->expects($this->any())->method('getRepository')->with('\Foo\Bar')->will($this->returnValue($this->repo));

        $this->loader = new GridFSLoader($dm, '\Foo\Bar');
    }

    public function testFindWithValidDocument()
    {
        $image = new GridFSFile();
        $image->setBytes('01010101');

        $imageDocument = $this->getMock('ImageDocument', array('getFile'));
        $imageDocument
            ->expects($this->any())
            ->method('getFile')
            ->with()
            ->will($this->returnValue($image))
        ;

        $this->repo->expects($this->atLeastOnce())->method('find')->with($this->isInstanceOf('\MongoId'))->will($this->returnValue($imageDocument));

        $this->assertEquals('01010101', $this->loader->find('0123456789abcdef01234567'));
    }

    /**
     * @expectedException \Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException
     */
    public function testFindWithInValidDocument()
    {
        $this->repo->expects($this->atLeastOnce())->method('find')->with($this->isInstanceOf('\MongoId'))->will($this->returnValue(null));

        $this->loader->find('0123456789abcdef01234567');
    }
}
