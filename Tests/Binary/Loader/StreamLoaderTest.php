<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Binary\Loader;

use Liip\ImagineBundle\Binary\Loader\StreamLoader;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers \Liip\ImagineBundle\Binary\Loader\StreamLoader<extended>
 */
class StreamLoaderTest extends AbstractTest
{
    /**
     * @expectedException \Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException
     * @expectedExceptionMessageRegExp {Source image file://.+ not found.}
     */
    public function testThrowsIfInvalidPathGivenOnFind()
    {
        $loader = new StreamLoader('file://');
        $loader->find($this->temporaryPath.'/invalid.jpeg');
    }

    public function testReturnImageContentOnFind()
    {
        $loader = new StreamLoader('file://');

        $this->assertSame(
            file_get_contents($this->fixturesPath.'/assets/cats.jpeg'),
            $loader->find($this->fixturesPath.'/assets/cats.jpeg')
        );
    }

    public function testReturnImageContentWhenStreamContextProvidedOnFind()
    {
        $loader = new StreamLoader('file://', stream_context_create());

        $this->assertSame(
            file_get_contents($this->fixturesPath.'/assets/cats.jpeg'),
            $loader->find($this->fixturesPath.'/assets/cats.jpeg')
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The given context is no valid resource
     */
    public function testThrowsIfInvalidResourceGivenInConstructor()
    {
        new StreamLoader('an-invalid-resource-name', true);
    }
}
