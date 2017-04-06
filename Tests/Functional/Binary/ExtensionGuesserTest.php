<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Functional\Binary;

use Liip\ImagineBundle\Tests\Functional\AbstractWebTestCase;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;

class ExtensionGuesserTest extends AbstractWebTestCase
{
    public function testCouldBeGetFromContainerAsService()
    {
        $this->createClient();

        $this->assertInstanceOf(
            ExtensionGuesser::class,
            self::$kernel->getContainer()->get('liip_imagine.extension_guesser')
        );
    }
}
