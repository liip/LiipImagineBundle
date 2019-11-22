<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Filter;

use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers \Liip\ImagineBundle\Imagine\Filter\FilterConfiguration
 */
class FilterConfigurationTest extends AbstractTest
{
    public function testSetAndGetFilter()
    {
        $config = [
            'filters' => [
                'thumbnail' => [
                    'size' => [180, 180],
                    'mode' => 'outbound',
                ],
            ],
            'cache' => 'web_path',
        ];

        $filterConfiguration = new FilterConfiguration();
        $filterConfiguration->set('profile_photo', $config);

        $this->assertSame($config, $filterConfiguration->get('profile_photo'));
    }

    public function testReturnAllFilters()
    {
        $filterConfiguration = new FilterConfiguration();
        $filterConfiguration->set('foo', ['fooConfig']);
        $filterConfiguration->set('bar', ['barConfig']);

        $filters = $filterConfiguration->all();

        $this->assertIsArray($filters);

        $this->assertArrayHasKey('foo', $filters);
        $this->assertSame(['fooConfig'], $filters['foo']);

        $this->assertArrayHasKey('bar', $filters);
        $this->assertSame(['barConfig'], $filters['bar']);
    }

    public function testGetUndefinedFilter()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not find configuration for a filter: thumbnail');

        $filterConfiguration = new FilterConfiguration();
        $filterConfiguration->get('thumbnail');
    }

    public function testShouldGetSameConfigSetBefore()
    {
        $config = [
            'quality' => 85,
            'format' => 'jpg',
            'filters' => [
                'thumbnail' => [
                    'size' => [180, 180],
                    'mode' => 'outbound',
                ],
            ],
            'cache' => 'web_path',
        ];

        $filterConfiguration = new FilterConfiguration();
        $filterConfiguration->set('profile_photo', $config);

        $this->assertSame($config, $filterConfiguration->get('profile_photo'));
    }

    public function testGetConfigSetViaConstructor()
    {
        $filterConfiguration = new FilterConfiguration([
            'profile_photo' => [],
            'thumbnail' => [],
        ]);

        $this->assertIsArray($filterConfiguration->get('profile_photo'));
        $this->assertIsArray($filterConfiguration->get('thumbnail'));
    }
}
