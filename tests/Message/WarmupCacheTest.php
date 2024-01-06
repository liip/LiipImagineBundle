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

use Liip\ImagineBundle\Message\WarmupCache;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Liip\ImagineBundle\Message\WarmupCache
 */
class WarmupCacheTest extends TestCase
{
    public function testMessageWithoutFiltersAndForce(): void
    {
        $message = new WarmupCache('thePath');

        $this->assertSame('thePath', $message->getPath());
        $this->assertNull($message->getFilters());
        $this->assertFalse($message->isForce());
    }

    public function testMessageWithFilters(): void
    {
        $message = new WarmupCache('thePath', ['fooFilter', 'barFilter']);

        $this->assertSame('thePath', $message->getPath());
        $this->assertSame(['fooFilter', 'barFilter'], $message->getFilters());
        $this->assertFalse($message->isForce());
    }

    public function testMessageWithFiltersAndForce(): void
    {
        $message = new WarmupCache('thePath', ['fooFilter', 'barFilter'], true);

        $this->assertSame('thePath', $message->getPath());
        $this->assertSame(['fooFilter', 'barFilter'], $message->getFilters());
        $this->assertTrue($message->isForce());
    }
}
