<?php

namespace Liip\ImagineBundle\Command;

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
            ->addArgument('paths', InputArgument::REQUIRED|InputArgument::IS_ARRAY, 'Image paths')
            ->addOption(
                'filters',
                'f',
                InputOption::VALUE_OPTIONAL|InputOption::VALUE_IS_ARRAY,
                'Filters list'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $paths = $input->getArgument('paths');
        $filters = $input->getOption('filters');

        /** @var FilterManager filterManager */
        $filterManager = $this->getContainer()->get('liip_imagine.filter.manager');
        /** @var CacheManager cacheManager */
        $cacheManager  = $this->getContainer()->get('liip_imagine.cache.manager');
        /** @var DataManager dataManager */
        $dataManager   = $this->getContainer()->get('liip_imagine.data.manager');

        if (empty($filters)) {
            $filters = array_keys($filterManager->getFilterConfiguration()->all());
        }

        foreach ($paths as $path) {
            foreach ($filters as $filter) {
                if (!$cacheManager->isStored($path, $filter)) {
                    $binary = $dataManager->find($filter, $path);

                    $cacheManager->store(
                        $filterManager->applyFilter($binary, $filter),
                        $path,
                        $filter
                    );
                }

                $output->writeln($cacheManager->resolve($path, $filter));
            }
        }
    }
}
