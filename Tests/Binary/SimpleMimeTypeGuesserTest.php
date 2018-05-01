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

use Liip\ImagineBundle\Binary\MimeTypeGuesserInterface;
use Liip\ImagineBundle\Binary\SimpleMimeTypeGuesser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

/**
 * @covers \Liip\ImagineBundle\Binary\SimpleMimeTypeGuesser<extended>
 */
class SimpleMimeTypeGuesserTest extends TestCase
{
    public function testCouldBeConstructedWithSymfonyMimeTypeGuesserAsFirstArgument()
    {
        $guesser = $this->getSimpleMimeTypeGuesser();

        $this->assertInstanceOf(SimpleMimeTypeGuesser::class, $guesser);
    }

    public function testImplementsMimeTypeGuesserInterface()
    {
        $this->assertInstanceOf(MimeTypeGuesserInterface::class, $this->getSimpleMimeTypeGuesser());
    }

    /**
     * @return array[]
     */
    public static function provideImageData()
    {
        return [
            'gif' => [__DIR__.'/../Fixtures/assets/cats.gif', 'image/gif'],
            'png' => [__DIR__.'/../Fixtures/assets/cats.png', 'image/png'],
            'jpg' => [__DIR__.'/../Fixtures/assets/cats.jpeg', 'image/jpeg'],
            'pdf' => [__DIR__.'/../Fixtures/assets/cats.pdf', 'application/pdf'],
            'txt' => [__DIR__.'/../Fixtures/assets/cats.txt', 'text/plain'],
        ];
    }

    /**
     * @dataProvider provideImageData
     *
     * @param string $fileName
     * @param string $mimeType
     *
     * @throws \Exception
     */
    public function testGuessMimeType($fileName, $mimeType)
    {
        $this->assertSame($mimeType, $this->getSimpleMimeTypeGuesser()->guess(file_get_contents($fileName)));
    }

    /**
     * @return SimpleMimeTypeGuesser
     */
    private function getSimpleMimeTypeGuesser()
    {
        return new SimpleMimeTypeGuesser(MimeTypeGuesser::getInstance());
    }
}
