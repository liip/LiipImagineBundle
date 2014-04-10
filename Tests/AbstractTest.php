<?php

namespace Liip\ImagineBundle\Tests;

use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class AbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    protected $fixturesDir;
    protected $tempDir;

    protected function setUp()
    {
        $this->fixturesDir = __DIR__.'/Fixtures';

        $this->tempDir = str_replace('/', DIRECTORY_SEPARATOR, sys_get_temp_dir().'/liip_imagine_test');

        $this->filesystem = new Filesystem();

        if ($this->filesystem->exists($this->tempDir)) {
            $this->filesystem->remove($this->tempDir);
        }

        $this->filesystem->mkdir($this->tempDir);
    }

    public function invalidPathProvider()
    {
        return array(
            array($this->fixturesDir.'/assets/../../foobar.png'),
            array($this->fixturesDir.'/assets/some_folder/../foobar.png'),
            array('../../outside/foobar.jpg'),
        );
    }

    protected function createFilterConfiguration()
    {
        $config = new FilterConfiguration();
        $config->set('thumbnail', array(
            'size' => array(180, 180),
            'mode' => 'outbound',
        ));

        return $config;
    }

    protected function getMockCacheManager()
    {
        return $this->getMock('Liip\ImagineBundle\Imagine\Cache\CacheManager', array(), array(), '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|FilterConfiguration
     */
    protected function createFilterConfigurationMock()
    {
        return $this->getMock('Liip\ImagineBundle\Imagine\Filter\FilterConfiguration');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|RouterInterface
     */
    protected function createRouterMock()
    {
        return $this->getMock('Symfony\Component\Routing\RouterInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ResolverInterface
     */
    protected function createResolverMock()
    {
        return $this->getMock('Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface');
    }

    protected function createEventDispatcherMock()
    {
        return $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
    }

    protected function getMockImage()
    {
        return $this->getMock('Imagine\Image\ImageInterface');
    }

    protected function createImagineMock()
    {
        return $this->getMock('Imagine\Image\ImagineInterface');
    }

    protected function tearDown()
    {
        if (!$this->filesystem) {
            return;
        }

        if ($this->filesystem->exists($this->tempDir)) {
            $this->filesystem->remove($this->tempDir);
        }
    }
}
