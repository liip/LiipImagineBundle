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
use Liip\ImagineBundle\Message\Handler\ResolveCacheHandler;
use Liip\ImagineBundle\Message\ResolveCache;
use Liip\ImagineBundle\Service\FilterService;
use Liip\ImagineBundle\Tests\Functional\AbstractWebTestCase;
use ReflectionClass;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @requires PHP 7.1
 *
 * @covers \Liip\ImagineBundle\Message\Handler\ResolveCacheHandler
 */
class ResolveCacheHandlerTest extends AbstractWebTestCase
{
    protected function setUp(): void
    {
        if (!interface_exists(MessageBusInterface::class)) {
            $this->markTestSkipped('Requires the symfony/messenger package.');
        }
    }

    public function testShouldImplementMessageHandlerInterface(): void
    {
        $rc = new ReflectionClass(ResolveCacheHandler::class);

        $this->assertTrue($rc->implementsInterface(MessageHandlerInterface::class));
    }

    public function testCouldBeConstructedWithExpectedArguments(): void
    {
        static::createClient();

        $handler = new ResolveCacheHandler(
            $this->createFilterManagerMock(),
            $this->createFilterServiceMock()
        );

        $this->assertInstanceOf(ResolveCacheHandler::class, $handler);
    }

    public function testThrowIfMessageMissingPath(): void
    {
        static::createClient();

        $handler = new ResolveCacheHandler(
            $this->createFilterManagerMock(),
            $this->createFilterServiceMock()
        );

        $this->expectException(NotLoadableException::class);
        $this->expectExceptionMessage('Source image not resolvable "thePath"');

        $handler->__invoke(new ResolveCache('thePath', null, true));
    }

    /**
     * @return object|FilterManager
     */
    private function createFilterManagerMock()
    {
        return $this->getService('liip_imagine.filter.manager');
    }

    /**
     * @return object|FilterService
     */
    private function createFilterServiceMock()
    {
        return $this->getService('liip_imagine.service.filter');
    }
}
