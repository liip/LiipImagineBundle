<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Command;

use Imagine\Exception\RuntimeException;
use Liip\ImagineBundle\Component\Console\Style\ImagineStyle;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
trait CacheCommandTrait
{
    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * @var FilterManager
     */
    private $filterManager;

    /**
     * @var ImagineStyle
     */
    private $io;

    /**
     * @var bool
     */
    private $outputMachineReadable;

    /**
     * @var int
     */
    private $failures = 0;

    private function setupOutputStyle(InputInterface $input, OutputInterface $output): void
    {
        $this->outputMachineReadable = $input->getOption('as-script');
        $this->io = new ImagineStyle($input, $output, $this->outputMachineReadable ? false : !$input->getOption('no-colors'));
    }

    /**
     * @return array[]
     */
    private function resolveInputFiltersAndPaths(InputInterface $input): array
    {
        return [
            $input->getArgument('paths'),
            $this->normalizeFilterList($input->getOption('filter')),
        ];
    }

    /**
     * @param string[] $filters
     *
     * @return string[]
     */
    private function normalizeFilterList(array $filters): array
    {
        if (0 < \count($filters)) {
            return $filters;
        }

        if (0 < \count($filters = array_keys((array) $this->filterManager->getFilterConfiguration()->all()))) {
            return $filters;
        }

        throw new RuntimeException('No filters have been defined in the active configuration!');
    }

    private function outputCommandHeader(): void
    {
        if (!$this->outputMachineReadable) {
            $this->io->title($this->getName(), 'liip/imagine-bundle');
        }
    }

    /**
     * @param string[] $images
     * @param string[] $filters
     */
    private function outputCommandResult(array $images, array $filters, string $singularAction): void
    {
        if (!$this->outputMachineReadable) {
            $wordPluralizer = function (int $count, string $singular) {
                return 1 === $count ? $singular : sprintf('%ss', $singular);
            };

            $imagePathsSize = \count($images);
            $filterSetsSize = \count($filters);
            $allActionsSize = 0 === $imagePathsSize ? $filterSetsSize : ($filterSetsSize * $imagePathsSize) - $this->failures;
            $allActionsWord = $wordPluralizer($allActionsSize, $singularAction);

            $rootTextOutput = sprintf('Completed %d %s', $allActionsSize, $allActionsWord);

            $detailTextFormat = '%d %s';

            $detailTextsOutput = [];

            if (0 !== $imagePathsSize) {
                $detailTextsOutput[] = sprintf($detailTextFormat, $imagePathsSize, $wordPluralizer($imagePathsSize, 'image'));
            }

            if (0 !== $filterSetsSize) {
                $detailTextsOutput[] = sprintf($detailTextFormat, $filterSetsSize, $wordPluralizer($filterSetsSize, 'filter'));
            }

            if (!empty($detailTextsOutput)) {
                $rootTextOutput = sprintf('%s (%s)', $rootTextOutput, implode(', ', $detailTextsOutput));
            }

            if ($this->failures) {
                $this->io->critBlock(sprintf('%s %%s', $rootTextOutput), [
                    sprintf('[encountered %d failures]', $this->failures),
                ]);
            } else {
                $this->io->okayBlock($rootTextOutput);
            }
        }
    }

    private function getResultCode(): int
    {
        return 0 === $this->failures ? 0 : 255;
    }
}
