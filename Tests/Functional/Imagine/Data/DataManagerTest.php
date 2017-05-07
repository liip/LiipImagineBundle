<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Functional\Imagine\Data;

use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Tests\Functional\AbstractWebTestCase;

/**
 * @covers \Liip\ImagineBundle\Imagine\Data\DataManager
 */
class DataManagerTest extends AbstractWebTestCase
{
    public function testCouldBeGetFromContainerAsService()
    {
        $this->createClient();

        $this->assertInstanceOf(
            DataManager::class,
            self::$kernel->getContainer()->get('liip_imagine.data.manager')
        );
    }
}
