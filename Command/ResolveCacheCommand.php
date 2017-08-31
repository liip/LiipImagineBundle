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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ResolveCacheCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('liip:imagine:cache:resolve')
            ->setDescription('Resolve cache for given path and set of filters.')
            ->addArgument('paths', InputArgument::REQUIRED | InputArgument::IS_ARRAY,
                'Any number of image paths to act on.')
            ->addOption('filters', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'List of filters to apply to passed images (Deprecated, use "filter").')
            ->addOption('filter', 'f', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'List of filters to apply to passed images.')
            ->addOption('force', 'F', InputOption::VALUE_NONE,
                'Force image resolution regardless of cache.')
            ->addOption('as-script', 's', InputOption::VALUE_NONE,
                'Only print machine-parseable results.')
            ->setHelp(<<<'EOF'
The <comment>%command.name%</comment> command resolves the passed image(s) for the resolved
filter(s), outputting results using the following basic format:
  <info>- "image.ext[filter]" (resolved|cached|failed) as "path/to/cached/image.ext"</>

<comment># bin/console %command.name% --filter=thumb1 foo.ext bar.ext</comment>
Resolve <options=bold>both</> <comment>foo.ext</comment> and <comment>bar.ext</comment> using <comment>thumb1</comment> filter, outputting:
  <info>- "foo.ext[thumb1]" resolved as "http://localhost/media/cache/thumb1/foo.ext"</>
  <info>- "bar.ext[thumb1]" resolved as "http://localhost/media/cache/thumb1/bar.ext"</>

<comment># bin/console %command.name% --filter=thumb1 --filter=thumb2 foo.ext</comment>
Resolve <comment>foo.ext</comment> using <options=bold>both</> <comment>thumb1</comment> and <comment>thumb2</comment> filters, outputting:
  <info>- "foo.ext[thumb1]" resolved as "http://localhost/media/cache/thumb1/foo.ext"</>
  <info>- "foo.ext[thumb2]" resolved as "http://localhost/media/cache/thumb2/foo.ext"</>

<comment># bin/console %command.name% foo.ext</comment>
Resolve <comment>foo.ext</comment> using <options=bold>all configured filters</> (as none are specified), outputting:
  <info>- "foo.ext[thumb1]" resolved as "http://localhost/media/cache/thumb1/foo.ext"</>
  <info>- "foo.ext[thumb2]" resolved as "http://localhost/media/cache/thumb2/foo.ext"</>

<comment># bin/console %command.name% --force --filter=thumb1 foo.ext</comment>
Resolve <comment>foo.ext</comment> using <comment>thumb1</comment> and <options=bold>force creation</> regardless of cache, outputting:
  <info>- "foo.ext[thumb1]" resolved as "http://localhost/media/cache/thumb1/foo.ext"</>

EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $force = $input->getOption('force');
        $paths = $input->getArgument('paths');
        $filters = $this->resolveInputFilters($input);
        $machine = $input->getOption('as-script');
        $failed = 0;

        $filterManager = $this->getFilterManager();
        $dataManager = $this->getDataManager();
        $cacheManager = $this->getCacheManager();

        if (0 === count($filters)) {
            $filters = array_keys($filterManager->getFilterConfiguration()->all());
        }

        $this->outputTitle($output, $machine);

        foreach ($paths as $path) {
            foreach ($filters as $filter) {
                $output->write(sprintf('- %s[%s] ', $path, $filter));

                try {
                    if ($force || !$cacheManager->isStored($path, $filter)) {
                        $cacheManager->store($filterManager->applyFilter($dataManager->find($filter, $path), $filter), $path, $filter);
                        $output->write('resolved: ');
                    } else {
                        $output->write('cached: ');
                    }

                    $output->writeln($cacheManager->resolve($path, $filter));
                } catch (\Exception $e) {
                    $output->writeln(sprintf('failed: %s', $e->getMessage()));
                    ++$failed;
                }
            }
        }

        $this->outputSummary($output, $machine, count($filters), count($paths), $failed);

        return 0 === $failed ? 0 : 255;
    }

    /**
     * @param OutputInterface $output
     * @param bool            $machine
     */
    private function outputTitle(OutputInterface $output, $machine)
    {
        if (!$machine) {
            $title = '[liip/imagine-bundle] Image Resolver';

            $output->writeln(sprintf('<info>%s</info>', $title));
            $output->writeln(str_repeat('=', strlen($title)));
            $output->writeln('');
        }
    }

    /**
     * @param OutputInterface $output
     * @param bool            $machine
     * @param int             $filters
     * @param int             $paths
     * @param int             $failed
     */
    private function outputSummary(OutputInterface $output, $machine, $filters, $paths, $failed)
    {
        if (!$machine) {
            $operations = ($filters * $paths) - $failed;

            $output->writeln('');
            $output->writeln(vsprintf('Completed %d %s (%d %s on %d %s) <fg=red;options=bold>%s</>', array(
                $operations,
                $this->pluralizeWord($operations, 'operation'),
                $filters,
                $this->pluralizeWord($filters, 'filter'),
                $paths,
                $this->pluralizeWord($paths, 'image'),
                0 === $failed ? '' : sprintf('[encountered %d %s]', $failed, $this->pluralizeWord($failed, 'failure')),
            )));
        }
    }

    /**
     * @param int    $count
     * @param string $singular
     * @param string $pluralEnding
     *
     * @return string
     */
    private function pluralizeWord($count, $singular, $pluralEnding = 's')
    {
        return 1 === $count ? $singular : $singular.$pluralEnding;
    }

    /**
     * @param InputInterface $input
     *
     * @return array|mixed
     */
    private function resolveInputFilters(InputInterface $input)
    {
        $filters = $input->getOption('filter');

        if (count($filtersDeprecated = $input->getOption('filters'))) {
            $filters = array_merge($filters, $filtersDeprecated);
            @trigger_error('As of 1.9, use of the "--filters" option has been deprecated in favor of "--filter" and will be removed in 2.0.', E_USER_DEPRECATED);
        }

        return $filters;
    }

    /**
     * @return FilterManager
     */
    private function getFilterManager()
    {
        return $this->getContainer()->get('liip_imagine.filter.manager');
    }

    /**
     * @return DataManager
     */
    private function getDataManager()
    {
        return $this->getContainer()->get('liip_imagine.data.manager');
    }

    /**
     * @return CacheManager
     */
    private function getCacheManager()
    {
        return $this->getContainer()->get('liip_imagine.cache.manager');
    }
}
