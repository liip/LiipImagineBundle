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

use Liip\ImagineBundle\Command\ResolveCacheCommand;

/**
 * @covers \Liip\ImagineBundle\Command\ResolveCacheCommand
 * @covers \Liip\ImagineBundle\Command\CacheCommandTrait
 */
class ResolveCacheCommandTest extends AbstractCommandTestCase
{
    public function testShouldResolveWithEmptyCache()
    {
        $images = array('images/cats.jpeg', 'images/cats2.jpeg');
        $filters = array('thumbnail_web_path');

        $this->assertImagesNotExist($images, $filters);

        $return = null;
        $output = $this->executeResolveCacheCommand($images, $filters, array(), $return);

        $this->assertSame(0, $return);
        $this->assertImagesExist($images, $filters);
        $this->assertImagesNotExist($images, array('thumbnail_default'));
        $this->assertOutputContainsResolvedImages($output, $images, $filters);
        $this->assertOutputContainsSummary($output, $images, $filters);

        $this->delResolvedImages($images, $filters);
    }

    public function testShouldResolveWithCacheExists()
    {
        $images = array('images/cats.jpeg');
        $filters = array('thumbnail_web_path');

        $this->putResolvedImages($images, $filters);

        $output = $this->executeResolveCacheCommand($images, $filters);

        $this->assertImagesExist($images, $filters);
        $this->assertImagesNotExist($images, array('thumbnail_default'));
        $this->assertOutputContainsCachedImages($output, $images, $filters);
        $this->assertOutputContainsSummary($output, $images, $filters);

        $this->delResolvedImages($images, $filters);
    }

    public function testShouldResolveWithFewPathsAndSingleFilter()
    {
        $images = array('images/cats.jpeg', 'images/cats2.jpeg');
        $filters = array('thumbnail_web_path');

        $output = $this->executeResolveCacheCommand($images, $filters);

        $this->assertImagesExist($images, $filters);
        $this->assertOutputContainsResolvedImages($output, $images, $filters);

        $output = $this->executeResolveCacheCommand($images, $filters);

        $this->assertImagesExist($images, $filters);
        $this->assertOutputContainsCachedImages($output, $images, $filters);
        $this->assertOutputContainsSummary($output, $images, $filters);

        $this->delResolvedImages($images, $filters);
    }

    public function testShouldResolveWithFewPathsSingleFilterAndPartiallyFullCache()
    {
        $imagesResolved = array('images/cats.jpeg');
        $imagesCached = array('images/cats2.jpeg');
        $images = array_merge($imagesResolved, $imagesCached);
        $filters = array('thumbnail_web_path');

        $this->putResolvedImages($imagesCached, $filters);

        $this->assertImagesNotExist($imagesResolved, $filters);
        $this->assertImagesExist($imagesCached, $filters);

        $output = $this->executeResolveCacheCommand($images, $filters);

        $this->assertImagesExist($images, $filters);
        $this->assertOutputContainsResolvedImages($output, $imagesResolved, $filters);
        $this->assertOutputContainsCachedImages($output, $imagesCached, $filters);
        $this->assertOutputContainsSummary($output, $images, $filters);

        $this->delResolvedImages($images, $filters);
    }

    public function testShouldResolveWithFewPathsAndFewFilters()
    {
        $images = array('images/cats.jpeg', 'images/cats2.jpeg');
        $filters = array('thumbnail_web_path', 'thumbnail_default');

        $output = $this->executeResolveCacheCommand($images, $filters);

        $this->assertImagesExist($images, $filters);
        $this->assertOutputContainsResolvedImages($output, $images, $filters);
        $this->assertOutputContainsSummary($output, $images, $filters);

        $this->delResolvedImages($images, $filters);
    }

    public function testShouldResolveWithFewPathsAndWithoutFilters()
    {
        $images = array('images/cats.jpeg', 'images/cats2.jpeg');
        $filters = array('thumbnail_web_path', 'thumbnail_default');

        $output = $this->executeResolveCacheCommand($images);

        $this->assertImagesExist($images, $filters);
        $this->assertOutputContainsResolvedImages($output, $images, $filters);
        $this->assertOutputContainsSummary($output, $images, $filters);

        $this->delResolvedImages($images, $filters);
    }

    public function testCachedAndForceResolve()
    {
        $images = array('images/cats.jpeg', 'images/cats2.jpeg');
        $filters = array('thumbnail_web_path', 'thumbnail_default');

        $this->assertImagesNotExist($images, $filters);
        $this->putResolvedImages($images, $filters);
        $this->assertImagesExist($images, $filters);

        $output = $this->executeResolveCacheCommand($images, $filters);

        $this->assertImagesExist($images, $filters);
        $this->assertOutputContainsCachedImages($output, $images, $filters);

        $output = $this->executeResolveCacheCommand($images, $filters, array('--force' => true));

        $this->assertImagesExist($images, $filters);
        $this->assertOutputContainsResolvedImages($output, $images, $filters);
        $this->assertOutputContainsSummary($output, $images, $filters);

        $this->delResolvedImages($images, $filters);
    }

    public function testFailedResolve()
    {
        $images = array('images/cats.jpeg', 'images/cats2.jpeg');
        $filters = array('does_not_exist');

        $this->assertImagesNotExist($images, $filters);

        $return = null;
        $output = $this->executeResolveCacheCommand($images, $filters, array(), $return);

        $this->assertSame(255, $return);
        $this->assertImagesNotExist($images, $filters);
        $this->assertOutputContainsFailedImages($output, $images, $filters);
        $this->assertOutputContainsSummary($output, $images, $filters, 2);

        $this->delResolvedImages($images, $filters);
    }

    public function testScriptReadableOption()
    {
        $images = array('images/cats.jpeg', 'images/cats2.jpeg');
        $filters = array('does_not_exist');

        $this->assertImagesNotExist($images, $filters);

        $output = $this->executeResolveCacheCommand($images, $filters, array('--as-script' => false));

        $this->assertContains('liip/imagine-bundle', $output);
        $this->assertOutputContainsFailedImages($output, $images, $filters);
        $this->assertOutputContainsSummary($output, $images, $filters, 2);

        $output = $this->executeResolveCacheCommand($images, $filters, array('--as-script' => true));

        $this->assertImagesNotExist($images, $filters);
        $this->assertNotContains('liip/imagine-bundle', $output);
        $this->assertOutputContainsFailedImages($output, $images, $filters);
        $this->assertOutputNotContainsSummary($output, $images, $filters, 2);

        $this->delResolvedImages($images, $filters);
    }

    /**
     * @param string[] $paths
     * @param string[] $filters
     * @param string[] $additionalOptions
     * @param int      $return
     *
     * @return string
     */
    private function executeResolveCacheCommand(array $paths, array $filters = array(), array $additionalOptions = array(), &$return = null)
    {
        $options = array_merge(array('path' => $paths), $additionalOptions);

        if (0 < count($filters)) {
            $options['--filter'] = $filters;
        }

        return $this->executeConsole($this->getResolveCacheCommand(), $options, $return);
    }

    /**
     * @return ResolveCacheCommand
     */
    private function getResolveCacheCommand(): ResolveCacheCommand
    {
        return $this->createClient()->getContainer()->get('liip_imagine.command.cache_resolve');
    }

    /**
     * @param string   $output
     * @param string[] $images
     * @param string[] $filters
     * @param int      $failures
     */
    protected function assertOutputContainsSummary($output, array $images, array $filters, $failures = 0)
    {
        $this->assertContains(sprintf('Completed %d resolution', (count($images) * count($filters)) - $failures), $output);
        $this->assertContains(sprintf('%d image', count($images)), $output);
        $this->assertContains(sprintf('%d filter', count($filters)), $output);
        if (0 !== $failures) {
            $this->assertContains(sprintf('%d failure', $failures), $output);
        }
    }

    /**
     * @param string   $output
     * @param string[] $images
     * @param string[] $filters
     * @param int      $failures
     */
    protected function assertOutputNotContainsSummary($output, array $images, array $filters, $failures = 0)
    {
        $this->assertNotContains(sprintf('Completed %d resolution', (count($images) * count($filters)) - $failures), $output);
        $this->assertNotContains(sprintf('%d image', count($images)), $output);
        $this->assertNotContains(sprintf('%d filter', count($filters)), $output);
        if (0 !== $failures) {
            $this->assertNotContains(sprintf('%d failure', $failures), $output);
        }
    }
}
