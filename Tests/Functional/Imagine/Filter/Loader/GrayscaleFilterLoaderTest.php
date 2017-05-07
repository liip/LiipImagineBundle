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

use Liip\ImagineBundle\Imagine\Filter\Loader\GrayscaleFilterLoader;
use Liip\ImagineBundle\Tests\Functional\AbstractWebTestCase;

/**
 * @covers \Liip\ImagineBundle\Imagine\Filter\Loader\GrayscaleFilterLoader
 *
 * @author Gregoire Humeau <gregoire.humeau@gmail.com>
 */
class GrayscaleFilterLoaderTest extends AbstractWebTestCase
{
    public function testCouldBeGetFromContainerAsService()
    {
        $this->createClient();

        $this->assertInstanceOf(
            GrayscaleFilterLoader::class,
                self::$kernel->getContainer()->get('liip_imagine.filter.loader.grayscale')
        );
    }
}
