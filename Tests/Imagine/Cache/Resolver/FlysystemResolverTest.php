<?php

namespace Liip\ImagineBundle\Tests\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Imagine\Cache\Resolver\FlysystemResolver;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Tests\AbstractTest;
use League\Flysystem\Filesystem;
use Symfony\Component\Routing\RequestContext;

/**
 * @covers Liip\ImagineBundle\Imagine\Cache\Resolver\FlysystemResolver
 */
class FlysystemResolverTest extends AbstractTest
{
    public function setUp()
    {
        parent::setUp();

        if (!class_exists('\League\Flysystem\Filesystem')) {
            $this->markTestSkipped(
                'The league/flysystem PHP library is not available.'
            );
        }
    }

    public function testImplementsResolverInterface()
    {
        $rc = new \ReflectionClass('Liip\ImagineBundle\Imagine\Cache\Resolver\FlysystemResolver');

        $this->assertTrue($rc->implementsInterface('Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface'));
    }

    public function testResolveUriForFilter()
    {
        $fs = $this->getFlysystemMock();

        $resolver = new FlysystemResolver($fs, new RequestContext(), 'http://images.example.com');
        $uri = $resolver->resolve('/some-folder/path.jpg', 'thumb');
        $this->assertEquals('http://images.example.com/media/cache/thumb/some-folder/path.jpg', $uri);
    }

    public function testRemoveObjectsForFilter()
    {
        $expectedFilter = 'theFilter';
        $fs = $this->getFlysystemMock();
        $fs
            ->expects($this->once())
            ->method('deleteDir')
            ->with('media/cache/theFilter')
        ;

        $resolver = new FlysystemResolver($fs, new RequestContext(), 'http://images.example.com');
        $resolver->remove(array(), array($expectedFilter));
    }

    public function testCreateObjectInAdapter()
    {
        $binary = new Binary('aContent', 'image/jpeg', 'jpeg');

        $fs = $this->getFlysystemMock();
        $fs
            ->expects($this->once())
            ->method('put')
            ->will($this->returnValue(true))
        ;

        $resolver = new FlysystemResolver($fs, new RequestContext(), 'http://images.example.com');

        $this->assertNull($resolver->store($binary, 'thumb/foobar.jpg', 'thumb'));
    }

    public function testIsStoredChecksObjectExistence()
    {
        $fs = $this->getFlysystemMock();
        $fs
            ->expects($this->once())
            ->method('has')
            ->will($this->returnValue(false))
        ;

        $resolver = new FlysystemResolver($fs, new RequestContext(), 'http://images.example.com');

        $this->assertFalse($resolver->isStored('/some-folder/path.jpg', 'thumb'));
    }

    public function testReturnResolvedImageUrlOnResolve()
    {
        $fs = $this->getFlysystemMock();

        $resolver = new FlysystemResolver($fs, new RequestContext(), 'http://images.example.com');

        $this->assertEquals(
            'http://images.example.com/media/cache/thumb/some-folder/path.jpg',
            $resolver->resolve('/some-folder/path.jpg', 'thumb')
        );
    }

    public function testRemoveCacheForPathAndFilterOnRemove()
    {
        $fs = $this->getFlysystemMock();
        $fs
            ->expects($this->once())
            ->method('has')
            ->with('media/cache/thumb/some-folder/path.jpg')
            ->will($this->returnValue(true))
        ;
        $fs
            ->expects($this->once())
            ->method('delete')
            ->with('media/cache/thumb/some-folder/path.jpg')
            ->will($this->returnValue(true))
        ;

        $resolver = new FlysystemResolver($fs, new RequestContext(), 'http://images.example.com');

        $resolver->remove(array('some-folder/path.jpg'), array('thumb'));
    }

    public function testRemoveCacheForSomePathsAndFilterOnRemove()
    {
        $fs = $this->getFlysystemMock();
        $fs
            ->expects($this->at(0))
            ->method('has')
            ->with('media/cache/thumb/pathOne.jpg')
            ->will($this->returnValue(true))
        ;
        $fs
            ->expects($this->at(1))
            ->method('delete')
            ->with('media/cache/thumb/pathOne.jpg')
            ->will($this->returnValue(true))
        ;
        $fs
            ->expects($this->at(2))
            ->method('has')
            ->with('media/cache/thumb/pathTwo.jpg')
            ->will($this->returnValue(true))
        ;
        $fs
            ->expects($this->at(3))
            ->method('delete')
            ->with('media/cache/thumb/pathTwo.jpg')
            ->will($this->returnValue(true))
        ;

        $resolver = new FlysystemResolver($fs, new RequestContext(), 'http://images.example.com');

        $resolver->remove(
            array('pathOne.jpg', 'pathTwo.jpg'),
            array('thumb')
        );
    }

    public function testRemoveCacheForSomePathsAndSomeFiltersOnRemove()
    {
        $fs = $this->getFlysystemMock();
        $fs
            ->expects($this->at(0))
            ->method('has')
            ->with('media/cache/filterOne/pathOne.jpg')
            ->will($this->returnValue(true))
        ;
        $fs
            ->expects($this->at(1))
            ->method('delete')
            ->with('media/cache/filterOne/pathOne.jpg')
            ->will($this->returnValue(true))
        ;
        $fs
            ->expects($this->at(2))
            ->method('has')
            ->with('media/cache/filterTwo/pathOne.jpg')
            ->will($this->returnValue(true))
        ;
        $fs
            ->expects($this->at(3))
            ->method('delete')
            ->with('media/cache/filterTwo/pathOne.jpg')
            ->will($this->returnValue(true))
        ;
        $fs
            ->expects($this->at(4))
            ->method('has')
            ->with('media/cache/filterOne/pathTwo.jpg')
            ->will($this->returnValue(true))
        ;
        $fs
            ->expects($this->at(5))
            ->method('delete')
            ->with('media/cache/filterOne/pathTwo.jpg')
            ->will($this->returnValue(true))
        ;
        $fs
            ->expects($this->at(6))
            ->method('has')
            ->with('media/cache/filterTwo/pathTwo.jpg')
            ->will($this->returnValue(true))
        ;
        $fs
            ->expects($this->at(7))
            ->method('delete')
            ->with('media/cache/filterTwo/pathTwo.jpg')
            ->will($this->returnValue(true))
        ;

        $resolver = new FlysystemResolver($fs, new RequestContext(), 'http://images.example.com');

        $resolver->remove(
            array('pathOne.jpg', 'pathTwo.jpg'),
            array('filterOne', 'filterTwo')
        );
    }

    public function testDoNothingWhenObjectNotExistForPathAndFilterOnRemove()
    {
        $fs = $this->getFlysystemMock();
        $fs
            ->expects($this->once())
            ->method('has')
            ->with('media/cache/thumb/some-folder/path.jpg')
            ->will($this->returnValue(false))
        ;
        $fs
            ->expects($this->never())
            ->method('delete')
        ;

        $resolver = new FlysystemResolver($fs, new RequestContext(), 'http://images.example.com');
        $resolver->remove(array('some-folder/path.jpg'), array('thumb'));
    }

    public function testRemoveCacheForFilterOnRemove()
    {
        $expectedFilter = 'theFilter';

        $fs = $this->getFlysystemMock();
        $fs
            ->expects($this->once())
            ->method('deleteDir')
            ->with('media/cache/theFilter')
        ;

        $resolver = new FlysystemResolver($fs, new RequestContext(), 'http://images.example.com');

        $resolver->remove(array(), array($expectedFilter));
    }

    public function testRemoveCacheForSomeFiltersOnRemove()
    {
        $expectedFilterOne = 'theFilterOne';
        $expectedFilterTwo = 'theFilterTwo';

        $fs = $this->getFlysystemMock();
        $fs
            ->expects($this->at(0))
            ->method('deleteDir')
            ->with('media/cache/theFilterOne')
        ;
        $fs
            ->expects($this->at(1))
            ->method('deleteDir')
            ->with('media/cache/theFilterTwo')
        ;

        $resolver = new FlysystemResolver($fs, new RequestContext(), 'http://images.example.com');

        $resolver->remove(array(), array($expectedFilterOne, $expectedFilterTwo));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Filesystem
     */
    protected function getFlysystemMock()
    {
        $mockedMethods = array(
            'delete',
            'deleteDir',
            'has',
            'put',
            'remove',
        );

        return $this->getMock('League\Flysystem\Filesystem', $mockedMethods, array(), '', false);
    }
}
