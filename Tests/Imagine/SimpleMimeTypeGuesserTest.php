<?php
namespace Liip\ImagineBundle\Tests\Imagine;

use Liip\ImagineBundle\Imagine\SimpleMimeTypeGuesser;

class SimpleMimeTypeGuesserTest extends \PHPUnit_Framework_TestCase
{
    public function provideImages()
    {
        return array(
            'gif' => array(__DIR__.'/../Fixtures/assets/cats.gif', 'image/gif'),
            'png' => array(__DIR__.'/../Fixtures/assets/cats.png', 'image/png'),
            'jpg' => array(__DIR__.'/../Fixtures/assets/cats.jpeg', 'image/jpeg'),
            'pdf' => array(__DIR__.'/../Fixtures/assets/cats.pdf', 'application/pdf'),
            'txt' => array(__DIR__.'/../Fixtures/assets/cats.txt', 'text/plain'),
        );
    }

    public function testImplementsMimeTypeGuesserInterface()
    {
        $rc = new \ReflectionClass('Liip\ImagineBundle\Imagine\SimpleMimeTypeGuesser');

        $this->assertTrue($rc->implementsInterface('Liip\ImagineBundle\Imagine\MimeTypeGuesserInterface'));
    }

    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new SimpleMimeTypeGuesser();
    }

    /**
     * @dataProvider provideImages
     */
    public function testGuessMimeType($imageFile, $expectedMimeType)
    {
        $guesser = new SimpleMimeTypeGuesser();

        $this->assertEquals($expectedMimeType, $guesser->guess(file_get_contents($imageFile)));
    }
}
