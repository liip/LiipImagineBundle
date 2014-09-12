<?php

namespace Liip\ImagineBundle\Tests\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Imagine\Cache\Resolver\WebPathResolver;
use Liip\ImagineBundle\Model\Binary;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\RequestContext;

/**
 * @covers Liip\ImagineBundle\Imagine\Cache\Resolver\WebPathResolver
 */
class WebPathResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testImplementsResolverInterface()
    {
        $rc = new \ReflectionClass('Liip\ImagineBundle\Imagine\Cache\Resolver\WebPathResolver');

        $this->assertTrue($rc->implementsInterface('Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface'));
    }

    public function testCouldBeConstructedWithRequiredArguments()
    {
        $filesystemMock = $this->createFilesystemMock();
        $requestContext = new RequestContext;
        $webRoot = 'theWebRoot';

        $resolver = new WebPathResolver($filesystemMock, $requestContext, $webRoot);

        $this->assertAttributeSame($filesystemMock, 'filesystem', $resolver);
        $this->assertAttributeSame($requestContext, 'requestContext', $resolver);
        $this->assertAttributeSame($webRoot, 'webRoot', $resolver);
    }

    public function testCouldBeConstructedWithOptionalArguments()
    {
        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            new RequestContext,
            'aWebRoot',
            'theCachePrefix'
        );

        $this->assertAttributeSame('theCachePrefix', 'cachePrefix', $resolver);
    }

    public function testTrimRightSlashFromWebPathOnConstruct()
    {
        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            new RequestContext,
            'aWebRoot/',
            'theCachePrefix'
        );

        $this->assertAttributeSame('aWebRoot', 'webRoot', $resolver);
    }

    public function testRemoveDoubleSlashFromWebRootOnConstruct()
    {
        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            new RequestContext,
            'aWeb//Root',
            '/aCachePrefix'
        );

        $this->assertAttributeSame('aWeb/Root', 'webRoot', $resolver);
    }

    public function testTrimRightSlashFromCachePrefixOnConstruct()
    {
        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            new RequestContext,
            'aWebRoot',
            '/aCachePrefix'
        );

        $this->assertAttributeSame('aCachePrefix', 'cachePrefix', $resolver);
    }

    public function testRemoveDoubleSlashFromCachePrefixOnConstruct()
    {
        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            new RequestContext,
            'aWebRoot',
            'aCache//Prefix'
        );

        $this->assertAttributeSame('aCache/Prefix', 'cachePrefix', $resolver);
    }

    public function testReturnTrueIfFileExistsOnIsStore()
    {
        $filesystemMock = $this->createFilesystemMock();
        $filesystemMock
            ->expects($this->once())
            ->method('exists')
            ->with('/aWebRoot/aCachePrefix/aFilter/aPath')
            ->will($this->returnValue(true))
        ;

        $resolver = new WebPathResolver(
            $filesystemMock,
            new RequestContext,
            '/aWebRoot',
            'aCachePrefix'
        );

        $this->assertTrue($resolver->isStored('aPath', 'aFilter'));
    }

    public function testReturnFalseIfFileNotExistsOnIsStore()
    {
        $filesystemMock = $this->createFilesystemMock();
        $filesystemMock
            ->expects($this->once())
            ->method('exists')
            ->with('/aWebRoot/aCachePrefix/aFilter/aPath')
            ->will($this->returnValue(false))
        ;

        $resolver = new WebPathResolver(
            $filesystemMock,
            new RequestContext,
            '/aWebRoot',
            'aCachePrefix'
        );

        $this->assertFalse($resolver->isStored('aPath', 'aFilter'));
    }

    public function testComposeSchemaHostAndFileUrlOnResolve()
    {
        $requestContext = new RequestContext;
        $requestContext->setScheme('theSchema');
        $requestContext->setHost('theHost');

        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            $requestContext,
            '/aWebRoot',
            'aCachePrefix'
        );

        $this->assertEquals(
            'theschema://theHost/aCachePrefix/aFilter/aPath',
            $resolver->resolve('aPath', 'aFilter')
        );
    }

    public function testComposeSchemaHostAndBasePathWithPhpFileAndFileUrlOnResolve()
    {
        $requestContext = new RequestContext;
        $requestContext->setScheme('theSchema');
        $requestContext->setHost('theHost');
        $requestContext->setBaseUrl('/theBasePath/app.php');

        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            $requestContext,
            '/aWebRoot',
            'aCachePrefix'
        );

        $this->assertEquals(
            'theschema://theHost/theBasePath/aCachePrefix/aFilter/aPath',
            $resolver->resolve('aPath', 'aFilter')
        );
    }

    public function testComposeSchemaHostAndBasePathWithDirsOnlyAndFileUrlOnResolve()
    {
        $requestContext = new RequestContext;
        $requestContext->setScheme('theSchema');
        $requestContext->setHost('theHost');
        $requestContext->setBaseUrl('/theBasePath/theSubBasePath');

        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            $requestContext,
            '/aWebRoot',
            'aCachePrefix'
        );

        $this->assertEquals(
            'theschema://theHost/theBasePath/theSubBasePath/aCachePrefix/aFilter/aPath',
            $resolver->resolve('aPath', 'aFilter')
        );
    }

    public function testComposeSchemaHostAndBasePathWithBackSplashOnResolve()
    {
        $requestContext = new RequestContext;
        $requestContext->setScheme('theSchema');
        $requestContext->setHost('theHost');
        $requestContext->setBaseUrl('\\');

        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            $requestContext,
            '/aWebRoot',
            'aCachePrefix'
        );

        $this->assertEquals(
            'theschema://theHost/aCachePrefix/aFilter/aPath',
            $resolver->resolve('aPath', 'aFilter')
        );
    }

    public function testComposeSchemaHttpAndCustomPortAndFileUrlOnResolve()
    {
        $requestContext = new RequestContext;
        $requestContext->setScheme('http');
        $requestContext->setHost('theHost');
        $requestContext->setHttpPort(88);

        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            $requestContext,
            '/aWebRoot',
            'aCachePrefix'
        );

        $this->assertEquals(
            'http://theHost:88/aCachePrefix/aFilter/aPath',
            $resolver->resolve('aPath', 'aFilter')
        );
    }

    public function testComposeSchemaHttpsAndCustomPortAndFileUrlOnResolve()
    {
        $requestContext = new RequestContext;
        $requestContext->setScheme('https');
        $requestContext->setHost('theHost');
        $requestContext->setHttpsPort(444);

        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            $requestContext,
            '/aWebRoot',
            'aCachePrefix'
        );

        $this->assertEquals(
            'https://theHost:444/aCachePrefix/aFilter/aPath',
            $resolver->resolve('aPath', 'aFilter')
        );
    }

    public function testDumpBinaryContentOnStore()
    {
        $binary = new Binary('theContent', 'aMimeType', 'aFormat');

        $filesystemMock = $this->createFilesystemMock();
        $filesystemMock
            ->expects($this->once())
            ->method('dumpFile')
            ->with('/aWebRoot/aCachePrefix/aFilter/aPath', 'theContent')
        ;

        $resolver = new WebPathResolver(
            $filesystemMock,
            new RequestContext,
            '/aWebRoot',
            'aCachePrefix'
        );

        $this->assertNull($resolver->store($binary, 'aPath', 'aFilter'));
    }

    public function testDoNothingIfFiltersAndPathsEmptyOnRemove()
    {
        $filesystemMock = $this->createFilesystemMock();
        $filesystemMock
            ->expects($this->never())
            ->method('remove')
        ;

        $resolver = new WebPathResolver(
            $filesystemMock,
            new RequestContext,
            '/aWebRoot',
            'aCachePrefix'
        );

        $resolver->remove(array(), array());
    }

    public function testRemoveCacheForPathAndFilterOnRemove()
    {
        $filesystemMock = $this->createFilesystemMock();
        $filesystemMock
            ->expects($this->once())
            ->method('remove')
            ->with('/aWebRoot/aCachePrefix/aFilter/aPath')
        ;

        $resolver = new WebPathResolver(
            $filesystemMock,
            new RequestContext,
            '/aWebRoot',
            'aCachePrefix'
        );

        $resolver->remove(array('aPath'), array('aFilter'));
    }

    public function testRemoveCacheForSomePathsAndFilterOnRemove()
    {
        $filesystemMock = $this->createFilesystemMock();
        $filesystemMock
            ->expects($this->at(0))
            ->method('remove')
            ->with('/aWebRoot/aCachePrefix/aFilter/aPathOne')
        ;
        $filesystemMock
            ->expects($this->at(1))
            ->method('remove')
            ->with('/aWebRoot/aCachePrefix/aFilter/aPathTwo')
        ;

        $resolver = new WebPathResolver(
            $filesystemMock,
            new RequestContext,
            '/aWebRoot',
            'aCachePrefix'
        );

        $resolver->remove(array('aPathOne', 'aPathTwo'), array('aFilter'));
    }

    public function testRemoveCacheForSomePathsAndSomeFiltersOnRemove()
    {
        $filesystemMock = $this->createFilesystemMock();
        $filesystemMock
            ->expects($this->at(0))
            ->method('remove')
            ->with('/aWebRoot/aCachePrefix/aFilterOne/aPathOne')
        ;
        $filesystemMock
            ->expects($this->at(1))
            ->method('remove')
            ->with('/aWebRoot/aCachePrefix/aFilterTwo/aPathOne')
        ;
        $filesystemMock
            ->expects($this->at(2))
            ->method('remove')
            ->with('/aWebRoot/aCachePrefix/aFilterOne/aPathTwo')
        ;
        $filesystemMock
            ->expects($this->at(3))
            ->method('remove')
            ->with('/aWebRoot/aCachePrefix/aFilterTwo/aPathTwo')
        ;

        $resolver = new WebPathResolver(
            $filesystemMock,
            new RequestContext,
            '/aWebRoot',
            'aCachePrefix'
        );

        $resolver->remove(
            array('aPathOne', 'aPathTwo'),
            array('aFilterOne', 'aFilterTwo')
        );
    }

    public function testRemoveCacheForFilterOnRemove()
    {
        $filesystemMock = $this->createFilesystemMock();
        $filesystemMock
            ->expects($this->once())
            ->method('remove')
            ->with(array(
                '/aWebRoot/aCachePrefix/aFilter',
            ))
        ;

        $resolver = new WebPathResolver(
            $filesystemMock,
            new RequestContext,
            '/aWebRoot',
            'aCachePrefix'
        );

        $resolver->remove(array(), array('aFilter'));
    }

    public function testRemoveCacheForSomeFiltersOnRemove()
    {
        $filesystemMock = $this->createFilesystemMock();
        $filesystemMock
            ->expects($this->once())
            ->method('remove')
            ->with(array(
                '/aWebRoot/aCachePrefix/aFilterOne',
                '/aWebRoot/aCachePrefix/aFilterTwo'
            ))
        ;

        $resolver = new WebPathResolver(
            $filesystemMock,
            new RequestContext,
            '/aWebRoot',
            'aCachePrefix'
        );

        $resolver->remove(array(), array('aFilterOne', 'aFilterTwo'));
    }

    public function testShouldRemoveDoubleSlashInUrl()
    {
        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            new RequestContext,
            '/aWebRoot',
            'aCachePrefix'
        );

        $rc = new \ReflectionClass($resolver);
        $method = $rc->getMethod('getFileUrl');
        $method->setAccessible(true);

        $result = $method->invokeArgs($resolver, array('/cats.jpg', 'some_filter'));

        $this->assertEquals('aCachePrefix/some_filter/cats.jpg', $result);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Filesystem
     */
    protected function createFilesystemMock()
    {
        return $this->getMock('Symfony\Component\Filesystem\Filesystem');
    }
}
