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
use Liip\ImagineBundle\Tests\Functional\Fixtures\CacheCommandFixtures;

/**
 * @covers \Liip\ImagineBundle\Command\ResolveCacheCommand
 * @covers \Liip\ImagineBundle\Command\AbstractCacheCommand
 */
class ResolveCacheCommandTest extends AbstractCacheCommandTestCase
{
    /**
     * @return array
     */
    public static function provideResolvedWhenPassedPathsAndFiltersData()
    {
        return CacheCommandFixtures::getAvailableFilterAndImageCombinations();
    }

    /**
     * @param array $images
     * @param array $filters
     *
     * @dataProvider provideResolvedWhenPassedPathsAndFiltersData
     */
    public function testResolvesWhenPassedPathsAndFilters(array $images, array $filters)
    {
        $this->putResolvedImages($images, $filters);

        $output = $this->executeResolveCacheCommand($images, $filters);

        $this->assertImagesExist($images, $filters);
        $this->assertOutputContainsSkippedImages($output, $images, $filters);
        $this->assertOutputContainsSummary($output, $images, $filters);

        $this->delResolvedImages($images, $filters);
    }

    /**
     * @return array
     */
    public static function provideResolvesWhenPassedOnlyImagesData()
    {
        return CacheCommandFixtures::getAvailableFilterAndImageCombinations();
    }

    /**
     * @param array $images
     * @param array $filters
     *
     * @dataProvider provideResolvedWhenPassedPathsAndFiltersData
     */
    public function testResolvesWhenPassedOnlyImages(array $images, array $filters)
    {
        $this->putResolvedImages($images, $filters);

        $output = $this->executeResolveCacheCommand($images, $filters);

        $this->assertImagesExist($images, $filters);
        $this->assertOutputContainsSkippedImages($output, $images, $filters);
        $this->assertOutputContainsSummary($output, $images, $filters);

        $this->delResolvedImages($images, $filters);
    }

    /**
     * @return array
     */
    public static function provideResolvesWhenPassedPathsAndFiltersWithPartialCachesData()
    {
        $data = array_map(function (array $entry) {
            $entry[] = CacheCommandFixtures::getImagesNotInArray($entry[0]);
            $entry[] = CacheCommandFixtures::getFiltersNotInArray($entry[1]);

            return $entry;
        }, CacheCommandFixtures::getAvailableFilterAndImageCombinations());

        return array_filter($data, function (array $entry) {
            return null !== $entry[2] && null !== $entry[3];
        });
    }

    /**
     * @param array $images
     * @param array $filters
     * @param array $cachedImages
     * @param array $cachedFilters
     *
     * @dataProvider provideResolvesWhenPassedPathsAndFiltersWithPartialCachesData
     */
    public function testResolvesWhenPassedPathsAndFiltersWithPartialCaches(array $images, array $filters, array $cachedImages, array $cachedFilters)
    {
        $allImages = array_merge($images, $cachedImages);
        $allFilters = array_merge($filters, $cachedFilters);

        $this->putResolvedImages($cachedImages, $cachedFilters);

        $this->assertImagesNotExist($images, $filters);
        $this->assertImagesExist($cachedImages, $cachedFilters);

        $output = $this->executeResolveCacheCommand($allImages, $allFilters);

        $this->assertImagesExist($allImages, $allFilters);
        $this->assertOutputContainsResolvedImages($output, $images, $filters);
        $this->assertOutputContainsSkippedImages($output, $cachedImages, $cachedFilters);
        $this->assertOutputContainsSummary($output, $allImages, $allFilters);

        $this->delResolvedImages($allImages, $allFilters);
    }

    /**
     * @return array
     */
    public static function provideResolvesForcedAllWhenPassedPathsAndFiltersWithPartialCachesData()
    {
        return static::provideResolvesWhenPassedPathsAndFiltersWithPartialCachesData();
    }

    /**
     * @param array $images
     * @param array $filters
     * @param array $cachedImages
     * @param array $cachedFilters
     *
     * @dataProvider provideResolvesForcedAllWhenPassedPathsAndFiltersWithPartialCachesData
     */
    public function testForcedResolveWhenPassedPathsAndFiltersWithPartialCaches(array $images, array $filters, array $cachedImages, array $cachedFilters)
    {
        $allImages = array_merge($images, $cachedImages);
        $allFilters = array_merge($filters, $cachedFilters);

        $this->putResolvedImages($cachedImages, $cachedFilters);

        $this->assertImagesNotExist($images, $filters);
        $this->assertImagesExist($cachedImages, $cachedFilters);

        $output = $this->executeResolveCacheCommand($allImages, $allFilters, array('--force' => true));

        $this->assertImagesExist($allImages, $allFilters);
        $this->assertOutputContainsResolvedImages($output, $allImages, $allFilters);
        $this->assertOutputContainsSummary($output, $allImages, $allFilters);

        $this->delResolvedImages($allImages, $allFilters);
    }

    /**
     * @return array
     */
    public static function provideResolveShowsFailureAndContinuesWhenPassedInvalidFiltersData()
    {
        return array_map(function (array $entry) {
            $entry[] = CacheCommandFixtures::getInvalidFilters();

            return $entry;
        }, CacheCommandFixtures::getAvailableFilterAndImageCombinations());
    }

    /**
     * @param array $images
     * @param array $filters
     * @param array $invalidFilters
     *
     * @dataProvider provideResolveShowsFailureAndContinuesWhenPassedInvalidFiltersData
     */
    public function testResolveShowsFailureAndContinuesWhenPassedInvalidFilters(array $images, array $filters, array $invalidFilters)
    {
        $allFilters = array_merge($filters, $invalidFilters);

        $this->assertImagesNotExist($images, $allFilters);

        $return = null;
        $output = $this->executeResolveCacheCommand($images, $allFilters, array(), $return);

        $this->assertSame(255, $return);
        $this->assertImagesExist($images, $filters);
        $this->assertImagesNotExist($images, $invalidFilters);
        $this->assertOutputContainsResolvedImages($output, $images, $filters);
        $this->assertOutputContainsFailedImages($output, $images, $invalidFilters);
        $this->assertOutputContainsSummary($output, $images, $allFilters, count($images) * count($invalidFilters));

        $this->delResolvedImages($images, $allFilters);
    }

    /**
     * @return array
     */
    public static function provideResolveShowsFailureAndContinuesWhenPassedInvalidPathsData()
    {
        return array_map(function (array $entry) {
            $entry[] = CacheCommandFixtures::getInvalidImages();

            return $entry;
        }, CacheCommandFixtures::getAvailableFilterAndImageCombinations());
    }

    /**
     * @param array $images
     * @param array $filters
     * @param array $invalidImages
     *
     * @dataProvider provideResolveShowsFailureAndContinuesWhenPassedInvalidPathsData
     */
    public function testResolveShowsFailureAndContinuesWhenPassedInvalidPaths(array $images, array $filters, array $invalidImages)
    {
        $allImages = array_merge($images, $invalidImages);

        $this->assertImagesNotExist($allImages, $filters);

        $return = null;
        $output = $this->executeResolveCacheCommand($allImages, $filters, array(), $return);

        $this->assertSame(255, $return);
        $this->assertImagesExist($images, $filters);
        $this->assertImagesNotExist($invalidImages, $filters);
        $this->assertOutputContainsResolvedImages($output, $images, $filters);
        $this->assertOutputContainsFailedImages($output, $invalidImages, $filters);
        $this->assertOutputContainsSummary($output, $allImages, $filters, count($filters) * count($invalidImages));

        $this->delResolvedImages($allImages, $filters);
    }

    /**
     * @return array
     */
    public static function provideResolveOutputsMachineParseableTextWhenPassedMachineReadableOptionData()
    {
        return CacheCommandFixtures::getAvailableFilterAndImageCombinations();
    }

    /**
     * @param array $images
     * @param array $filters
     *
     * @dataProvider provideResolveOutputsMachineParseableTextWhenPassedMachineReadableOptionData
     */
    public function testResolveOutputsMachineParseableTextWhenPassedMachineReadableOption(array $images, array $filters)
    {
        $this->assertImagesNotExist($images, $filters);

        $output = $this->executeResolveCacheCommand($images, $filters, array('--machine-readable' => true));

        $this->assertImagesExist($images, $filters);
        $this->assertNotContains('[liip/imagine-bundle]', $output);
        $this->assertNotContains('=====================', $output);
        $this->assertOutputContainsResolvedImages($output, $images, $filters);
        $this->assertOutputNotContainsSummary($output, $images, $filters);

        $this->delResolvedImages($images, $filters);
    }

    /**
     * @return array
     */
    public static function provideResolveEmitsDeprecationMessageWhenUsingLegacyFiltersOptionData()
    {
        return CacheCommandFixtures::getAvailableFilterAndImageCombinations();
    }

    /**
     * @dataProvider provideResolveEmitsDeprecationMessageWhenUsingLegacyFiltersOptionData
     *
     * @group legacy
     * @expectedDeprecation The --filters option was deprecated in 1.9.0 and removed in 2.0.0. Use the --filter option instead.
     */
    public function testResolveEmitsDeprecationMessageWhenUsingLegacyFiltersOption(array $images, array $filters)
    {
        $this->assertImagesNotExist($images, $filters);

        $output = $this->executeResolveCacheCommand($images, array(), array('paths' => $images, '--filters' => $filters));

        $this->assertImagesExist($images, $filters);
        $this->assertOutputContainsResolvedImages($output, $images, $filters);

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
    protected function executeResolveCacheCommand(array $paths, array $filters = array(), array $additionalOptions = array(), &$return = null)
    {
        $options = array_merge(array('paths' => $paths), $additionalOptions);

        if (0 < count($filters)) {
            $options['--filter'] = $filters;
        }

        return $this->executeConsole(new ResolveCacheCommand(), $options, $return);
    }
}
