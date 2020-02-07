<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Templating;

use Liip\ImagineBundle\Templating\FilterExtension;
use Liip\ImagineBundle\Tests\AbstractTest;
use Twig\Extension\AbstractExtension;

/**
 * @covers \Liip\ImagineBundle\Templating\FilterExtension
 */
class FilterExtensionTest extends AbstractTest
{
    public function testAddsFilterMethodToFiltersList(): void
    {
        $this->assertCount(1, $this->createTemplatingMock()->getFilters());
    }

    protected function createTemplatingMock(): FilterExtension
    {
        if (!class_exists(AbstractExtension::class)) {
            $this->markTestSkipped('Requires the twig/twig package.');
        }

        $mock = new FilterExtension();

        $this->assertInstanceOf(FilterExtension::class, $mock);

        return $mock;
    }
}
