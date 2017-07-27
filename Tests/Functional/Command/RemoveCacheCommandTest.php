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
 * @covers \Liip\ImagineBundle\Command\CacheCommandTrait
 */
class RemoveCacheCommandTest extends AbstractCommandTestCase
{
    public function testShouldRemoveWithEmptyCache()
    {
        $images = array('images/cats.jpeg', 'images/cats2.jpeg');
        $filters = array('thumbnail_web_path');

        $this->assertImagesNotExist($images, $filters);

        $return = null;
        $output = $this->executeRemoveCacheCommand($images, $filters, array(), $return);

        $this->assertSame(0, $return);
        $this->assertImagesNotExist($images, $filters);
        $this->assertOutputContainsSkippedImages($output, $images, $filters);
        $this->assertOutputContainsSummary($output, $images, $filters);

        $this->delResolvedImages($images, $filters);
    }

    public function testShouldRemoveWithCacheExists()
    {
        $images = array('images/cats.jpeg');
        $filters = array('thumbnail_web_path');

        $this->putResolvedImages($images, $filters);

        $output = $this->executeRemoveCacheCommand($images, $filters);

        $this->assertImagesNotExist($images, $filters);
        $this->assertOutputContainsRemovedImages($output, $images, $filters);
        $this->assertOutputContainsSummary($output, $images, $filters);

        $this->delResolvedImages($images, $filters);
    }

    public function testShouldRemoveWithFewPathsAndSingleFilter()
    {
        $images = array('images/cats.jpeg', 'images/cats2.jpeg');
        $filters = array('thumbnail_web_path');

        $this->putResolvedImages($images, $filters);

        $output = $this->executeRemoveCacheCommand($images, $filters);

        $this->assertImagesNotExist($images, $filters);
        $this->assertOutputContainsRemovedImages($output, $images, $filters);

        $output = $this->executeRemoveCacheCommand($images, $filters);

        $this->assertImagesNotExist($images, $filters);
        $this->assertOutputContainsSkippedImages($output, $images, $filters);
        $this->assertOutputContainsSummary($output, $images, $filters);

        $this->delResolvedImages($images, $filters);
    }

    public function testShouldRemoveWithFewPathsSingleFilterAndPartiallyFullCache()
    {
        $imagesNotCached = array('images/cats.jpeg');
        $imagesCached = array('images/cats2.jpeg');
        $images = array_merge($imagesNotCached, $imagesCached);
        $filters = array('thumbnail_web_path');

        $this->putResolvedImages($imagesCached, $filters);

        $this->assertImagesNotExist($imagesNotCached, $filters);
        $this->assertImagesExist($imagesCached, $filters);

        $output = $this->executeRemoveCacheCommand($images, $filters);

        $this->assertImagesNotExist($images, $filters);
        $this->assertOutputContainsSkippedImages($output, $imagesNotCached, $filters);
        $this->assertOutputContainsRemovedImages($output, $imagesCached, $filters);
        $this->assertOutputContainsSummary($output, $images, $filters);

        $this->delResolvedImages($images, $filters);
    }

    public function testShouldRemoveWithFewPathsAndFewFilters()
    {
        $images = array('images/cats.jpeg', 'images/cats2.jpeg');
        $filters = array('thumbnail_web_path', 'thumbnail_default');

        $this->putResolvedImages($images, $filters);

        $output = $this->executeRemoveCacheCommand($images, $filters);

        $this->assertImagesNotExist($images, $filters);
        $this->assertOutputContainsRemovedImages($output, $images, $filters);
        $this->assertOutputContainsSummary($output, $images, $filters);

        $this->delResolvedImages($images, $filters);
    }

    public function testShouldRemoveWithFewPathsAndWithoutFilters()
    {
        $images = array('images/cats.jpeg', 'images/cats2.jpeg');
        $filters = array('thumbnail_web_path', 'thumbnail_default');

        $this->putResolvedImages($images, $filters);

        $output = $this->executeRemoveCacheCommand($images);

        $this->assertImagesNotExist($images, $filters);
        $this->assertOutputContainsRemovedImages($output, $images, $filters);
        $this->assertOutputContainsSummary($output, $images, $filters);

        $this->delResolvedImages($images, $filters);
    }

    public function testSkippedRemove()
    {
        $images = array('images/cats.jpeg', 'images/cats2.jpeg');
        $filters = array('thumbnail_web_path', 'thumbnail_default');

        $this->assertImagesNotExist($images, $filters);

        $output = $this->executeRemoveCacheCommand($images, $filters);

        $this->assertImagesNotExist($images, $filters);
        $this->assertOutputContainsSkippedImages($output, $images, $filters);
        $this->assertOutputContainsSummary($output, $images, $filters);

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
    private function executeRemoveCacheCommand(array $paths, array $filters = array(), array $additionalOptions = array(), &$return = null)
    {
        $options = array_merge(array('path' => $paths), $additionalOptions);

        if (0 < count($filters)) {
            $options['--filter'] = $filters;
        }

        return $this->executeConsole($this->getRemoveCacheCommand(), $options, $return);
    }

    /**
     * @return RemoveCacheCommand
     */
    private function getRemoveCacheCommand(): RemoveCacheCommand
    {
        return $this->createClient()->getContainer()->get('liip_imagine.command.cache_remove');
    }

    /**
     * @param string $output
     * @param array  $images
     * @param array  $filters
     */
    protected function assertOutputContainsSkippedImages($output, array $images, array $filters)
    {
        foreach ($images as $i) {
            foreach ($filters as $f) {
                $this->assertContains(sprintf('%s[%s] (skipped)', $i, $f), $output);
            }
        }
    }

    /**
     * @param string $output
     * @param array  $images
     * @param array  $filters
     */
    protected function assertOutputContainsRemovedImages($output, array $images, array $filters)
    {
        foreach ($images as $i) {
            foreach ($filters as $f) {
                $this->assertContains(sprintf('%s[%s] (removed)', $i, $f), $output);
            }
        }
    }

    /**
     * @param string   $output
     * @param string[] $images
     * @param string[] $filters
     * @param int      $failures
     */
    protected function assertOutputContainsSummary($output, array $images, array $filters, $failures = 0)
    {
        $this->assertContains(sprintf('Completed %d removal', (count($images) * count($filters)) - $failures), $output);
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
        $this->assertNotContains(sprintf('Completed %d removal', (count($images) * count($filters)) - $failures), $output);
        $this->assertNotContains(sprintf('%d image', count($images)), $output);
        $this->assertNotContains(sprintf('%d filter', count($filters)), $output);
        if (0 !== $failures) {
            $this->assertNotContains(sprintf('%d failure', $failures), $output);
        }
    }
}
