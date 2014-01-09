<?php

namespace Liip\ImagineBundle\Tests\Filter;

use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Tests\AbstractTest;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers Liip\ImagineBundle\Imagine\Filter\FilterManager
 */
class FilterManagerTest extends AbstractTest
{
    public function testUseDefaultConfigOnApplyFilter()
    {
        $config = new FilterConfiguration(array(
            'thumbnail' => array(
                'format' => 'png',
                'quantity' => 100,
            )
        ));

        $image = $this->getMockImage();
        $image
            ->expects($this->once())
            ->method('get')
            ->with('png', array('quality' => 100))
            ->will($this->returnValue('aBinaryContent'))
        ;

        $filterManager = new FilterManager($config, $this->getMockImagine());

        $filterManager->applyFilter($image, 'thumbnail');
    }

    public function testUseRuntimeConfigOnApplyFilter()
    {
        $config = new FilterConfiguration(array(
            'thumbnail' => array()
        ));

        $image = $this->getMockImage();
        $image
            ->expects($this->once())
            ->method('get')
            ->with('jpg', array('quality' => 70))
            ->will($this->returnValue('aBinaryContent'))
        ;

        $filterManager = new FilterManager($config, $this->getMockImagine());

        $filterManager->applyFilter($image, 'thumbnail', array(
            'format' => 'jpg',
            'quality' => 70,
        ));
    }

    public function testMergeDefaultAndRuntimeConfigOnApplyFilter()
    {
        $config = new FilterConfiguration(array(
            'thumbnail' => array(
                'quality' => 70,
                'format' => 'gif',
            )
        ));

        $image = $this->getMockImage();
        $image
            ->expects($this->once())
            ->method('get')
            ->with('gif', array('quality' => 50))
            ->will($this->returnValue('aBinaryContent'))
        ;

        $filterManager = new FilterManager($config, $this->getMockImagine());

        $filterManager->applyFilter($image, 'thumbnail', array(
            'quality' => 50,
        ));
    }

    public function testUseDefaultFilterConfigOnApplyFilter()
    {
        $filterConfig = array(
            'foo' => 'fooVal',
            'bar' => 'barVal',
        );

        $config = new FilterConfiguration(array(
            'thumbnail' => array(
                'filters' => array(
                    'theFilter' => $filterConfig
                )
            )
        ));

        $image = $this->getMockImage();
        $image
            ->expects($this->once())
            ->method('get')
            ->with('png', array('quality' => 100))
            ->will($this->returnValue('aBinaryContent'))
        ;

        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $filterConfig)
            ->will($this->returnValue($image))
        ;

        $filterManager = new FilterManager($config, $this->getMockImagine());
        $filterManager->addLoader('theFilter', $loader);

        $filterManager->applyFilter($image, 'thumbnail');
    }

    public function testUseCustomFilterConfigOnApplyFilter()
    {
        $filterConfig = array(
            'foo' => 'fooVal',
            'bar' => 'barVal',
        );

        $config = new FilterConfiguration(array(
            'thumbnail' => array(
                'filters' => array()
            )
        ));

        $image = $this->getMockImage();
        $image
            ->expects($this->once())
            ->method('get')
            ->with('png', array('quality' => 100))
            ->will($this->returnValue('aBinaryContent'))
        ;

        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $filterConfig)
            ->will($this->returnValue($image))
        ;

        $filterManager = new FilterManager($config, $this->getMockImagine());
        $filterManager->addLoader('theFilter', $loader);

        $filterManager->applyFilter($image, 'thumbnail', array(
            'filters' => array(
                'theFilter' => $filterConfig
            ),
        ));
    }

    public function testMergeDefaultAndCustomFilterConfigOnApplyFilter()
    {
        $filterConfig = array(
            'foo' => 'fooVal',
            'bar' => 'barDefaultVal',
        );

        $config = new FilterConfiguration(array(
            'thumbnail' => array(
                'filters' => array(
                    'theFilter' => $filterConfig
                )
            )
        ));

        $image = $this->getMockImage();
        $image
            ->expects($this->once())
            ->method('get')
            ->with('png', array('quality' => 100))
        ;

        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), array(
                'foo' => 'fooVal',
                'bar' => 'barCustomVal'
            ))
            ->will($this->returnValue($image))
        ;

        $filterManager = new FilterManager($config, $this->getMockImagine());
        $filterManager->addLoader('theFilter', $loader);

        $filterManager->applyFilter($image, 'thumbnail', array(
            'filters' => array(
                'theFilter' => array(
                    'bar' => 'barCustomVal'
                )
            ),
        ));
    }

    public function testExecuteAllConfiguredFiltersOnApplyFilter()
    {
        $config = new FilterConfiguration(array(
            'thumbnail' => array(
                'filters' => array(
                    'firstFilter' => array(),
                    'secondFilter' => array(),
                )
            )
        ));

        $image = $this->getMockImage();
        $image
            ->expects($this->once())
            ->method('get')
            ->with('png', array('quality' => 100))
            ->will($this->returnValue('aBinaryContent'))
        ;

        $firstLoader = $this->getMockLoader();
        $firstLoader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), array())
            ->will($this->returnValue($image))
        ;

        $secondLoader = $this->getMockLoader();
        $secondLoader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), array())
            ->will($this->returnValue($image))
        ;

        $filterManager = new FilterManager($config, $this->getMockImagine());
        $filterManager->addLoader('firstFilter', $firstLoader);
        $filterManager->addLoader('secondFilter', $secondLoader);

        $filterManager->applyFilter($image, 'thumbnail');
    }

    public function testThrowsIfRequiredFilterWasNotAddedOnApplyFilter()
    {
        $config = new FilterConfiguration(array(
            'thumbnail' => array(
                'filters' => array(
                    'theFilter' => array(),
                )
            )
        ));

        $filterManager = new FilterManager($config, $this->getMockImagine());

        $this->setExpectedException('InvalidArgumentException', 'Could not find filter loader for "theFilter" filter type');
        $filterManager->applyFilter($this->getMockImage(), 'thumbnail');
    }

    protected function getMockLoader()
    {
        return $this->getMock('Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface');
    }

    protected function getMockImagine()
    {
        return $this->getMock('Imagine\Image\ImagineInterface');
    }
}
