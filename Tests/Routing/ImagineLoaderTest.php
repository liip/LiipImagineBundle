<?php
namespace Liip\ImagineBundle\Tests\Routing;

use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Liip\ImagineBundle\Routing\ImagineLoader;

/**
 * @covers Liip\ImagineBundle\Routing\ImagineLoader
 */
class ImagineLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testSubClassOfLoader()
    {
        $rc = new \ReflectionClass('Liip\ImagineBundle\Routing\ImagineLoader');

        $this->assertTrue($rc->isSubclassOf('Symfony\Component\Config\Loader\Loader'));
    }

    public function testReturnTrueIfResourceTypeImagineOnSupports()
    {
        $loader = new ImagineLoader($this->createFilterConfigurationMock(), 'anAction', '');

        $this->assertTrue($loader->supports('aResource', 'imagine'));
    }

    public function testReturnFalseIfResourceTypeNotImagineOnSupports()
    {
        $loader = new ImagineLoader($this->createFilterConfigurationMock(), 'anAction', '');

        $this->assertFalse($loader->supports('aResource', 'notImagine'));
    }

    public function testReturnCollectionWithOneRouterIfOneFilterPassedOnLoad()
    {
        $filterConfiguration = $this->createFilterConfigurationMock();
        $filterConfiguration
            ->expects($this->once())
            ->method('all')
            ->will($this->returnValue(array(
                'thumbnail' => array('filter_config_here')
            )))
        ;

        $loader = new ImagineLoader($filterConfiguration, 'liip_imagine.controller:filterAction', '/media/cache');

        $result = $loader->load('aResource', 'aType');

        $this->assertInstanceOf('Symfony\Component\Routing\RouteCollection', $result);
        $this->assertCount(1, $result);

        $route = $result->get('_imagine_thumbnail');
        $this->assertEquals(
            array(
                '_controller' => 'liip_imagine.controller:filterAction',
                'filter' => 'thumbnail',
            ),
            $route->getDefaults()
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|FilterConfiguration
     */
    protected function createFilterConfigurationMock()
    {
        return $this->getMock('Liip\ImagineBundle\Imagine\Filter\FilterConfiguration', array(), array(), '', false);
    }
}
