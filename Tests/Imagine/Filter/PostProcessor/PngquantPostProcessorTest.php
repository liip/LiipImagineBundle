<?php

namespace Liip\ImagineBundle\Tests\Imagine\Filter\PostProcessor;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Filter\PostProcessor\PngquantPostProcessor;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers \Liip\ImagineBundle\Imagine\Filter\PostProcessor\PngquantPostProcessor
 */
class PngquantPostProcessorTest extends AbstractTest
{
    public function testPngquantPostProcessor()
    {
        $pngquantPostProcessor = new PngquantPostProcessor(
            __DIR__.'/../../../Fixtures/bash/empty-command.sh'
        );

        $binary = new Binary('content', 'image/png', 'png');
        $result = $pngquantPostProcessor->process($binary);

        $this->assertInstanceOf(BinaryInterface::class, $result);
    }
}
