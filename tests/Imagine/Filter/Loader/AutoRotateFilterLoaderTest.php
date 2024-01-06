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

use Imagine\Image\ImageInterface;
use Liip\ImagineBundle\Imagine\Filter\Loader\AutoRotateFilterLoader;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers \Liip\ImagineBundle\Imagine\Filter\Loader\AutoRotateFilterLoader
 */
class AutoRotateFilterLoaderTest extends AbstractTest
{
    public function testUseAutorotateFromImagine(): void
    {
        $loader = new AutoRotateFilterLoader();

        $image = $this->createMock(ImageInterface::class);
        $image->expects($this->once())
            ->method('metadata');

        $loader->load($image);
    }
}
