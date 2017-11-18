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

class ResolveCacheCommand extends AbstractCacheCommand
{
    protected function configure()
    {
        $this
            ->setName('liip:imagine:cache:resolve')
            ->setDescription('Resolves asset caches for the passed asset paths(s) and filter set name(s)')
            ->addArgument('paths', InputArgument::REQUIRED | InputArgument::IS_ARRAY,
                'Asset paths to resolve caches for')
            ->addOption('filter', 'f', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Filter name to resolve caches for (passing none will use all registered filters)')
            ->addOption('force', 'F', InputOption::VALUE_NONE,
                'Force asset cache resolution (ignoring whether it already cached)')
            ->addOption('machine-readable', 'm', InputOption::VALUE_NONE,
                'Enable machine parseable output (removing extraneous output and all text styles)')
            ->addOption('filters', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Deprecated in 1.9.0 and removed in 2.0.0 (use the --filter option instead)')
            ->setHelp(<<<'EOF'
The <comment>%command.name%</comment> command resolves asset cache for the passed image(s) and filter(s).

For an application that has only the two files "foo.ext" and "bar.ext", and only the filter sets
named "thumb_sm" and "thumb_lg", the following examples will behave as shown.

<comment># bin/console %command.name% foo.ext bar.ext</comment>
Resolves caches for <options=bold>both</> <options=bold>foo.ext</> and <options=bold>bar.ext</> using <options=bold>all configured filters</>, outputting:
  <info>- foo.ext[thumb_sm] resolved: http://localhost/media/cache/thumb_sm/foo.ext</>
  <info>- bar.ext[thumb_sm] resolved: http://localhost/media/cache/thumb_sm/bar.ext</>
  <info>- foo.ext[thumb_lg] resolved: http://localhost/media/cache/thumb_lg/foo.ext</>
  <info>- bar.ext[thumb_lg] resolved: http://localhost/media/cache/thumb_lg/bar.ext</>

<comment># bin/console %command.name% --filter=thumb_sm foo.ext</comment>
Resolves caches for <options=bold>foo.ext</> using <options=bold>only</> <options=bold>thumb_sm</> filter, outputting:
  <info>- foo.ext[thumb_sm] resolved: http://localhost/media/cache/thumb_sm/foo.ext</>

<comment># bin/console %command.name% --filter=thumb_sm --filter=thumb_lg foo.ext</comment>
Resolves caches for <options=bold>foo.ext</> using <options=bold>both</> <options=bold>thumb_sm</> and <options=bold>thumb_lg</> filters, outputting:
  <info>- foo.ext[thumb_sm] resolved: http://localhost/media/cache/thumb_sm/foo.ext</>
  <info>- foo.ext[thumb_lg] resolved: http://localhost/media/cache/thumb_lg/foo.ext</>

<comment># bin/console %command.name% --force --filter=thumb_sm foo.ext</comment>
Resolving caches for <options=bold>foo.ext</> using <options=bold>thumb_sm</> filter when <options=bold>already cached</> will caused <options=bold>skipped</>, outputting:
  <info>- foo.ext[thumb_sm] skipped: http://localhost/media/cache/thumb_sm/foo.ext</>

<comment># bin/console %command.name% --force --filter=thumb_sm foo.ext</comment>
Resolving caches for <options=bold>foo.ext</> using <options=bold>thumb_sm</> filter when <options=bold>already cached</> with <options=bold>force</> option <options=bold>re-resolves</> (ignoring cache), outputting:
  <info>- foo.ext[thumb_sm] resolved: http://localhost/media/cache/thumb_sm/foo.ext</>

<comment># bin/console %command.name% --filter=does_not_exist --filter=thumb_sm foo.ext</comment>
Resolves caches for <options=bold>foo.ext</> using <options=bold>thumb_sm</> while <options=bold>failing inline</> on invalid filter (or other errors), outputting:
  <info>- foo.ext[does_not_exist] failed: Could not find configuration for a filter: does_not_exist</>
  <info>- foo.ext[thumb_sm] removed: http://localhost/media/cache/thumb_sm/foo.ext</>

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
        $this->writeCommandHeading('resolve');

        $filters = $this->resolveFilters($input);
        $targets = $input->getArgument('paths');
        $doForce = $input->getOption('force');

        foreach ($targets as $t) {
            foreach ($filters as $f) {
                $this->doCacheResolve($t, $f, $doForce);
            }
        }

        $this->writeResultSummary($filters, $targets);

        return $this->getReturnCode();
    }

    /**
     * @param string $target
     * @param string $filter
     * @param bool   $forced
     */
    private function doCacheResolve($target, $filter, $forced)
    {
        $this->writeActionStart($filter, $target);

        try {
            if ($forced || !$this->getCacheManager()->isStored($target, $filter)) {
                $this->getCacheManager()->store($this->getFilterManager()->applyFilter($this->getDataManager()->find($filter, $target), $filter), $target, $filter);
                $this->writeActionResult('resolved');
            } else {
                $this->writeActionResult('skipped');
            }

            $this->writeActionDetail($this->getCacheManager()->resolve($target, $filter));
        } catch (\Exception $e) {
            $this->writeActionException($e);
        }
    }
}
