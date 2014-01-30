<?php

namespace Liip\ImagineBundle\Tests\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Tests\AbstractTest;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @covers Liip\ImagineBundle\Imagine\Cache\Resolver\AbstractFilesystemResolver
 */
class AbstractFilesystemResolverTest extends AbstractTest
{
    public function testStoreCyrillicFilename()
    {
        if (false !== strpos(strtolower(PHP_OS), 'win')) {
            $this->markTestSkipped('file_get_contents can not read files with utf-8 file names on windows');
        }

        $image = $this->fixturesDir.'/assets/АГГЗ.jpeg';

        $data = file_get_contents($image);

        $binary = new Binary($data, 'image/jpeg', 'jpeg');

        $filePath = $this->tempDir.'/cached/АГГЗ.jpeg';

        $resolver = $this->getMockAbstractFilesystemResolver(new Filesystem());
        $resolver
            ->expects($this->once())
            ->method('getFilePath')
            ->will($this->returnValue($filePath))
        ;

        $resolver->store($binary, '/a/path', 'mirror');

        $this->assertTrue(file_exists($filePath));
        $this->assertEquals($data, file_get_contents($filePath));
    }

    public function testUsePathAndFilterToGetFilePath()
    {
        if (false !== strpos(strtolower(PHP_OS), 'win')) {
            $this->markTestSkipped('mkdir mode is ignored on windows');
        }

        $expectedPath = '/a/path';
        $expectedFilter = 'thumbnail';
        $expectedFilePath = $this->tempDir . '/cats.jpeg';

        $resolver = $this->getMockAbstractFilesystemResolver(new Filesystem());
        $resolver
            ->expects($this->once())
            ->method('getFilePath')
            ->with($expectedPath, $expectedFilter)
            ->will($this->returnValue($expectedFilePath))
        ;

        $binary = new Binary('theImageContent', 'image/jpeg', 'jpg');

        $resolver->store($binary, $expectedPath, $expectedFilter);
    }

    public function testStoreBinaryContentToFilePath()
    {
        if (false !== strpos(strtolower(PHP_OS), 'win')) {
            $this->markTestSkipped('mkdir mode is ignored on windows');
        }

        $expectedFilePath = $this->tempDir . '/cats.jpeg';

        $resolver = $this->getMockAbstractFilesystemResolver(new Filesystem());
        $resolver
            ->expects($this->once())
            ->method('getFilePath')
            ->will($this->returnValue($expectedFilePath))
        ;

        $binary = new Binary('theImageContent', 'image/jpeg', 'jpeg');

        $resolver->store($binary, '/a/path', 'thumbnail');
        $this->assertFileExists($expectedFilePath);
        $this->assertEquals('theImageContent', file_get_contents($expectedFilePath));
    }

    public function testMkdirVerifyPermissionOnLastLevel()
    {
        if (false !== strpos(strtolower(PHP_OS), 'win')) {
            $this->markTestSkipped('mkdir mode is ignored on windows');
        }

        $filePath = $this->tempDir . '/first-level/second-level/cats.jpeg';

        $resolver = $this->getMockAbstractFilesystemResolver(new Filesystem());
        $resolver
            ->expects($this->once())
            ->method('getFilePath')
            ->will($this->returnValue($filePath))
        ;

        $resolver->store(new Binary('aContent', 'image/jpeg', 'jpg'), '/a/path', 'thumbnail');
        $this->assertEquals(040777, fileperms($this->tempDir . '/first-level/second-level'));
    }

    public function testMkdirVerifyPermissionOnFirstLevel()
    {
        if (false !== strpos(strtolower(PHP_OS), 'win')) {
            $this->markTestSkipped('mkdir mode is ignored on windows');
        }

        $filePath = $this->tempDir . '/first-level/second-level/cats.jpeg';

        $resolver = $this->getMockAbstractFilesystemResolver(new Filesystem());
        $resolver
            ->expects($this->once())
            ->method('getFilePath')
            ->will($this->returnValue($filePath))
        ;

        $resolver->store(new Binary('aContent', 'image/jpeg', 'jpg'), '/a/path', 'thumbnail');
        $this->assertEquals(040777, fileperms($this->tempDir . '/first-level'));
    }

    public function testStoreInvalidDirectory()
    {
        if (false !== strpos(strtolower(PHP_OS), 'win')) {
            $this->markTestSkipped('mkdir mode is ignored on windows');
        }

        $filePath = $this->tempDir.'/unwriteable/thumbnail/cats.jpeg';

        $resolver = $this->getMockAbstractFilesystemResolver(new Filesystem());
        $resolver
            ->expects($this->once())
            ->method('getFilePath')
            ->will($this->returnValue($filePath))
        ;

        $this->filesystem->mkdir($this->tempDir.'/unwriteable', 0555);

        $this->setExpectedException('RuntimeException', 'Could not create directory '.dirname($this->tempDir.'/unwriteable/thumbnail/cats.jpeg'));
        $resolver->store(new Binary('aContent', 'image/jpeg', 'jpg'), '/a/path', 'thumbnail');
    }

    /**
     * @param Filesystem $filesystem
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Liip\ImagineBundle\Imagine\Cache\Resolver\AbstractFilesystemResolver
     */
    protected function getMockAbstractFilesystemResolver(Filesystem $filesystem)
    {
        return $this->getMock('Liip\ImagineBundle\Imagine\Cache\Resolver\AbstractFilesystemResolver', array('resolve', 'clear', 'getBrowserPath', 'getFilePath'), array($filesystem));
    }
}
