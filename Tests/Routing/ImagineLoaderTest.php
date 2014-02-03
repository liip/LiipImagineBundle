<?php
namespace Liip\ImagineBundle\Tests\Routing;

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
        $loader = new ImagineLoader('anAction', '', array());

        $this->assertTrue($loader->supports('aResource', 'imagine'));
    }

    public function testReturnFalseIfResourceTypeNotImagineOnSupports()
    {
        $loader = new ImagineLoader('anAction', '', array());

        $this->assertFalse($loader->supports('aResource', 'notImagine'));
    }

    public function testReturnCollectionWithOneRouterIfOneFilterPassedOnLoad()
    {
        $loader = new ImagineLoader('liip_imagine.controller:filterAction', '/media/cache', array(
            'thumbnail' => array('filter_config_here')
        ));

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
}
