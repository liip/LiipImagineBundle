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

use Liip\ImagineBundle\Binary\SimpleMimeTypeGuesser;
use Liip\ImagineBundle\Tests\AbstractTest;

use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;

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
        $dataLoader = new FileSystemLoader(
            MimeTypeGuesser::getInstance(),
            ExtensionGuesser::getInstance(),
            array(),
            $this->dataDir
        );

        $dataManager = new DataManager(
            new SimpleMimeTypeGuesser(MimeTypeGuesser::getInstance()),
            ExtensionGuesser::getInstance(),
            $this->configuration,
            'filesystem'
        );
        $dataManager->addLoader('filesystem', $dataLoader);

        $filterLoader = new ThumbnailFilterLoader();

        $filterManager = new FilterManager($this->configuration, $this->imagine);
        $filterManager->addLoader('thumbnail', $filterLoader);

        $webPathResolver = new WebPathResolver(
            $this->filesystem,
            new RequestContext,
            $this->webRoot
        );

        $cacheManager = new CacheManager(
            $this->configuration,
            $this->createRouterMock(),
            'web_path'
        );

        $cacheManager->addResolver('web_path', $webPathResolver);

        $controller = new ImagineController($dataManager, $filterManager, $cacheManager, $this->imagine);

        $response = $controller->filterAction('cats.jpeg', 'thumbnail');

        $filePath = realpath($this->webRoot).'/media/cache/thumbnail/cats.jpeg';

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);

        $this->assertEquals('http://localhost/media/cache/thumbnail/cats.jpeg', $response->getTargetUrl());
        $this->assertEquals(301, $response->getStatusCode());

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

        $mimeTypeGuesser = new SimpleMimeTypeGuesser(MimeTypeGuesser::getInstance());
        $extensionGuesser = ExtensionGuesser::getInstance();

        $dataManager = $this->getMock('Liip\ImagineBundle\Imagine\Data\DataManager', array(), array($mimeTypeGuesser, $extensionGuesser, $this->configuration));
        $filterManager = $this->getMock('Liip\ImagineBundle\Imagine\Filter\FilterManager', array(), array($this->configuration, $this->imagine));

        $controller = new ImagineController($dataManager, $filterManager, $cacheManager, $this->imagine);

        $response = $controller->filterAction('cats.jpeg', 'thumbnail');

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals('http://foo.com/a/path/image.jpg', $response->headers->get('Location'));
        $this->assertEquals(301, $response->getStatusCode());
    }
}
