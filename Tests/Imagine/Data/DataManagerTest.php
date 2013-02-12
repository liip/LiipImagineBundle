<?php

namespace Liip\ImagineBundle\Tests\Imagine\Data;

use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Tests\AbstractTest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers Liip\ImagineBundle\Imagine\Data\DataManager
 */
class DataManagerTest extends AbstractTest
{
    protected $loader;

    public function testDefaultLoaderUsedIfNoneSet()
    {
        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('find')
            ->with('cats.jpeg')
        ;

        $config = $this->getMockFilterConfiguration();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'size' => array(180, 180),
                'mode' => 'outbound',
                'data_loader' => null,
            )))
        ;

        $dataManager = new DataManager($config, 'default');
        $dataManager->addLoader('default', $loader);

        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    public function testFindWithoutLoader()
    {
        $config = $this->getMockFilterConfiguration();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue(array(
                'size' => array(180, 180),
                'mode' => 'outbound',
                'data_loader' => null,
            )))
        ;

        $dataManager = new DataManager($config);

        $this->setExpectedException('InvalidArgumentException', 'Could not find data loader for "thumbnail" filter type');
        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    protected function getMockLoader()
    {
        return $this->getMock('Liip\ImagineBundle\Imagine\Data\Loader\LoaderInterface');
    }
}
