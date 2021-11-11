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

use Liip\ImagineBundle\Templating\LazyFilterExtension;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers \Liip\ImagineBundle\Templating\LazyFilterExtension
 */
class LazyFilterExtensionTest extends AbstractTest
{
    private LazyFilterExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new LazyFilterExtension();
    }

    public function testAddsFilterMethodToFiltersList(): void
    {
        $this->assertCount(2, $this->extension->getFilters());
    }
}
