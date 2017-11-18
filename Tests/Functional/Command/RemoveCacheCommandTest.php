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
use Liip\ImagineBundle\Tests\Functional\Fixtures\CacheCommandFixtures;

/**
 * @covers \Liip\ImagineBundle\Command\RemoveCacheCommand
 * @covers \Liip\ImagineBundle\Command\AbstractCacheCommand
 */
class RemoveCacheCommandTest extends AbstractCacheCommandTestCase
{
    /**
     * @return array
     */
    public static function provideRemovesCachesWithoutPathsOrFiltersData()
    {
        return array_map(function (array $entry) {
            $entry[] = CacheCommandFixtures::getValidFilters();

            return $entry;
        }, CacheCommandFixtures::getAvailableFilterAndImageCombinations());
    }

    /**
     * @dataProvider provideRemovesCachesWithoutPathsOrFiltersData
     *
     * @param array $images
     * @param array $filters
     * @param array $allFilters
     */
    public function testRemovesCachesWithoutPathsOrFilters(array $images, array $filters, array $allFilters)
    {
        $this->putResolvedImages($images, $filters);
        $this->assertImagesExist($images, $filters);

        $output = $this->executeRemoveCacheCommand(array(), array());

        $this->assertImagesNotExist($images, $allFilters);
        $this->assertOutputContainsRemovedGlob($output, $allFilters);
        $this->assertOutputContainsSummaryGlob($output, $allFilters);

        $this->delResolvedImages($images, $allFilters);
    }

    /**
     * @return array
     */
    public static function provideRemovesWhenPassedPathsAndFiltersData()
    {
        return CacheCommandFixtures::getAvailableFilterAndImageCombinations();
    }

    /**
     * @dataProvider provideRemovesWhenPassedPathsAndFiltersData
     *
     * @param array $images
     * @param array $filters
     */
    public function testRemovesWhenPassedPathsAndFilters(array $images, array $filters)
    {
        $this->putResolvedImages($images, $filters);
        $this->assertImagesExist($images, $filters);

        $output = $this->executeRemoveCacheCommand($images, $filters);

        $this->assertImagesNotExist($images, $filters);
        $this->assertOutputContainsRemovedImages($output, $images, $filters);
        $this->assertOutputContainsSummary($output, $images, $filters);

        $this->delResolvedImages($images, $filters);
    }

    /**
     * @return array
     */
    public static function provideRemovesWhenPassedOnlyImagesData()
    {
        return static::provideRemovesCachesWithoutPathsOrFiltersData();
    }

    /**
     * @dataProvider provideRemovesWhenPassedOnlyImagesData
     *
     * @param array $images
     * @param array $filters
     * @param array $allFilters
     */
    public function testRemovesWhenPassedOnlyImages(array $images, array $filters, array $allFilters)
    {
        $this->putResolvedImages($images, $filters);
        $this->assertImagesExist($images, $filters);

        $output = $this->executeRemoveCacheCommand($images, array());

        $this->assertImagesNotExist($images, $filters);
        $this->assertOutputContainsRemovedImages($output, $images, $filters);
        $this->assertOutputContainsSummary($output, $images, $allFilters);

        if (count($allFilters) !== count($filters)) {
            $this->assertOutputContainsSkippedImagesShort($output, $images, array_diff($allFilters, $filters));
        }

        $this->delResolvedImages($images, $allFilters);
    }

    /**
     * @return array
     */
    public static function provideRemovesWhenPassedOnlyFiltersData()
    {
        return array_map(function (array $entry) {
            $entry[] = CacheCommandFixtures::getValidImages();

            return $entry;
        }, CacheCommandFixtures::getAvailableFilterAndImageCombinations());
    }

    /**
     * @dataProvider provideRemovesWhenPassedOnlyFiltersData
     *
     * @param array $images
     * @param array $filters
     * @param array $allImages
     */
    public function testRemovesWhenPassedOnlyFilters(array $images, array $filters, array $allImages)
    {
        $this->putResolvedImages($allImages, $filters);
        $this->assertImagesExist($allImages, $filters);

        $output = $this->executeRemoveCacheCommand(array(), $filters);

        $this->assertImagesNotExist($allImages, $filters);
        $this->assertOutputContainsRemovedGlob($output, $filters);
        $this->assertOutputContainsSummaryGlob($output, $filters);

        $this->delResolvedImages($allImages, $filters);
    }

    /**
     * @return array
     */
    public static function provideRemoveSkipsWhenCacheDoesNotExistData()
    {
        $data = array_map(function (array $entry) {
            $entry[] = CacheCommandFixtures::getImagesNotInArray($entry[0]);

            return $entry;
        }, CacheCommandFixtures::getAvailableFilterAndImageCombinations());

        return array_filter($data, function (array $entry) {
            return null !== $entry[2];
        });
    }

    /**
     * @dataProvider provideRemoveSkipsWhenCacheDoesNotExistData
     *
     * @param array $images
     * @param array $filters
     * @param array $existingImages
     */
    public function testRemoveSkipsWhenCacheDoesNotExist(array $images, array $filters, array $existingImages)
    {
        $this->putResolvedImages($existingImages, $filters);
        $this->assertImagesExist($existingImages, $filters);
        $this->assertImagesNotExist($images, $filters);

        $output = $this->executeRemoveCacheCommand($images, $filters);

        $this->assertImagesExist($existingImages, $filters);
        $this->assertImagesNotExist($images, $filters);
        $this->assertOutputContainsSkippedImagesShort($output, $images, $filters);
        $this->assertOutputContainsSummary($output, $images, $filters);

        $this->delResolvedImages(array_merge($images, $existingImages), $filters);
    }

    /**
     * @return array
     */
    public static function provideRemoveShowsFailureAndContinuesWhenPassedInvalidFiltersData()
    {
        return array_map(function (array $entry) {
            $entry[] = CacheCommandFixtures::getInvalidFilters();

            return $entry;
        }, CacheCommandFixtures::getAvailableFilterAndImageCombinations());
    }

    /**
     * @dataProvider provideRemoveShowsFailureAndContinuesWhenPassedInvalidFiltersData
     *
     * @param array $images
     * @param array $filters
     * @param array $invalidFilters
     */
    public function testRemoveShowsFailureAndContinuesWhenPassedInvalidFilters(array $images, array $filters, array $invalidFilters)
    {
        $allFilters = array_merge($filters, $invalidFilters);

        $this->putResolvedImages($images, $allFilters);
        $this->assertImagesExist($images, $allFilters);

        $return = null;
        $output = $this->executeRemoveCacheCommand($images, $allFilters, array(), $return);

        $this->assertSame(255, $return);
        $this->assertImagesNotExist($images, $filters);
        $this->assertImagesExist($images, $invalidFilters);
        $this->assertOutputContainsRemovedImages($output, $images, $filters);
        $this->assertOutputContainsFailedImages($output, $images, $invalidFilters);
        $this->assertOutputContainsSummary($output, $images, $allFilters, count($images) * count($invalidFilters));

        $this->delResolvedImages($images, $allFilters);
    }

    /**
     * @return array
     */
    public static function provideRemoveShowsFailureAndContinuesWhenPassedInvalidPathsData()
    {
        return array_map(function (array $entry) {
            $entry[] = CacheCommandFixtures::getInvalidImages();

            return $entry;
        }, CacheCommandFixtures::getAvailableFilterAndImageCombinations());
    }

    /**
     * @dataProvider provideRemoveShowsFailureAndContinuesWhenPassedInvalidPathsData
     *
     * @param array $images
     * @param array $filters
     * @param array $invalidImages
     */
    public function testRemoveShowsFailureAndContinuesWhenPassedInvalidPaths(array $images, array $filters, array $invalidImages)
    {
        $allImages = array_merge($images, $invalidImages);

        $this->putResolvedImages($images, $filters);
        $this->assertImagesExist($images, $filters);
        $this->assertImagesNotExist($invalidImages, $filters);

        $output = $this->executeRemoveCacheCommand($allImages, $filters);

        $this->assertImagesNotExist($allImages, $filters);
        $this->assertOutputContainsRemovedImages($output, $images, $filters);
        $this->assertOutputContainsSkippedImagesShort($output, $invalidImages, $filters);
        $this->assertOutputContainsSummary($output, $allImages, $filters);

        $this->delResolvedImages($allImages, $filters);
    }

    /**
     * @return array
     */
    public static function provideRemoveOutputsMachineParseableTextWhenPassedMachineReadableOptionData()
    {
        return CacheCommandFixtures::getAvailableFilterAndImageCombinations();
    }

    /**
     * @dataProvider provideRemoveOutputsMachineParseableTextWhenPassedMachineReadableOptionData
     *
     * @param array $images
     * @param array $filters
     */
    public function testRemoveOutputsMachineParseableTextWhenPassedMachineReadableOption(array $images, array $filters)
    {
        $this->putResolvedImages($images, $filters);
        $this->assertImagesExist($images, $filters);

        $output = $this->executeRemoveCacheCommand($images, $filters, array('--machine-readable' => true));

        $this->assertImagesNotExist($images, $filters);
        $this->assertNotContains('[liip/imagine-bundle]', $output);
        $this->assertNotContains('=====================', $output);
        $this->assertOutputContainsRemovedImages($output, $images, $filters);
        $this->assertOutputNotContainsSummary($output, $images, $filters);

        $this->delResolvedImages($images, $filters);
    }

    /**
     * @return array
     */
    public static function provideRemoveEmitsDeprecationMessageWhenUsingLegacyFiltersOptionData()
    {
        return CacheCommandFixtures::getAvailableFilterAndImageCombinations();
    }

    /**
     * @dataProvider provideRemoveEmitsDeprecationMessageWhenUsingLegacyFiltersOptionData
     *
     * @group legacy
     * @expectedDeprecation The --filters option was deprecated in 1.9.0 and removed in 2.0.0. Use the --filter option instead.
     */
    public function testRemoveEmitsDeprecationMessageWhenUsingLegacyFiltersOption(array $images, array $filters)
    {
        $this->putResolvedImages($images, $filters);
        $this->assertImagesExist($images, $filters);

        $output = $this->executeRemoveCacheCommand($images, array(), array('paths' => $images, '--filters' => $filters));

        $this->assertImagesNotExist($images, $filters);
        $this->assertOutputContainsRemovedImages($output, $images, $filters);

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
        $options = array_merge(array('paths' => $paths), $additionalOptions);

        if (0 < count($filters)) {
            $options['--filter'] = $filters;
        }

        return $this->executeConsole(new RemoveCacheCommand(), $options, $return);
    }
}
