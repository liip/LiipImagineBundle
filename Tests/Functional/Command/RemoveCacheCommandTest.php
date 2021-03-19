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

/**
 * @covers \Liip\ImagineBundle\Command\RemoveCacheCommand
 * @covers \Liip\ImagineBundle\Command\CacheCommandTrait
 */
class RemoveCacheCommandTest extends AbstractCommandTestCase
{
    public function testShouldRemoveWithEmptyCache(): void
    {
        $images = ['images/cats.jpeg', 'images/cats2.jpeg'];
        $filters = ['thumbnail_web_path'];

        $this->assertImagesNotExist($images, $filters);

        $return = null;
        $output = $this->executeRemoveCacheCommand($images, $filters, [], $return);

        $this->assertSame(0, $return);
        $this->assertImagesNotExist($images, $filters);
        $this->assertOutputContainsSkippedImages($output, $images, $filters);
        $this->assertOutputContainsSummary($output, $images, $filters);

        $this->delResolvedImages($images, $filters);
    }

    public function testShouldRemoveWithCacheExists(): void
    {
        $images = ['images/cats.jpeg'];
        $filters = ['thumbnail_web_path'];

        $this->putResolvedImages($images, $filters);

        $output = $this->executeRemoveCacheCommand($images, $filters);

        $this->assertImagesNotExist($images, $filters);
        $this->assertOutputContainsRemovedImages($output, $images, $filters);
        $this->assertOutputContainsSummary($output, $images, $filters);

        $this->delResolvedImages($images, $filters);
    }

    public function testShouldRemoveWithFewPathsAndSingleFilter(): void
    {
        $images = ['images/cats.jpeg', 'images/cats2.jpeg'];
        $filters = ['thumbnail_web_path'];

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

    public function testShouldRemoveWithFewPathsSingleFilterAndPartiallyFullCache(): void
    {
        $imagesNotCached = ['images/cats.jpeg'];
        $imagesCached = ['images/cats2.jpeg'];
        $images = array_merge($imagesNotCached, $imagesCached);
        $filters = ['thumbnail_web_path'];

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

    public function testShouldRemoveWithFewPathsAndFewFilters(): void
    {
        $images = ['images/cats.jpeg', 'images/cats2.jpeg'];
        $filters = ['thumbnail_web_path', 'thumbnail_default'];

        $this->putResolvedImages($images, $filters);

        $output = $this->executeRemoveCacheCommand($images, $filters);

        $this->assertImagesNotExist($images, $filters);
        $this->assertOutputContainsRemovedImages($output, $images, $filters);
        $this->assertOutputContainsSummary($output, $images, $filters);

        $this->delResolvedImages($images, $filters);
    }

    public function testShouldRemoveWithFewPathsAndWithoutFilters(): void
    {
        $images = ['images/cats.jpeg', 'images/cats2.jpeg'];
        $filters = ['thumbnail_web_path', 'thumbnail_default'];

        $this->putResolvedImages($images, $filters);

        $output = $this->executeRemoveCacheCommand($images);

        $this->assertImagesNotExist($images, $filters);
        $this->assertOutputContainsRemovedImages($output, $images, $filters);
        $this->assertOutputContainsSummary($output, $images, $filters);

        $this->delResolvedImages($images, $filters);
    }

    public function testSkippedRemove(): void
    {
        $images = ['images/cats.jpeg', 'images/cats2.jpeg'];
        $filters = ['thumbnail_web_path', 'thumbnail_default'];

        $this->assertImagesNotExist($images, $filters);

        $output = $this->executeRemoveCacheCommand($images, $filters);

        $this->assertImagesNotExist($images, $filters);
        $this->assertOutputContainsSkippedImages($output, $images, $filters);
        $this->assertOutputContainsSummary($output, $images, $filters);

        $this->delResolvedImages($images, $filters);
    }

    public function testShouldRemoveAllImagesAndFilters(): void
    {
        $images = [];
        $filters = [];

        $output = $this->executeRemoveCacheCommand($images, $filters);

        $filtersDefined = ['thumbnail_web_path', 'thumbnail_default'];

        $this->assertOutputContainsSummary($output, $images, $filtersDefined);

        $this->delResolvedImages($images, $filters);
    }

    protected function assertOutputContainsSkippedImages($output, array $images, array $filters): void
    {
        foreach ($images as $i) {
            foreach ($filters as $f) {
                $this->assertStringContainsString(sprintf('%s[%s] (skipped)', $i, $f), $output);
            }
        }
    }

    protected function assertOutputContainsRemovedImages($output, array $images, array $filters): void
    {
        foreach ($images as $i) {
            foreach ($filters as $f) {
                $this->assertStringContainsString(sprintf('%s[%s] (removed)', $i, $f), $output);
            }
        }
    }

    /**
     * @param string[] $images
     * @param string[] $filters
     */
    protected function assertOutputContainsSummary(string $output, array $images, array $filters, int $failures = 0): void
    {
        $imagesSize = \count($images);
        $filtersSize = \count($filters);

        $totalSize = 0 === $imagesSize ? $filtersSize : ($imagesSize * $filtersSize) - $failures;
        $this->assertStringContainsString(sprintf('Completed %d removal', $totalSize), $output);

        if (0 !== $imagesSize) {
            $this->assertStringContainsString(sprintf('%d image', $imagesSize), $output);
        }

        $this->assertStringContainsString(sprintf('%d filter', $filtersSize), $output);

        if (0 !== $failures) {
            $this->assertStringContainsString(sprintf('%d failure', $failures), $output);
        }
    }

    /**
     * @param string[] $images
     * @param string[] $filters
     */
    protected function assertOutputNotContainsSummary(string $output, array $images, array $filters, int $failures = 0): void
    {
        $this->assertStringNotContainsString(sprintf('Completed %d removal', (\count($images) * \count($filters)) - $failures), $output);
        $this->assertStringNotContainsString(sprintf('%d image', \count($images)), $output);
        $this->assertStringNotContainsString(sprintf('%d filter', \count($filters)), $output);
        if (0 !== $failures) {
            $this->assertStringNotContainsString(sprintf('%d failure', $failures), $output);
        }
    }

    /**
     * @param string[] $paths
     * @param string[] $filters
     * @param string[] $additionalOptions
     */
    private function executeRemoveCacheCommand(array $paths, array $filters = [], array $additionalOptions = [], int &$return = null): string
    {
        $options = array_merge(['paths' => $paths], $additionalOptions);

        if (0 < \count($filters)) {
            $options['--filter'] = $filters;
        }

        return $this->executeConsole('liip:imagine:cache:remove', $options, $return);
    }
}
