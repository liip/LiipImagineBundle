<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests;

use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\RouterInterface;

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
        return $this->getMockBuilder('Liip\ImagineBundle\Imagine\Cache\CacheManager')->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|FilterConfiguration
     */
    protected function createFilterConfigurationMock()
    {
        return $this->getMockBuilder('Liip\ImagineBundle\Imagine\Filter\FilterConfiguration')->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|RouterInterface
     */
    protected function createRouterMock()
    {
        return $this->getMockBuilder('Symfony\Component\Routing\RouterInterface')->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ResolverInterface
     */
    protected function createResolverMock()
    {
        return $this->getMockBuilder('Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface')->getMock();
    }

    protected function createEventDispatcherMock()
    {
        return $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();
    }

    protected function getMockImage()
    {
        return $this->getMockBuilder('Imagine\Image\ImageInterface')->getMock();
    }

    protected function getMockMetaData()
    {
        return $this->getMockBuilder('Imagine\Image\Metadata\MetadataBag')->getMock();
    }

    protected function createImagineMock()
    {
        return $this->getMockBuilder('Imagine\Image\ImagineInterface')->getMock();
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
