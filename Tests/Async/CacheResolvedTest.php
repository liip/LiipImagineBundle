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
use Liip\ImagineBundle\Async\CacheResolved;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Liip\ImagineBundle\Async\CacheResolved
 */
class CacheResolvedTest extends TestCase
{
    public function setUp(): void
    {
        if (!class_exists(EnqueueBundle::class)) {
            self::markTestSkipped('The tests are run without enqueue integration. Skip them');
        }
    }

    public function testCouldBeJsonSerialized(): void
    {
        $message = new CacheResolved('thePath', [
            'fooFilter' => 'http://example.com/fooFilter/thePath',
            'barFilter' => 'http://example.com/barFilter/thePath',
        ]);

        $this->assertSame(
            '{"path":"thePath","uris":{"fooFilter":"http:\/\/example.com\/fooFilter\/thePath","barFilter":"http:\/\/example.com\/barFilter\/thePath"}}',
            json_encode($message)
        );
    }

    public function testCouldBeJsonDeSerialized(): void
    {
        $message = CacheResolved::jsonDeserialize('{"path":"thePath","uris":{"fooFilter":"http:\/\/example.com\/fooFilter\/thePath","barFilter":"http:\/\/example.com\/barFilter\/thePath"}}');

        $this->assertSame('thePath', $message->getPath());
        $this->assertSame([
            'fooFilter' => 'http://example.com/fooFilter/thePath',
            'barFilter' => 'http://example.com/barFilter/thePath',
        ], $message->getUris());
    }
}
