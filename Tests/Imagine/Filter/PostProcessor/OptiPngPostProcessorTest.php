<?php

namespace Liip\ImagineBundle\Tests\Filter\PostProcessor;

use Liip\ImagineBundle\Imagine\Filter\PostProcessor\OptiPngPostProcessor;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Tests\Filter\PostProcessor\FilterTest;
use Symfony\Component\Process\ExecutableFinder;

/**
 * @covers Liip\ImagineBundle\Imagine\Filter\PostProcessor\OptiPngPostProcessor
 */
class OptiPngPostProcessorTest extends FilterTest
{
    private $postProcessor;

    protected function setUp()
    {
        parent::setUp();

        if (!$optipngBin = $this->findExecutable('optipng', 'OPTIPNG_BIN')) {
            $this->markTestSkipped('Unable to find `optipng` executable.');
        }

        $this->postProcessor = new OptiPngPostProcessor($optipngBin);
    }

    protected function tearDown()
    {
        $this->postProcessor = null;
    }

    public function testProcess()
    {
        $binary = new Binary(file_get_contents($this->fixturesDir.'/assets/cats.png'), 'image/png', 'png');

        $before = $binary->getContent();
        $binaryProcessed = $this->postProcessor->process($binary);

        $this->assertNotEmpty($binaryProcessed->getContent(), '->process() sets content');
        $this->assertNotEquals($before, $binaryProcessed->getContent(), '->process() changes the content');
        $this->assertEquals($binary->getMimeType(), $binaryProcessed->getMimeType(), '->process() doest not change mime type');
    }
}
