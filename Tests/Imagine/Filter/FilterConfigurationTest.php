<?php

namespace Liip\ImagineBundle\Tests\Filter;

use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers Liip\ImagineBundle\Imagine\Filter\FilterConfiguration
 */
class FilterConfigurationTest extends AbstractTest
{
    public function testSetAndGetFilter()
    {
        $config = array(
            'filters' => array(
                'thumbnail' => array(
                    'size' => array(180, 180),
                    'mode' => 'outbound',
                ),
            ),
            'cache' => 'web_path',
        );

        $filterConfiguration = new FilterConfiguration();
        $filterConfiguration->set('profile_photo', $config);

        $this->assertEquals($config, $filterConfiguration->get('profile_photo'));
    }

    public function testGetUndefinedFilter()
    {
        $filterConfiguration = new FilterConfiguration();

        $this->setExpectedException('RuntimeException', 'Filter not defined: thumbnail');
        $filterConfiguration->get('thumbnail');
    }
}
