<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Async;

use Enqueue\Bundle\EnqueueBundle;
use Liip\ImagineBundle\Async\ResolveCache;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Liip\ImagineBundle\Async\ResolveCache
 */
class ResolveCacheTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        if (!class_exists(EnqueueBundle::class)) {
            self::markTestSkipped('The tests are run without enqueue integration. Skip them');
        }
    }

    public function testCouldBeJsonSerializedWithoutFiltersAndForce(): void
    {
        $message = new ResolveCache('thePath');

        $this->assertSame('{"path":"thePath","filters":null,"force":false}', json_encode($message));
    }

    public function testCouldBeJsonSerializedWithFilters(): void
    {
        $message = new ResolveCache('thePath', ['fooFilter', 'barFilter']);

        $this->assertSame('{"path":"thePath","filters":["fooFilter","barFilter"],"force":false}', json_encode($message));
    }

    public function testCouldBeJsonSerializedWithFiltersAndForce(): void
    {
        $message = new ResolveCache('thePath', ['fooFilter', 'barFilter'], true);

        $this->assertSame('{"path":"thePath","filters":["fooFilter","barFilter"],"force":true}', json_encode($message));
    }

    public function testCouldBeJsonDeSerializedWithoutFiltersAndForce(): void
    {
        $message = ResolveCache::jsonDeserialize('{"path":"thePath","filters":null,"force":false}');

        $this->assertSame('thePath', $message->getPath());
        $this->assertNull($message->getFilters());
        $this->assertFalse($message->isForce());
    }

    public function testCouldBeJsonDeSerializedWithFilters(): void
    {
        $message = ResolveCache::jsonDeserialize('{"path":"thePath","filters":["fooFilter","barFilter"],"force":false}');

        $this->assertSame('thePath', $message->getPath());
        $this->assertSame(['fooFilter', 'barFilter'], $message->getFilters());
        $this->assertFalse($message->isForce());
    }

    public function testCouldBeJsonDeSerializedWithFiltersAndForce(): void
    {
        $message = ResolveCache::jsonDeserialize('{"path":"thePath","filters":["fooFilter","barFilter"],"force":true}');

        $this->assertSame('thePath', $message->getPath());
        $this->assertSame(['fooFilter', 'barFilter'], $message->getFilters());
        $this->assertTrue($message->isForce());
    }

    public function testCouldBeJsonDeSerializedWithOnlyPath(): void
    {
        $message = ResolveCache::jsonDeserialize('{"path":"thePath"}');

        $this->assertSame('thePath', $message->getPath());
        $this->assertNull($message->getFilters());
        $this->assertFalse($message->isForce());
    }

    public function testThrowIfMessageMissingPathOnJsonDeserialize(): void
    {
        $this->expectException(\Liip\ImagineBundle\Exception\LogicException::class);
        $this->expectExceptionMessage('The message does not contain "path" but it is required.');

        ResolveCache::jsonDeserialize('{}');
    }

    public function testThrowIfMessageContainsNotSupportedFilters(): void
    {
        $this->expectException(\Liip\ImagineBundle\Exception\LogicException::class);
        $this->expectExceptionMessage('The message filters could be either null or array.');

        ResolveCache::jsonDeserialize('{"path": "aPath", "filters": "stringFilterIsNotAllowed"}');
    }
}
