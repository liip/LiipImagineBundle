<?php

namespace Liip\ImagineBundle\Tests\Binary\Loader;

use Liip\ImagineBundle\Binary\Loader\FlysystemLoader;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers Liip\ImagineBundle\Binary\Loader\FlysystemLoader
 */
class FlysystemLoaderTest extends AbstractTest
{
    private $container;
    private $sFlysystemFileSystem = 'someLocalFileSystem';
    
    public function setUp()
    {
        parent::setUp();
        
        $adapter = new \League\Flysystem\Adapter\Local($this->fixturesDir);
        $fileSystem = new \League\Flysystem\Filesystem($adapter);
        
        $this->container = new ContainerBuilder();
        $id = sprintf('oneup_flysystem.%s_filesystem', $this->sFlysystemFileSystem);
        $this->container->set($id, $fileSystem);
    }
    
    public function testShouldImplementLoaderInterface()
    {
        $rc = new \ReflectionClass('Liip\ImagineBundle\Binary\Loader\FlysystemLoader');

        $this->assertTrue($rc->implementsInterface('Liip\ImagineBundle\Binary\Loader\LoaderInterface'));
    }

    public function testCouldBeConstructedWithExpectedArguments()
    {
        return new FlysystemLoader(
            $this->container,
            ExtensionGuesser::getInstance(),
            $this->sFlysystemFileSystem
        );
    }
    
    /**
     * @depends testCouldBeConstructedWithExpectedArguments
     */
    public function testReturnImageContentOnFind($loader)
    {
        $expectedContent = file_get_contents($this->fixturesDir.'/assets/cats.jpeg');

        $this->assertSame(
            $expectedContent,
            $loader->find('assets/cats.jpeg')->getContent()
        );
    }
    
    /**
     * @depends testCouldBeConstructedWithExpectedArguments
     */
    public function testThrowsIfInvalidPathGivenOnFind($loader)
    {
        $sPath = 'invalid.jpeg';
        
        $this->setExpectedException(
            'Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException',
            sprintf('Source image "%s" not found.', $sPath)
        );

        $loader->find($sPath);
    }
}
