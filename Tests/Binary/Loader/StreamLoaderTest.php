<?php

namespace Liip\ImagineBundle\Tests\Binary\Loader;

use Liip\ImagineBundle\Binary\Loader\StreamLoader;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers Liip\ImagineBundle\Binary\Loader\StreamLoader<extended>
 */
class StreamLoaderTest extends AbstractTest
{
    public function testThrowsIfInvalidPathGivenOnFind()
    {
        $loader = new StreamLoader('file://');

        $this->setExpectedException(
            'Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException'
        );
        $loader->find($this->tempDir.'/invalid.jpeg');
    }

    public function testReturnImageContentOnFind()
    {
        $expectedContent = file_get_contents($this->fixturesDir.'/assets/cats.jpeg');

        $loader = new StreamLoader('file://');

        $this->assertSame(
            $expectedContent,
            $loader->find($this->fixturesDir.'/assets/cats.jpeg')
        );
    }

    public function testReturnImageContentWhenStreamContextProvidedOnFind()
    {
        $expectedContent = file_get_contents($this->fixturesDir.'/assets/cats.jpeg');

        $context = stream_context_create();

        $loader = new StreamLoader('file://', $context);

        $this->assertSame(
            $expectedContent,
            $loader->find($this->fixturesDir.'/assets/cats.jpeg')
        );
    }

    public function testThrowsIfInvalidResourceGivenInConstructor()
    {
        $this->setExpectedException('InvalidArgumentException', 'The given context is no valid resource.');

        new StreamLoader('not valid resource', true);
    }
}
