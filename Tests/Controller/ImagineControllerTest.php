<?php

namespace Liip\ImagineBundle\Tests\Controller;

use Imagine\Image\ImagineInterface;

use Liip\ImagineBundle\Controller\ImagineController;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Cache\Resolver\WebPathResolver;

use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Data\Loader\FileSystemLoader;

use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Imagine\Filter\Loader\ThumbnailFilterLoader;

use Liip\ImagineBundle\Tests\AbstractTest;

use Symfony\Component\HttpFoundation\Request;

/**
 * @covers Liip\ImagineBundle\Controller\ImagineController
 */
class ImagineControllerTest extends AbstractTest
{
    /**
     * @var ImagineInterface
     */
    protected $imagine;

    protected $webRoot;
    protected $cacheDir;
    protected $dataDir;

    protected $configuration;

    protected function setUp()
    {
        parent::setUp();

        foreach (array('Imagine\Gd\Imagine', 'Imagine\Imagick\Imagine', 'Imagine\Gmagick\Imagine') as $eachClass) {
            try {
                $this->imagine = new $eachClass;

                break;
            } catch (\Exception $e) { }
        }

        if (!$this->imagine) {
            $this->markTestSkipped('No Imagine could be instantiated.');
        }

        $this->webRoot = $this->tempDir.'/web';
        $this->filesystem->mkdir($this->webRoot);

        $this->cacheDir = $this->webRoot.'/media/cache';
        $this->dataDir = $this->fixturesDir.'/assets';

        $this->configuration = new FilterConfiguration(array(
            'thumbnail' => array(
                'filters' => array(
                    'thumbnail' => array(
                        'size' => array(300, 150),
                        'mode' => 'outbound',
                    ),
                ),
            ),
        ));
    }

    public function testFilterActionLive()
    {
        $router = $this->getMockRouter();
        $router
            ->expects($this->any())
            ->method('generate')
            ->with('_imagine_thumbnail', array(
                'path' => 'cats.jpeg'
            ), false)
            ->will($this->returnValue('/media/cache/thumbnail/cats.jpeg'))
        ;

        $dataLoader = new FileSystemLoader($this->imagine, array(), $this->dataDir);

        $dataManager = new DataManager($this->configuration, 'filesystem');
        $dataManager->addLoader('filesystem', $dataLoader);

        $filterLoader = new ThumbnailFilterLoader();

        $filterManager = new FilterManager($this->configuration);
        $filterManager->addLoader('thumbnail', $filterLoader);

        $webPathResolver = new WebPathResolver($this->filesystem);

        $cacheManager = new CacheManager($this->configuration, $router, $this->webRoot, 'web_path');
        $cacheManager->addResolver('web_path', $webPathResolver);

        $controller = new ImagineController($dataManager, $filterManager, $cacheManager);

        $request = Request::create('/media/cache/thumbnail/cats.jpeg');

        $webPathResolver->setRequest($request);

        $response = $controller->filterAction($request, 'cats.jpeg', 'thumbnail');

        $filePath = realpath($this->webRoot).'/media/cache/thumbnail/cats.jpeg';

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertTrue(file_exists($filePath));
        $this->assertNotEmpty(file_get_contents($filePath));

        return $controller;
    }

    public function testFilterDelegatesResolverResponse()
    {
        $cacheManager = $this->getMockCacheManager();
        $cacheManager
            ->expects($this->once())
            ->method('isStored')
            ->will($this->returnValue(true))
        ;
        $cacheManager
            ->expects($this->once())
            ->method('resolve')
            ->will($this->returnValue('http://foo.com/a/path/image.jpg'))
        ;

        $dataManager = $this->getMock('Liip\ImagineBundle\Imagine\Data\DataManager', array(), array($this->configuration));
        $filterManager = $this->getMock('Liip\ImagineBundle\Imagine\Filter\FilterManager', array(), array($this->configuration));

        $controller = new ImagineController($dataManager, $filterManager, $cacheManager);

        $request = Request::create('/media/cache/thumbnail/cats.jpeg');

        $response = $controller->filterAction($request, 'cats.jpeg', 'thumbnail');

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals('http://foo.com/a/path/image.jpg', $response->headers->get('Location'));
        $this->assertEquals(301, $response->getStatusCode());
    }
}
