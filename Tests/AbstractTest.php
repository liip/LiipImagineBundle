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

use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Metadata\MetadataBag;
use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Liip\ImagineBundle\Binary\MimeTypeGuesserInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Imagine\Cache\SignerInterface;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Imagine\Filter\PostProcessor\PostProcessorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface;
use Symfony\Component\Routing\RouterInterface;

abstract class AbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $fixturesPath;

    /**
     * @var string
     */
    protected $temporaryPath;

    protected function setUp()
    {
        $this->fixturesPath = realpath(__DIR__.DIRECTORY_SEPARATOR.'Fixtures');
        $this->temporaryPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'liip_imagine_test';
        $this->filesystem = new Filesystem();

        if ($this->filesystem->exists($this->temporaryPath)) {
            $this->filesystem->remove($this->temporaryPath);
        }

        $this->filesystem->mkdir($this->temporaryPath);
    }

    /**
     * @return string[]
     */
    public function invalidPathProvider()
    {
        return array(
            array($this->fixturesPath.'/assets/../../foobar.png'),
            array($this->fixturesPath.'/assets/some_folder/../foobar.png'),
            array('../../outside/foobar.jpg'),
        );
    }

    /**
     * @return FilterConfiguration
     */
    protected function createFilterConfiguration()
    {
        $config = new FilterConfiguration();
        $config->set('thumbnail', array(
            'size' => array(180, 180),
            'mode' => 'outbound',
        ));

        return $config;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|CacheManager
     */
    protected function createCacheManagerMock()
    {
        return $this
            ->getMockBuilder('\Liip\ImagineBundle\Imagine\Cache\CacheManager')
            ->setConstructorArgs(array(
                $this->createFilterConfiguration(),
                $this->createRouterInterfaceMock(),
                $this->createSignerInterfaceMock(),
                $this->createEventDispatcherInterfaceMock(),
            ))
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|FilterConfiguration
     */
    protected function createFilterConfigurationMock()
    {
        return $this->createObjectMock('\Liip\ImagineBundle\Imagine\Filter\FilterConfiguration');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SignerInterface
     */
    protected function createSignerInterfaceMock()
    {
        return $this->createObjectMock('\Liip\ImagineBundle\Imagine\Cache\SignerInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|RouterInterface
     */
    protected function createRouterInterfaceMock()
    {
        return $this->createObjectMock('\Symfony\Component\Routing\RouterInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ResolverInterface
     */
    protected function createCacheResolverInterfaceMock()
    {
        return $this->createObjectMock('\Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|EventDispatcherInterface
     */
    protected function createEventDispatcherInterfaceMock()
    {
        return $this->createObjectMock('\Symfony\Component\EventDispatcher\EventDispatcherInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ImageInterface
     */
    protected function getImageInterfaceMock()
    {
        return $this->createObjectMock('\Imagine\Image\ImageInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|MetadataBag
     */
    protected function getMetadataBagMock()
    {
        return $this->createObjectMock('\Imagine\Image\Metadata\MetadataBag');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ImagineInterface
     */
    protected function createImagineInterfaceMock()
    {
        return $this->createObjectMock('\Imagine\Image\ImagineInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|LoggerInterface
     */
    protected function createLoggerInterfaceMock()
    {
        return $this->createObjectMock('\Psr\Log\LoggerInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|LoaderInterface
     */
    protected function createBinaryLoaderInterfaceMock()
    {
        return $this->createObjectMock('\Liip\ImagineBundle\Binary\Loader\LoaderInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|MimeTypeGuesserInterface
     */
    protected function createMimeTypeGuesserInterfaceMock()
    {
        return $this->createObjectMock('\Liip\ImagineBundle\Binary\MimeTypeGuesserInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ExtensionGuesserInterface
     */
    protected function createExtensionGuesserInterfaceMock()
    {
        return $this->createObjectMock('\Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PostProcessorInterface
     */
    protected function createPostProcessorInterfaceMock()
    {
        return $this->createObjectMock('\Liip\ImagineBundle\Imagine\Filter\PostProcessor\PostProcessorInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|FilterManager
     */
    protected function createFilterManagerMock()
    {
        return $this->createObjectMock('\Liip\ImagineBundle\Imagine\Filter\FilterManager', array(), false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|DataManager
     */
    protected function createDataManagerMock()
    {
        return $this->createObjectMock('\Liip\ImagineBundle\Imagine\Data\DataManager', array(), false);
    }

    /**
     * @param string   $object
     * @param string[] $methods
     * @param bool     $constructorInvoke
     * @param mixed[]  $constructorParams
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createObjectMock($object, array $methods = array(), $constructorInvoke = false, array $constructorParams = array())
    {
        $builder = $this->getMockBuilder($object);

        if (count($methods) > 0) {
            $builder->setMethods($methods);
        }

        if ($constructorInvoke) {
            $builder->enableOriginalConstructor();
        } else {
            $builder->disableOriginalConstructor();
        }

        if (count($constructorParams) > 0) {
            $builder->setConstructorArgs($constructorParams);
        }

        return $builder->getMock();
    }

    /**
     * @param object $object
     * @param string $name
     *
     * @return \ReflectionMethod
     */
    protected function getVisibilityRestrictedMethod($object, $name)
    {
        $r = new \ReflectionObject($object);

        $m = $r->getMethod($name);
        $m->setAccessible(true);

        return $m;
    }

    protected function tearDown()
    {
        if (!$this->filesystem) {
            return;
        }

        if ($this->filesystem->exists($this->temporaryPath)) {
            $this->filesystem->remove($this->temporaryPath);
        }
    }
}
