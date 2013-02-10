<?php

namespace Liip\ImagineBundle\Tests;

use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractTest extends \PHPUnit_Framework_TestCase
{
    protected $filesystem;
    protected $fixturesDir;
    protected $tempDir;

    protected function setUp()
    {
        $this->fixturesDir = __DIR__.'/Fixtures';

        $this->tempDir = sys_get_temp_dir().'/liip_imagine_test';

        $this->filesystem = new Filesystem();

        if ($this->filesystem->exists($this->tempDir)) {
            $this->filesystem->remove($this->tempDir);
        }

        $this->filesystem->mkdir($this->tempDir);
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

    protected function tearDown()
    {
        if ($this->filesystem->exists($this->tempDir)) {
            $this->filesystem->remove($this->tempDir);
        }
    }
}
