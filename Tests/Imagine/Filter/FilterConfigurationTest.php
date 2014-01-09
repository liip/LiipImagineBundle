<?php

namespace Liip\ImagineBundle\Tests\Filter;

use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers Liip\ImagineBundle\Imagine\Filter\FilterConfiguration
 */
class FilterConfigurationTest extends AbstractTest
{
    public function testShouldGetSameConfigSetBefore()
    {
        $config = array(
            'quality' => 85,
            'format' => 'jpg',
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

    public function testShouldSetDefaultFormatIfNotProvidedOnSet()
    {
        $filterConfiguration = new FilterConfiguration();
        $filterConfiguration->set('profile_photo', array());

        $actualConfig = $filterConfiguration->get('profile_photo');

        $this->assertArrayHasKey('format', $actualConfig);
        $this->assertEquals('png', $actualConfig['format']);
    }

    public function testShouldSetDefaultQualityIfNotProvidedOnSet()
    {
        $filterConfiguration = new FilterConfiguration();
        $filterConfiguration->set('profile_photo', array());

        $actualConfig = $filterConfiguration->get('profile_photo');

        $this->assertArrayHasKey('quality', $actualConfig);
        $this->assertEquals(100, $actualConfig['quality']);
    }

    public function testShouldSetDefaultEmptyFiltersArrayIfNotProvidedOnSet()
    {
        $filterConfiguration = new FilterConfiguration();
        $filterConfiguration->set('profile_photo', array());

        $actualConfig = $filterConfiguration->get('profile_photo');

        $this->assertArrayHasKey('filters', $actualConfig);
        $this->assertEquals(array(), $actualConfig['filters']);
    }

    public function testGetUndefinedFilter()
    {
        $filterConfiguration = new FilterConfiguration();

        $this->setExpectedException('RuntimeException', 'Could not find configuration for a filter: thumbnail');
        $filterConfiguration->get('thumbnail');
    }

    public function testGetConfigSetViaConstructor()
    {
        $filterConfiguration = new FilterConfiguration(array(
            'profile_photo' => array(),
            'thumbnail' => array(),
        ));

        $this->assertInternalType('array', $filterConfiguration->get('profile_photo'));
        $this->assertInternalType('array', $filterConfiguration->get('thumbnail'));
    }

    public function testMergeDefaultAndRuntimeConfigOnGet()
    {
        $defaultConfig = array(
            'filters' => array(
                'thumbnail' => array(
                    'size' => array(180, 180),
                    'mode' => 'outbound',
                ),
            ),
            'cache' => 'web_path',
        );

        $runtimeConfig = array(
            'quality' => 85,
            'format' => 'jpg',
        );

        $expectedConfig = array(
            'quality' => 85,
            'format' => 'jpg',
            'filters' => array(
                'thumbnail' => array(
                    'size' => array(180, 180),
                    'mode' => 'outbound',
                ),
            ),
            'cache' => 'web_path',
        );

        $filterConfiguration = new FilterConfiguration();
        $filterConfiguration->set('profile_photo', $defaultConfig);

        $this->assertEquals($expectedConfig, $filterConfiguration->get('profile_photo', $runtimeConfig));
    }

    public function testShouldNotOverwriteDefaultFormatByRuntimeOneOnGet()
    {
        $defaultConfig = array(
            'format' => 'jpg',
        );

        $runtimeConfig = array(
            'format' => 'gif',
        );

        $filterConfiguration = new FilterConfiguration();
        $filterConfiguration->set('profile_photo', $defaultConfig);

        $config = $filterConfiguration->get('profile_photo', $runtimeConfig);
        $this->assertEquals('jpg', $config['format']);
    }
}
