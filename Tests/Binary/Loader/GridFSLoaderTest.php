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

use Doctrine\MongoDB\GridFSFile;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Liip\ImagineBundle\Binary\Loader\GridFSLoader;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers \Liip\ImagineBundle\Binary\Loader\GridFSLoader<extended>
 */
class GridFSLoaderTest extends AbstractTest
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|DocumentRepository
     */
    private $repo;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|GridFSLoader
     */
    private $loader;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|DocumentManager
     */
    private $dm;

    public function setUp()
    {
        if (!extension_loaded('mongodb')) {
            $this->markTestSkipped('Requires the mongodb extension.');
        }

        if (!class_exists('\Doctrine\MongoDB\GridFSFile')) {
            $this->markTestSkipped('Requires the Doctrine mongo ODM.');
        }

        $this->repo = $this->createObjectMock('\Doctrine\ODM\MongoDB\DocumentRepository');
        $this->dm = $this->createObjectMock('\Doctrine\ODM\MongoDB\DocumentManager');
        $this->dm
            ->expects($this->any())
            ->method('getRepository')
            ->with('\Foo\Bar')
            ->will($this->returnValue($this->repo));
        $this->loader = new GridFSLoader($this->dm, '\Foo\Bar');
    }

    public function testFindWithValidDocument()
    {
        $image = new GridFSFile();
        $image->setBytes('01010101');

        $imageDocument = $this->createObjectMock('ImageDocument', array('getFile'));
        $imageDocument
            ->expects($this->any())
            ->method('getFile')
            ->with()
            ->will($this->returnValue($image));

        $this->repo
            ->expects($this->atLeastOnce())
            ->method('find')
            ->with($this->isInstanceOf('\MongoId'))
            ->will($this->returnValue($imageDocument));

        $this->assertEquals('01010101', $this->loader->find('0123456789abcdef01234567'));
    }

    /**
     * @expectedException \Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException
     */
    public function testFindWithInValidDocument()
    {
        $this->repo
            ->expects($this->atLeastOnce())
            ->method('find')
            ->with($this->isInstanceOf('\MongoId'))
            ->will($this->returnValue(null));

        $this->loader->find('0123456789abcdef01234567');
    }
}
