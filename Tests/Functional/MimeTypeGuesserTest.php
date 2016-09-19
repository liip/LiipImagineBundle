<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Functional;

class MimeTypeGuesserTest extends WebTestCase
{
    public function testCouldBeGetFromContainerAsService()
    {
        $this->createClient();
        $guesser = self::$kernel->getContainer()->get('liip_imagine.mime_type_guesser');

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser', $guesser);
    }
}
