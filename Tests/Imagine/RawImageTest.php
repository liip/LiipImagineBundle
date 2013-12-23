<?php
namespace Liip\ImagineBundle\Tests\Imagine;

use Liip\ImagineBundle\Imagine\RawImage;

class RawImageTest extends \PHPUnit_Framework_TestCase
{
    public function testAllowGetContentSetInConstructor()
    {
        $image = new RawImage('theContent', 'image/png');

        $this->assertEquals('theContent', $image->getContent());
    }

    public function testShouldReturnContentWhenRawImageConvertedToString()
    {
        $image = new RawImage('theContent', 'image/png');

        $this->assertEquals('theContent', (string) $image);
    }


    public function testAllowGetMimeTypeSetInConstructor()
    {
        $image = new RawImage('aContent', 'image/png');

        $this->assertEquals('image/png', $image->getMimeType());
    }

    public function testAllowGetFormatGuessedByMimeTypeSetInConstructor()
    {
        $image = new RawImage('aContent', 'image/png');

        $this->assertEquals('png', $image->getFormat());
    }
}