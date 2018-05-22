<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Functional\Binary\Loader;

use Liip\ImagineBundle\Binary\Loader\ChainLoader;
use Liip\ImagineBundle\Tests\Functional\AbstractWebTestCase;

/**
 * @covers \Liip\ImagineBundle\Binary\Loader\ChainLoader
 */
class ChainLoaderTest extends AbstractWebTestCase
{
    public function testFind(): void
    {
        static::createClient();

        $loader = $this->getLoader('baz');

        foreach (['images/cats.jpeg', 'images/cats2.jpeg', 'file.ext', 'bar-bundle-file.ext', 'foo-bundle-file.ext'] as $file) {
            $this->assertNotNull($loader->find($file));
        }
    }

    /**
     * @param string $name
     *
     * @return ChainLoader|object
     */
    private function getLoader(string $name): ChainLoader
    {
        return $this->getService(sprintf('liip_imagine.binary.loader.%s', $name));
    }
}
