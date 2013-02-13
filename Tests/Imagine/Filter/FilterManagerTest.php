<?php

namespace Liip\ImagineBundle\Tests\Filter;

use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Tests\AbstractTest;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers Liip\ImagineBundle\Imagine\Filter\FilterManager
 */
class FilterManagerTest extends AbstractTest
{
    public function testGetWithoutLoader()
    {
        $config = $this->getMockFilterConfiguration();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'filters' => array(
                    'thumbnail' => array(
                        'size' => array(180, 180),
                        'mode' => 'outbound',
                    ),
                ),
            )))
        ;
        $filterManager = new FilterManager($config);

        $this->setExpectedException('InvalidArgumentException', 'Could not find filter loader for "thumbnail" filter type');
        $filterManager->get(new Request(), 'thumbnail', $this->getMockImage(), 'cats.jpeg');
    }

    public function testGetDefaultBehavior()
    {
        $thumbConfig = array(
            'size' => array(180, 180),
            'mode' => 'outbound',
        );

        $config = $this->getMockFilterConfiguration();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'filters' => array(
                    'thumbnail' => $thumbConfig,
                ),
            )))
        ;

        $image = $this->getMockImage();
        $image
            ->expects($this->once())
            ->method('get')
            ->with('jpeg', array('quality' => 100))
            ->will($this->returnSelf())
        ;

        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($image, $thumbConfig)
            ->will($this->returnValue($image))
        ;

        $filterManager = new FilterManager($config);
        $filterManager->addLoader('thumbnail', $loader);

        $request = new Request();
        $response = $filterManager->get($request, 'thumbnail', $image, $this->fixturesDir.'/assets/cats.jpeg');

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('image/jpeg', $response->headers->get('Content-Type'));
    }

    public function testGetConfigAltersFormatAndQuality()
    {
        $thumbConfig = array(
            'size' => array(180, 180),
            'mode' => 'outbound',
        );

        $config = $this->getMockFilterConfiguration();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'filters' => array(
                    'thumbnail' => $thumbConfig,
                ),
                'format' => 'jpg',
                'quality' => 80,
            )))
        ;

        $image = $this->getMockImage();
        $image
            ->expects($this->once())
            ->method('get')
            ->with('jpg', array('quality' => 80))
            ->will($this->returnSelf())
        ;

        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($image, $thumbConfig)
            ->will($this->returnValue($image))
        ;

        $filterManager = new FilterManager($config);
        $filterManager->addLoader('thumbnail', $loader);

        $request = new Request();
        $response = $filterManager->get($request, 'thumbnail', $image, $this->fixturesDir.'/assets/cats.jpeg');

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('image/jpg', $response->headers->get('Content-Type'));
    }

    public function testGetRequestKnowsContentType()
    {
        $thumbConfig = array(
            'size' => array(180, 180),
            'mode' => 'outbound',
        );

        $config = $this->getMockFilterConfiguration();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'filters' => array(
                    'thumbnail' => $thumbConfig,
                ),
                'format' => 'jpg',
            )))
        ;

        $image = $this->getMockImage();
        $image
            ->expects($this->once())
            ->method('get')
            ->with('jpg', array('quality' => 100))
            ->will($this->returnSelf())
        ;

        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($image, $thumbConfig)
            ->will($this->returnValue($image))
        ;

        $filterManager = new FilterManager($config);
        $filterManager->addLoader('thumbnail', $loader);

        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $request
            ->expects($this->once())
            ->method('getMimeType')
            ->with('jpg')
            ->will($this->returnValue('image/jpeg'))
        ;

        $response = $filterManager->get($request, 'thumbnail', $image, $this->fixturesDir.'/assets/cats.jpeg');

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('image/jpeg', $response->headers->get('Content-Type'));
    }

    public function testApplyFilterSet()
    {
        $image = $this->getMockImage();

        $thumbConfig = array(
            'size' => array(180, 180),
            'mode' => 'outbound',
        );

        $config = $this->getMockFilterConfiguration();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'filters' => array(
                    'thumbnail' => $thumbConfig,
                ),
            )))
        ;

        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($image, $thumbConfig)
            ->will($this->returnValue($image))
        ;

        $filterManager = new FilterManager($config);
        $filterManager->addLoader('thumbnail', $loader);

        $this->assertSame($image, $filterManager->applyFilter($image, 'thumbnail'));
    }

    protected function getMockLoader()
    {
        return $this->getMock('Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface');
    }
}
