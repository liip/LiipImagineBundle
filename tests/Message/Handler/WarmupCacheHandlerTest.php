<?php

declare(strict_types=1);

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Message\Handler;

use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Message\Handler\WarmupCacheHandler;
use Liip\ImagineBundle\Message\WarmupCache;
use Liip\ImagineBundle\Service\FilterService;
use Liip\ImagineBundle\Tests\Functional\AbstractWebTestCase;

/**
 * @covers \Liip\ImagineBundle\Message\Handler\WarmupCacheHandler
 */
class WarmupCacheHandlerTest extends AbstractWebTestCase
{
    public function testCouldBeConstructedWithExpectedArguments(): void
    {
        static::createClient();

        $handler = new WarmupCacheHandler(
            $this->createFilterManagerMock(),
            $this->createFilterServiceMock()
        );

        $this->assertInstanceOf(WarmupCacheHandler::class, $handler);
    }

    public function testThrowIfMessageMissingPath(): void
    {
        static::createClient();

        $handler = new WarmupCacheHandler(
            $this->createFilterManagerMock(),
            $this->createFilterServiceMock()
        );

        $this->expectException(NotLoadableException::class);
        $this->expectExceptionMessage('Source image not resolvable "thePath"');

        $handler->__invoke(new WarmupCache('thePath', null, true));
    }

    /**
     * @return object|FilterManager
     */
    private function createFilterManagerMock()
    {
        return $this->getService('test.liip_imagine.filter.manager');
    }

    /**
     * @return object|FilterService
     */
    private function createFilterServiceMock()
    {
        return $this->getService('test.liip_imagine.service.filter');
    }
}
