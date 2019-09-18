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
 * @covers \Liip\ImagineBundle\Command\ResolveCacheCommand
 * @covers \Liip\ImagineBundle\Command\CacheCommandTrait
 */
class ResolveCacheCommandTest extends AbstractCommandTestCase
{
    public function testShouldResolveWithEmptyCache(): void
    {
        $images = ['images/cats.jpeg', 'images/cats2.jpeg'];
        $filters = ['thumbnail_web_path'];

        $this->assertImagesNotExist($images, $filters);

        $return = null;
        $output = $this->executeResolveCacheCommand($images, $filters, [], $return);

        $this->assertSame(0, $return);
        $this->assertImagesExist($images, $filters);
        $this->assertImagesNotExist($images, ['thumbnail_default']);
        $this->assertOutputContainsResolvedImages($output, $images, $filters);
        $this->assertOutputContainsSummary($output, $images, $filters);

        $this->delResolvedImages($images, $filters);
    }

    public function testShouldResolveWithCacheExists(): void
    {
        $images = ['images/cats.jpeg'];
        $filters = ['thumbnail_web_path'];

        $this->putResolvedImages($images, $filters);

        $output = $this->executeResolveCacheCommand($images, $filters);

        $this->assertImagesExist($images, $filters);
        $this->assertImagesNotExist($images, ['thumbnail_default']);
        $this->assertOutputContainsCachedImages($output, $images, $filters);
        $this->assertOutputContainsSummary($output, $images, $filters);

        $this->delResolvedImages($images, $filters);
    }

    public function testShouldResolveWithFewPathsAndSingleFilter(): void
    {
        $images = ['images/cats.jpeg', 'images/cats2.jpeg'];
        $filters = ['thumbnail_web_path'];

        $output = $this->executeResolveCacheCommand($images, $filters);

        $this->assertImagesExist($images, $filters);
        $this->assertOutputContainsResolvedImages($output, $images, $filters);

        $output = $this->executeResolveCacheCommand($images, $filters);

        $this->assertImagesExist($images, $filters);
        $this->assertOutputContainsCachedImages($output, $images, $filters);
        $this->assertOutputContainsSummary($output, $images, $filters);

        $this->delResolvedImages($images, $filters);
    }

    public function testShouldResolveWithFewPathsSingleFilterAndPartiallyFullCache(): void
    {
        $imagesResolved = ['images/cats.jpeg'];
        $imagesCached = ['images/cats2.jpeg'];
        $images = array_merge($imagesResolved, $imagesCached);
        $filters = ['thumbnail_web_path'];

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

    public function testShouldResolveWithFewPathsAndFewFilters(): void
    {
        $images = ['images/cats.jpeg', 'images/cats2.jpeg'];
        $filters = ['thumbnail_web_path', 'thumbnail_default'];

        $output = $this->executeResolveCacheCommand($images, $filters);

        $this->assertImagesExist($images, $filters);
        $this->assertOutputContainsResolvedImages($output, $images, $filters);
        $this->assertOutputContainsSummary($output, $images, $filters);

        $this->delResolvedImages($images, $filters);
    }

    public function testShouldResolveWithFewPathsAndWithoutFilters(): void
    {
        $images = ['images/cats.jpeg', 'images/cats2.jpeg'];
        $filters = ['thumbnail_web_path', 'thumbnail_default'];

        $output = $this->executeResolveCacheCommand($images);

        $this->assertImagesExist($images, $filters);
        $this->assertOutputContainsResolvedImages($output, $images, $filters);
        $this->assertOutputContainsSummary($output, $images, $filters);

        $this->delResolvedImages($images, $filters);
    }

    public function testCachedAndForceResolve(): void
    {
        $images = ['images/cats.jpeg', 'images/cats2.jpeg'];
        $filters = ['thumbnail_web_path', 'thumbnail_default'];

        $this->assertImagesNotExist($images, $filters);
        $this->putResolvedImages($images, $filters);
        $this->assertImagesExist($images, $filters);

        $output = $this->executeResolveCacheCommand($images, $filters);

        $this->assertImagesExist($images, $filters);
        $this->assertOutputContainsCachedImages($output, $images, $filters);

        $output = $this->executeResolveCacheCommand($images, $filters, ['--force' => true]);

        $this->assertImagesExist($images, $filters);
        $this->assertOutputContainsResolvedImages($output, $images, $filters);
        $this->assertOutputContainsSummary($output, $images, $filters);

        $this->delResolvedImages($images, $filters);
    }

    public function testFailedResolve(): void
    {
        $images = ['images/cats.jpeg', 'images/cats2.jpeg'];
        $filters = ['does_not_exist'];

        $this->assertImagesNotExist($images, $filters);

        $return = null;
        $output = $this->executeResolveCacheCommand($images, $filters, [], $return);

        $this->assertSame(255, $return);
        $this->assertImagesNotExist($images, $filters);
        $this->assertOutputContainsFailedImages($output, $images, $filters);
        $this->assertOutputContainsSummary($output, $images, $filters, 2);

        $this->delResolvedImages($images, $filters);
    }

    public function testScriptReadableOption(): void
    {
        $images = ['images/cats.jpeg', 'images/cats2.jpeg'];
        $filters = ['does_not_exist'];

        $this->assertImagesNotExist($images, $filters);

        $output = $this->executeResolveCacheCommand($images, $filters, ['--as-script' => false]);

        $this->assertContains('liip/imagine-bundle', $output);
        $this->assertOutputContainsFailedImages($output, $images, $filters);
        $this->assertOutputContainsSummary($output, $images, $filters, 2);

        $output = $this->executeResolveCacheCommand($images, $filters, ['--as-script' => true]);

        $this->assertImagesNotExist($images, $filters);
        $this->assertNotContains('liip/imagine-bundle', $output);
        $this->assertOutputContainsFailedImages($output, $images, $filters);
        $this->assertOutputNotContainsSummary($output, $images, $filters, 2);

        $this->delResolvedImages($images, $filters);
    }

    /**
     * @param string[] $images
     * @param string[] $filters
     */
    protected function assertOutputContainsSummary(string $output, array $images, array $filters, int $failures = 0): void
    {
        $this->assertContains(sprintf('Completed %d resolution', (count($images) * count($filters)) - $failures), $output);
        $this->assertContains(sprintf('%d image', count($images)), $output);
        $this->assertContains(sprintf('%d filter', count($filters)), $output);
        if (0 !== $failures) {
            $this->assertContains(sprintf('%d failure', $failures), $output);
        }
    }

    /**
     * @param string[] $images
     * @param string[] $filters
     */
    protected function assertOutputNotContainsSummary(string $output, array $images, array $filters, int $failures = 0): void
    {
        $this->assertNotContains(sprintf('Completed %d resolution', (count($images) * count($filters)) - $failures), $output);
        $this->assertNotContains(sprintf('%d image', count($images)), $output);
        $this->assertNotContains(sprintf('%d filter', count($filters)), $output);
        if (0 !== $failures) {
            $this->assertNotContains(sprintf('%d failure', $failures), $output);
        }
    }

    /**
     * @param string[] $paths
     * @param string[] $filters
     * @param string[] $additionalOptions
     * @param int      $return
     *
     * @return string
     */
    private function executeResolveCacheCommand(array $paths, array $filters = [], array $additionalOptions = [], &$return = null): string
    {
        $options = array_merge(['path' => $paths], $additionalOptions);

        if (0 < count($filters)) {
            $options['--filter'] = $filters;
        }

        return $this->executeConsole('liip:imagine:cache:resolve', $options, $return);
    }
}
