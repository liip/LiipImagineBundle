<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Functional\Imagine\Filter\Loader;

use Liip\ImagineBundle\Tests\Functional\AbstractWebTestCase;

/**
 * @covers \Liip\ImagineBundle\Imagine\Filter\Loader\ResampleFilterLoader
 */
class ResampleFilterLoaderTest extends AbstractWebTestCase
{
    public function testContainerHasService()
    {
        $this->createClient();

        $this->assertInstanceOf(
            '\Liip\ImagineBundle\Imagine\Filter\Loader\ResampleFilterLoader',
            self::$kernel->getContainer()->get('liip_imagine.filter.loader.resample')
        );
    }
}
