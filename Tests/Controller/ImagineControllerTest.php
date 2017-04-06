<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Controller;

use Liip\ImagineBundle\Controller\ImagineController;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers \Liip\ImagineBundle\Controller\ImagineController
 */
class ImagineControllerTest extends AbstractTest
{
    public function testConstruction()
    {
        new ImagineController(
            $this->createDataManagerMock(),
            $this->createFilterManagerMock(),
            $this->createCacheManagerMock(),
            $this->createSignerInterfaceMock(),
            $this->createLoggerInterfaceMock()
        );
    }
}
