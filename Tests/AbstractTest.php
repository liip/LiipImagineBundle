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
use Liip\ImagineBundle\Config\Controller\ControllerConfig;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Imagine\Cache\SignerInterface;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Imagine\Filter\PostProcessor\PostProcessorInterface;
use Liip\ImagineBundle\Service\FilterService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface;
use Symfony\Component\Mime\MimeTypesInterface;
use Symfony\Component\Routing\RouterInterface;

abstract class AbstractTest extends TestCase
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

    protected function setUp(): void
    {
        $this->fixturesPath = realpath(__DIR__.DIRECTORY_SEPARATOR.'Fixtures');
        $this->temporaryPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'liip_imagine_test';
        $this->filesystem = new Filesystem();

        if ($this->filesystem->exists($this->temporaryPath)) {
            $this->filesystem->remove($this->temporaryPath);
        }

        $this->filesystem->mkdir($this->temporaryPath);
    }

    protected function tearDown(): void
    {
        if (!$this->filesystem) {
            return;
        }

        if ($this->filesystem->exists($this->temporaryPath)) {
            $this->filesystem->remove($this->temporaryPath);
        }
    }

    /**
     * @return string[]
     */
    public function invalidPathProvider(): array
    {
        return [
            [$this->fixturesPath.'/assets/../../foobar.png'],
            [$this->fixturesPath.'/assets/some_folder/../foobar.png'],
            ['../../outside/foobar.jpg'],
        ];
    }

    protected function createFilterConfiguration(): FilterConfiguration
    {
        $config = new FilterConfiguration();
        $config->set('thumbnail', [
            'size' => [180, 180],
            'mode' => 'outbound',
        ]);

        return $config;
    }

    /**
     * @return MockObject|CacheManager
     */
    protected function createCacheManagerMock()
    {
        return $this
            ->getMockBuilder(CacheManager::class)
            ->setConstructorArgs([
                $this->createFilterConfiguration(),
                $this->createRouterInterfaceMock(),
                $this->createSignerInterfaceMock(),
                $this->createEventDispatcherInterfaceMock(),
            ])
            ->getMock();
    }

    /**
     * @return MockObject|FilterConfiguration
     */
    protected function createFilterConfigurationMock()
    {
        return $this->createObjectMock(FilterConfiguration::class);
    }

    /**
     * @return MockObject|SignerInterface
     */
    protected function createSignerInterfaceMock()
    {
        return $this->createObjectMock(SignerInterface::class);
    }

    /**
     * @return MockObject|RouterInterface
     */
    protected function createRouterInterfaceMock()
    {
        return $this->createObjectMock(RouterInterface::class);
    }

    /**
     * @return MockObject|ResolverInterface
     */
    protected function createCacheResolverInterfaceMock()
    {
        return $this->createObjectMock(ResolverInterface::class);
    }

    /**
     * @return MockObject|EventDispatcherInterface
     */
    protected function createEventDispatcherInterfaceMock()
    {
        return $this->createObjectMock(EventDispatcherInterface::class);
    }

    /**
     * @return MockObject|ImageInterface
     */
    protected function getImageInterfaceMock()
    {
        return $this->createObjectMock(ImageInterface::class);
    }

    /**
     * @return MockObject|MetadataBag
     */
    protected function getMetadataBagMock()
    {
        return $this->createObjectMock(MetadataBag::class);
    }

    /**
     * @return MockObject|ImagineInterface
     */
    protected function createImagineInterfaceMock()
    {
        return $this->createObjectMock(ImagineInterface::class);
    }

    /**
     * @return MockObject|LoggerInterface
     */
    protected function createLoggerInterfaceMock()
    {
        return $this->createObjectMock(LoggerInterface::class);
    }

    /**
     * @return MockObject|LoaderInterface
     */
    protected function createBinaryLoaderInterfaceMock()
    {
        return $this->createObjectMock(LoaderInterface::class);
    }

    /**
     * @return MockObject|MimeTypeGuesserInterface
     */
    protected function createMimeTypeGuesserInterfaceMock()
    {
        return $this->createObjectMock(MimeTypeGuesserInterface::class);
    }

    /**
     * @return MockObject|ExtensionGuesserInterface
     */
    protected function createExtensionGuesserInterfaceMock()
    {
        if (!interface_exists(MimeTypesInterface::class)) {
            return $this->createObjectMock(ExtensionGuesserInterface::class);
        }

        return $this->createObjectMock(MimeTypesInterface::class);
    }

    /**
     * @return MockObject|PostProcessorInterface
     */
    protected function createPostProcessorInterfaceMock()
    {
        return $this->createObjectMock(PostProcessorInterface::class);
    }

    /**
     * @return MockObject|FilterManager
     */
    protected function createFilterManagerMock()
    {
        return $this->createObjectMock(FilterManager::class, [], false);
    }

    /**
     * @return MockObject|FilterService
     */
    protected function createFilterServiceMock()
    {
        return $this->createObjectMock(FilterService::class);
    }

    /**
     * @return MockObject|DataManager
     */
    protected function createDataManagerMock()
    {
        return $this->createObjectMock(DataManager::class, [], false);
    }

    protected function createControllerConfigInstance(int $redirectResponseCode = null): ControllerConfig
    {
        return new ControllerConfig($redirectResponseCode ?? 301);
    }

    /**
     * @param string[] $methods
     * @param mixed[]  $constructorParams
     */
    protected function createObjectMock(string $object, array $methods = [], bool $constructorInvoke = false, array $constructorParams = []): MockObject
    {
        $builder = $this->getMockBuilder($object);

        if (\count($methods) > 0) {
            $builder->setMethods($methods);
        }

        if ($constructorInvoke) {
            $builder->enableOriginalConstructor();
        } else {
            $builder->disableOriginalConstructor();
        }

        if (\count($constructorParams) > 0) {
            $builder->setConstructorArgs($constructorParams);
        }

        return $builder->getMock();
    }

    /**
     * @param object $object
     */
    protected function getVisibilityRestrictedMethod($object, string $name): \ReflectionMethod
    {
        $r = new \ReflectionObject($object);

        $m = $r->getMethod($name);
        $m->setAccessible(true);

        return $m;
    }
}
