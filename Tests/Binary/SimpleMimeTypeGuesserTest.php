<?php

namespace Liip\ImagineBundle\Tests\Binary;

use Liip\ImagineBundle\Binary\SimpleMimeTypeGuesser;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

/**
 * @covers Liip\ImagineBundle\Binary\SimpleMimeTypeGuesser<extended>
 */
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
        $rc = new \ReflectionClass('Liip\ImagineBundle\Binary\SimpleMimeTypeGuesser');

        $this->assertTrue($rc->implementsInterface('Liip\ImagineBundle\Binary\MimeTypeGuesserInterface'));
    }

    public function testCouldBeConstructedWithSymfonyMimeTypeGuesserAsFirstArgument()
    {
        new SimpleMimeTypeGuesser(MimeTypeGuesser::getInstance());
    }

    /**
     * @dataProvider provideImages
     */
    public function testGuessMimeType($imageFile, $expectedMimeType)
    {
        $guesser = new SimpleMimeTypeGuesser(MimeTypeGuesser::getInstance());

        $this->assertEquals($expectedMimeType, $guesser->guess(file_get_contents($imageFile)));
    }
}
