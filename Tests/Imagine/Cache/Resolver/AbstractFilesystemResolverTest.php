<?php

namespace Liip\ImagineBundle\Tests\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Tests\AbstractTest;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers Liip\ImagineBundle\Imagine\Cache\Resolver\AbstractFilesystemResolver
 */
class AbstractFilesystemResolverTest extends AbstractTest
{
    public function testStoreCyrillicFilename()
    {
        $image = $this->fixturesDir.'/assets/АГГЗ.jpeg';
        $data = file_get_contents($image);
        $response = new Response($data, 200, array(
            'content-type' => 'image/jpeg',
        ));

        $targetPath = $this->tempDir.'/cached/АГГЗ.jpeg';

        $resolver = $this->getMockAbstractFilesystemResolver(new Filesystem());
        $resolver->store($response, $targetPath, 'mirror');

        $this->assertTrue(file_exists($targetPath));
        $this->assertEquals($data, file_get_contents($targetPath));
    }

    public function testStoreInvalidDirectory()
    {
        $resolver = $this->getMockAbstractFilesystemResolver(new Filesystem());

        $this->filesystem->mkdir($this->tempDir.'/unwriteable', 0555);

        $this->setExpectedException('RuntimeException', 'Could not create directory '.dirname($this->tempDir.'/unwriteable/thumbnail/cats.jpeg'));
        $resolver->store(new Response(''), $this->tempDir.'/unwriteable/thumbnail/cats.jpeg', 'thumbnail');
    }

    protected function getMockAbstractFilesystemResolver($filesystem)
    {
        return $this->getMock('Liip\ImagineBundle\Imagine\Cache\Resolver\AbstractFilesystemResolver', array('resolve', 'clear', 'getBrowserPath', 'getFilePath'), array($filesystem));
    }
}
