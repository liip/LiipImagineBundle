<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Imagine\Filter\PostProcessor;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Filter\PostProcessor\MozJpegPostProcessor;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers \Liip\ImagineBundle\Imagine\Filter\PostProcessor\MozJpegPostProcessor
 */
class MozJpegPostProcessorTest extends AbstractTest
{
    public function testMozJpegPostProcessor()
    {
        $mozJpegPostProcessor = new MozJpegPostProcessor(
            __DIR__.'/../../../Fixtures/bash/empty-command.sh'
        );

        $binary = new Binary('content', 'image/jpg', 'jpg');
        $result = $mozJpegPostProcessor->process($binary);

        $this->assertInstanceOf(BinaryInterface::class, $result);
    }
}
