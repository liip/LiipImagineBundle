<?php

namespace Liip\ImagineBundle\Tests;

use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Symfony\Component\Filesystem\Filesystem;

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

    protected function getMockFilterConfiguration()
    {
        return $this->getMock('Liip\ImagineBundle\Imagine\Filter\FilterConfiguration');
    }

    protected function getMockRouter()
    {
        return $this->getMock('Symfony\Component\Routing\RouterInterface');
    }

    protected function getMockResolver()
    {
        return $this->getMock('Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface');
    }

    protected function getMockImage()
    {
        return $this->getMock('Imagine\Image\ImageInterface');
    }

    protected function getMockImagine()
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
