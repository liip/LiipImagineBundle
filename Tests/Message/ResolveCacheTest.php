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

namespace Liip\ImagineBundle\Tests\Message;

use Liip\ImagineBundle\Message\ResolveCache;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @covers \Liip\ImagineBundle\Message\ResolveCache
 */
class ResolveCacheTest extends TestCase
{
    protected function setUp(): void
    {
        if (!interface_exists(MessageBusInterface::class)) {
            $this->markTestSkipped('Requires the symfony/messenger package.');
        }
    }

    public function testMessageWithoutFiltersAndForce(): void
    {
        $message = new ResolveCache('thePath');

        $this->assertSame('thePath', $message->getPath());
        $this->assertNull($message->getFilters());
        $this->assertFalse($message->isForce());
    }

    public function testMessageWithFilters(): void
    {
        $message = new ResolveCache('thePath', ['fooFilter', 'barFilter']);

        $this->assertSame('thePath', $message->getPath());
        $this->assertSame(['fooFilter', 'barFilter'], $message->getFilters());
        $this->assertFalse($message->isForce());
    }

    public function testMessageWithFiltersAndForce(): void
    {
        $message = new ResolveCache('thePath', ['fooFilter', 'barFilter'], true);

        $this->assertSame('thePath', $message->getPath());
        $this->assertSame(['fooFilter', 'barFilter'], $message->getFilters());
        $this->assertTrue($message->isForce());
    }
}
