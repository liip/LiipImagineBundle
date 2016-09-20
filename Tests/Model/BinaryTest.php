<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Model;

use Liip\ImagineBundle\Model\Binary;

/**
 * @covers Liip\ImagineBundle\Model\Binary
 */
class BinaryTest extends \PHPUnit_Framework_TestCase
{
    public function testImplementsBinaryInterface()
    {
        $rc = new \ReflectionClass('Liip\ImagineBundle\Model\Binary');

        $this->assertTrue($rc->implementsInterface('Liip\ImagineBundle\Binary\BinaryInterface'));
    }

    public function testAllowGetContentSetInConstructor()
    {
        $image = new Binary('theContent', 'image/png', 'png');

        $this->assertEquals('theContent', $image->getContent());
    }

    public function testAllowGetMimeTypeSetInConstructor()
    {
        $image = new Binary('aContent', 'image/png', 'png');

        $this->assertEquals('image/png', $image->getMimeType());
    }

    public function testAllowGetFormatSetInConstructor()
    {
        $image = new Binary('aContent', 'image/png', 'png');

        $this->assertEquals('png', $image->getFormat());
    }
}
