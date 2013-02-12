<?php

namespace Liip\ImagineBundle\Tests\Imagine\Data\Transformer;

use Liip\ImagineBundle\Imagine\Data\Transformer\PdfTransformer;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers Liip\ImagineBundle\Imagine\Data\Transformer\PdfTransformer
 */
class PdfTransformerTest extends AbstractTest
{
    public function setUp()
    {
        if (!class_exists('Imagick')) {
            $this->markTestSkipped('Imagick is not available.');
        }

        parent::setUp();
    }

    public function testApplyWritesPng()
    {
        $pdfFilename = $this->tempDir.'/cats.pdf';
        $pngFilename = $pdfFilename.'.png';

        $pdf = $this->fixturesDir.'/assets/cats.pdf';
        $this->filesystem->copy($pdf, $pdfFilename);
        $this->assertTrue(file_exists($pdfFilename));

        $transformer = new PdfTransformer(new \Imagick());
        $absolutePath = $transformer->apply($pdfFilename);

        $this->assertEquals($pngFilename, $absolutePath);
        $this->assertTrue(file_exists($pngFilename));
        $this->assertNotEmpty(file_get_contents($pngFilename));
    }

    public function testApplyDoesNotOverwriteExisting()
    {
        $pdfFilename = $this->tempDir.'/cats.pdf';
        $pngFilename = $pdfFilename.'.png';
        $this->filesystem->touch(array(
            $pdfFilename,
            $pngFilename,
        ));

        $transformer = new PdfTransformer(new \Imagick());
        $absolutePath = $transformer->apply($pdfFilename);

        $this->assertEquals($pngFilename, $absolutePath);
        $this->assertEmpty(file_get_contents($pngFilename));
    }
}
