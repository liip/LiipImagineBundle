<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Functional\Imagine\Cache;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Tests\Functional\AbstractWebTestCase;

/**
 * @covers \Liip\ImagineBundle\Imagine\Cache\CacheManager
 */
class CacheManagerTest extends AbstractWebTestCase
{
    public function providePaths(): iterable
    {
        yield 'querystring' => [
            'path' => 'path?v51',
            'expectedUrl' => 'path%3Fv51',
        ];

        yield 'plus' => [
            'path' => 'path+x.jpg',
            'expectedUrl' => 'path+x.jpg',
        ];
    }

    /**
     * @dataProvider providePaths
     */
    public function testGetAsService($path, $expectedUrl): void
    {
        $this->createClient();

        /** @var CacheManager $manager */
        $manager = self::$kernel->getContainer()->get('liip_imagine.cache.manager');

        $this->assertSame(
            'http://localhost/media/cache/resolve/thumbnail_web_path/'.$expectedUrl,
            $manager->generateUrl($path, 'thumbnail_web_path')
        );
    }
}
