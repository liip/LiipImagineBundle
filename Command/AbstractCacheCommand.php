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

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCacheCommand extends ContainerAwareCommand
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var bool
     */
    protected $machineReadable;

    /**
     * @var int
     */
    protected $actionFailures;

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function initializeInstState(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->machineReadable = $input->getOption('machine-readable');
        $this->actionFailures = 0;
    }

    /**
     * @param InputInterface $input
     *
     * @return array
     */
    protected function resolveFilters(InputInterface $input)
    {
        $filters = $input->getOption('filter');

        if (0 !== count($deprecated = $input->getOption('filters'))) {
            $filters = array_merge($filters, $deprecated);
            @trigger_error('The --filters option was deprecated in 1.9.0 and removed in 2.0.0. Use the --filter option instead.', E_USER_DEPRECATED);
        }

        if (0 === count($filters) && 0 === count($filters = array_keys($this->getFilterManager()->getFilterConfiguration()->all()))) {
            $this->output->writeln('<bg=red;fg=white> [ERROR] You do not have any configured filters available. </>');
        }

        return $filters;
    }

    /**
     * @param string $command
     */
    protected function writeCommandHeading($command)
    {
        if ($this->machineReadable) {
            return;
        }

        $title = sprintf('[liip/imagine-bundle] %s Image Caches', ucfirst($command));
        $this->writeNewline();
        $this->output->writeln(sprintf('<info>%s</info>', $title));
        $this->output->writeln(str_repeat('=', strlen($title)));
        $this->writeNewline();
    }

    /**
     * @param string[] $filters
     * @param string[] $targets
     * @param bool     $glob
     */
    protected function writeResultSummary(array $filters, array $targets, $glob = false)
    {
        if ($this->machineReadable) {
            return;
        }

        $targetCount = count($targets);
        $filterCount = count($filters);
        $actionCount = ($glob ? $filterCount : ($filterCount * $targetCount)) - $this->actionFailures;

        $this->writeNewline();
        $this->output->writeln(vsprintf('<fg=black;bg=green> Completed %d %s (%d %s / %s %s) </>%s', array(
            $actionCount,
            $this->getPluralized($actionCount, 'operation'),
            $filterCount,
            $this->getPluralized($filterCount, 'filter'),
            $glob ? '?' : $targetCount,
            $this->getPluralized($targetCount, 'image'),
            $this->getResultSummaryFailureMarkup(),
        )));
        $this->writeNewline();
    }

    /**
     * @param string      $filter
     * @param string|null $target
     */
    protected function writeActionStart($filter, $target = null)
    {
        if (!$this->machineReadable) {
            $this->output->write(' - ');
        }

        $this->output->write(sprintf('%s[%s] ', $target ?: '*', $filter));
    }

    /**
     * @param string $result
     * @param bool   $continued
     */
    protected function writeActionResult($result, $continued = true)
    {
        $this->output->write($continued ? sprintf('%s: ', $result) : $result);

        if (!$continued) {
            $this->writeNewline();
        }
    }

    /**
     * @param string $detail
     */
    protected function writeActionDetail($detail)
    {
        $this->output->write($detail);
        $this->writeNewline();
    }

    /**
     * @param \Exception $exception
     */
    protected function writeActionException(\Exception $exception)
    {
        $this->writeActionResult('failure');
        $this->writeActionDetail($exception->getMessage());
        ++$this->actionFailures;
    }

    /**
     * @return int
     */
    protected function getReturnCode()
    {
        return 0 === $this->actionFailures ? 0 : 255;
    }

    /**
     * @return CacheManager
     */
    protected function getCacheManager()
    {
        static $manager;

        if (null === $manager) {
            $manager = $this->getContainer()->get('liip_imagine.cache.manager');
        }

        return $manager;
    }

    /**
     * @return FilterManager
     */
    protected function getFilterManager()
    {
        static $manager;

        if (null === $manager) {
            $manager = $this->getContainer()->get('liip_imagine.filter.manager');
        }

        return $manager;
    }

    /**
     * @return DataManager
     */
    protected function getDataManager()
    {
        static $manager;

        if (null === $manager) {
            $manager = $this->getContainer()->get('liip_imagine.data.manager');
        }

        return $manager;
    }

    /**
     * @param int $count
     */
    private function writeNewline($count = 1)
    {
        $this->output->write(str_repeat(PHP_EOL, $count));
    }

    /**
     * @param int    $size
     * @param string $word
     *
     * @return string
     */
    private function getPluralized($size, $word)
    {
        return 1 === $size ? $word : sprintf('%ss', $word);
    }

    /**
     * @return string
     */
    private function getResultSummaryFailureMarkup()
    {
        if (0 === $this->actionFailures) {
            return '';
        }

        return vsprintf(' <fg=white;bg=red;options=bold> encountered %s %s </>', array(
            $this->actionFailures,
            $this->getPluralized($this->actionFailures, 'failure'),
        ));
    }
}
