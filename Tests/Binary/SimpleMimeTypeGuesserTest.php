<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Binary;

use Liip\ImagineBundle\Binary\SimpleMimeTypeGuesser;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

/**
 * @covers \Liip\ImagineBundle\Binary\SimpleMimeTypeGuesser<extended>
 */
class SimpleMimeTypeGuesserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return SimpleMimeTypeGuesser
     */
    private function getSimpleMimeTypeGuesser()
    {
        return new SimpleMimeTypeGuesser(MimeTypeGuesser::getInstance());
    }

    public function testCouldBeConstructedWithSymfonyMimeTypeGuesserAsFirstArgument()
    {
        $this->getSimpleMimeTypeGuesser();
    }

    public function testImplementsMimeTypeGuesserInterface()
    {
        $this->assertInstanceOf('\Liip\ImagineBundle\Binary\MimeTypeGuesserInterface', $this->getSimpleMimeTypeGuesser());
    }

    /**
     * @return array[]
     */
    public static function provideImageData()
    {
        return array(
            'gif' => array(__DIR__.'/../Fixtures/assets/cats.gif', 'image/gif'),
            'png' => array(__DIR__.'/../Fixtures/assets/cats.png', 'image/png'),
            'jpg' => array(__DIR__.'/../Fixtures/assets/cats.jpeg', 'image/jpeg'),
            'pdf' => array(__DIR__.'/../Fixtures/assets/cats.pdf', 'application/pdf'),
            'txt' => array(__DIR__.'/../Fixtures/assets/cats.txt', 'text/plain'),
        );
    }

    /**
     * @dataProvider provideImageData
     *
     * @param string $fileName
     * @param string $mimeType
     */
    public function testGuessMimeType($fileName, $mimeType)
    {
        $this->assertEquals($mimeType, $this->getSimpleMimeTypeGuesser()->guess(file_get_contents($fileName)));
    }
}
