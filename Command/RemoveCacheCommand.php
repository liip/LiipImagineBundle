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

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveCacheCommand extends AbstractCacheCommand
{
    protected function configure()
    {
        $this
            ->setName('liip:imagine:cache:remove')
            ->setDescription('Remove asset caches for the passed asset paths(s) and filter name(s)')
            ->addArgument('paths', InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'Asset paths to remove caches of (passing none will use all paths).')
            ->addOption('filter', 'f', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Filter name to remove caches of (passing none will use all registered filters)')
            ->addOption('machine-readable', 'm', InputOption::VALUE_NONE,
                'Enable machine parseable output (removing extraneous output and all text styles)')
            ->addOption('filters', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Deprecated in 1.9.0 and removed in 2.0.0 (use the --filter option instead)')
            ->setHelp(<<<'EOF'
The <comment>%command.name%</comment> command removes asset cache for the passed image(s) and filter(s).

For an application that has only the two files "foo.ext" and "bar.ext", and only the filter sets
named "thumb_sm" and "thumb_lg", the following examples will behave as shown.

<comment># bin/console %command.name% foo.ext</comment>
Removes caches for <options=bold>foo.ext</> using <options=bold>all configured filters</>, outputting:
  <info>- foo.ext[thumb_sm] removed</>
  <info>- foo.ext[thumb_lg] removed</>

<comment># bin/console %command.name% --filter=thumb_sm --filter=thumb_lg foo.ext bar.ext</comment>
Removes caches for both <options=bold>foo.ext</> and <options=bold>bar.ext</> using <options=bold>thumb_sm</> and <options=bold>thumb_lg</> filters, outputting:
  <info>- foo.ext[thumb_sm] removed</>
  <info>- foo.ext[thumb_lg] removed</>
  <info>- bar.ext[thumb_sm] removed</>
  <info>- bar.ext[thumb_lg] removed</>

<comment># bin/console %command.name% --filter=thumb_sm</comment>
Removes <options=bold>all caches</> for <options=bold>thumb_sm</> filter, outputting:
  <info>- *[thumb_sm] glob-removal</>

<comment># bin/console %command.name%</comment>
Removes <options=bold>all caches</> for <options=bold>all configured filters</>, removing all cached assets, outputting:
  <info>- *[thumb_sm] glob-removal</>
  <info>- *[thumb_lg] glob-removal</>

<comment># bin/console %command.name% --force --filter=thumb_sm foo.ext</comment>
Removing caches for <options=bold>foo.ext</> using <options=bold>thumb_sm</> filter when <options=bold>already removed</> will caused <options=bold>skipping</>, outputting:
  <info>- foo.ext[thumb_sm] skipped</>

<comment># bin/console %command.name% --filter=does_not_exist --filter=thumb_sm foo.ext</comment>
Removes caches for <options=bold>foo.ext</> for <options=bold>thumb_sm</> while <options=bold>failing inline</> on invalid filter (or other errors), outputting:
  <info>- foo.ext[does_not_exist] failure: Could not find configuration for a filter: does_not_exist</>
  <info>- foo.ext[thumb_sm] removed</>

EOF
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initializeInstState($input, $output);
        $this->writeCommandHeading('remove');

        $filters = $this->resolveFilters($input);
        $targets = $input->getArgument('paths');

        if (0 === count($targets)) {
            $this->doCacheRemoveAsGlobbedFilterName($filters);
        } else {
            $this->doCacheRemoveAsFiltersAndTargets($filters, $targets);
        }

        return $this->getReturnCode();
    }

    /**
     * @param string[] $filters
     */
    private function doCacheRemoveAsGlobbedFilterName(array $filters)
    {
        foreach ($filters as $f) {
            $this->doCacheRemove($f);
        }

        $this->writeResultSummary($filters, array(), true);
    }

    /**
     * @param string[] $filters
     * @param string[] $targets
     */
    private function doCacheRemoveAsFiltersAndTargets(array $filters, array $targets)
    {
        foreach ($targets as $t) {
            foreach ($filters as $f) {
                $this->doCacheRemove($f, $t);
            }
        }

        $this->writeResultSummary($filters, $targets);
    }

    /**
     * @param string      $filter
     * @param string|null $target
     */
    private function doCacheRemove($filter, $target = null)
    {
        $this->writeActionStart($filter, $target);

        try {
            if (null === $target) {
                $this->getCacheManager()->remove(null, $filter);
                $this->writeActionResult('glob-removal', false);
            } elseif ($this->getCacheManager()->isStored($target, $filter)) {
                $this->getCacheManager()->remove($target, $filter);
                $this->writeActionResult('removed', false);
            } else {
                $this->writeActionResult('skipped', false);
            }
        } catch (\Exception $e) {
            $this->writeActionException($e);
        }
    }
}
