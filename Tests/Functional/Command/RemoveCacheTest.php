<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Functional\Command;

use Liip\ImagineBundle\Command\RemoveCacheCommand;

/**
 * @covers \Liip\ImagineBundle\Command\RemoveCacheCommand
 */
class RemoveCacheTest extends AbstractCommandTestCase
{
    public function testExecuteSuccessfullyWithEmptyCacheAndWithoutParameters()
    {
        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg');

        $this->executeConsole(new RemoveCacheCommand());
    }

    public function testExecuteSuccessfullyWithEmptyCacheAndOnePathAndOneFilter()
    {
        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg');

        $this->executeConsole(
            new RemoveCacheCommand(),
            array(
                'paths' => array('images/cats.jpeg'),
                '--filters' => array('thumbnail_web_path'),
        ));
    }

    public function testExecuteSuccessfullyWithEmptyCacheAndMultiplePaths()
    {
        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg');

        $this->executeConsole(
            new RemoveCacheCommand(),
            array('paths' => array('images/cats.jpeg', 'images/cats2.jpeg'))
        );
    }

    public function testExecuteSuccessfullyWithEmptyCacheAndMultipleFilters()
    {
        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg');

        $this->executeConsole(
            new RemoveCacheCommand(),
            array('--filters' => array('thumbnail_web_path', 'thumbnail_default'))
        );
    }

    public function testShouldRemoveAllCacheIfParametersDoesNotPassed()
    {
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg',
            'anImageContent'
        );
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_web_path/images/cats2.jpeg',
            'anImageContent2'
        );
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_default/images/cats.jpeg',
            'anImageContent'
        );

        $this->executeConsole(new RemoveCacheCommand());

        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg');
        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_web_path/images/cats2.jpeg');
        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_default/images/cats.jpeg');
    }

    public function testShouldRemoveCacheBySinglePath()
    {
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg',
            'anImageContent'
        );
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_default/images/cats.jpeg',
            'anImageContent'
        );
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_web_path/images/cats2.jpeg',
            'anImageContent2'
        );
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_default/images/cats2.jpeg',
            'anImageContent2'
        );

        $this->executeConsole(
            new RemoveCacheCommand(),
            array('paths' => array('images/cats.jpeg'))
        );

        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg');
        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_default/images/cats.jpeg');
        $this->assertFileExists($this->cacheRoot.'/thumbnail_web_path/images/cats2.jpeg');
        $this->assertFileExists($this->cacheRoot.'/thumbnail_default/images/cats2.jpeg');
    }

    public function testShouldRemoveCacheByMultiplePaths()
    {
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg',
            'anImageContent'
        );
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_default/images/cats.jpeg',
            'anImageContent'
        );
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_web_path/images/cats2.jpeg',
            'anImageContent2'
        );
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_default/images/cats2.jpeg',
            'anImageContent2'
        );

        $this->executeConsole(
            new RemoveCacheCommand(),
            array('paths' => array('images/cats.jpeg', 'images/cats2.jpeg'))
        );

        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg');
        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_default/images/cats.jpeg');
        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_web_path/images/cats2.jpeg');
        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_default/images/cats2.jpeg');
    }

    public function testShouldRemoveCacheBySingleFilter()
    {
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg',
            'anImageContent'
        );
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_default/images/cats.jpeg',
            'anImageContent'
        );
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_web_path/images/cats2.jpeg',
            'anImageContent2'
        );
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_default/images/cats2.jpeg',
            'anImageContent2'
        );

        $this->executeConsole(
            new RemoveCacheCommand(),
            array('--filters' => array('thumbnail_default'))
        );

        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_default/images/cats.jpeg');
        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_default/images/cats2.jpeg');
        $this->assertFileExists($this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg');
        $this->assertFileExists($this->cacheRoot.'/thumbnail_web_path/images/cats2.jpeg');
    }

    public function testShouldRemoveCacheByMultipleFilters()
    {
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg',
            'anImageContent'
        );
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_default/images/cats.jpeg',
            'anImageContent'
        );
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_web_path/images/cats2.jpeg',
            'anImageContent2'
        );
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_default/images/cats2.jpeg',
            'anImageContent2'
        );

        $this->executeConsole(
            new RemoveCacheCommand(),
            array('--filters' => array('thumbnail_default', 'thumbnail_web_path'))
        );

        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg');
        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_default/images/cats.jpeg');
        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_web_path/images/cats2.jpeg');
        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_default/images/cats2.jpeg');
    }

    public function testShouldRemoveCacheByOnePathAndMultipleFilters()
    {
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg',
            'anImageContent'
        );
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_default/images/cats.jpeg',
            'anImageContent'
        );
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_web_path/images/cats2.jpeg',
            'anImageContent2'
        );

        $this->executeConsole(
            new RemoveCacheCommand(),
            array(
                'paths' => array('images/cats.jpeg'),
                '--filters' => array('thumbnail_default', 'thumbnail_web_path'), )
        );

        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg');
        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_default/images/cats.jpeg');
        $this->assertFileExists($this->cacheRoot.'/thumbnail_web_path/images/cats2.jpeg');
    }

    public function testShouldRemoveCacheByMultiplePathsAndSingleFilter()
    {
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg',
            'anImageContent'
        );
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_default/images/cats.jpeg',
            'anImageContent'
        );
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_web_path/images/cats2.jpeg',
            'anImageContent2'
        );

        $this->executeConsole(
            new RemoveCacheCommand(),
            array(
                'paths' => array('images/cats.jpeg', 'images/cats2.jpeg'),
                '--filters' => array('thumbnail_web_path'), )
        );

        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg');
        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_web_path/images/cats2.jpeg');
        $this->assertFileExists($this->cacheRoot.'/thumbnail_default/images/cats.jpeg');
    }
}
