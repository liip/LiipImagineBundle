<?php

namespace Liip\ImagineBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResolveCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('liip_imagine:cache:resolve')
            ->setDescription('Resolve url for image')
            ->addArgument('path', InputArgument::REQUIRED, 'Image path')
            ->addArgument('filters', InputArgument::OPTIONAL|InputArgument::IS_ARRAY, 'Filters list');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path    = $input->getArgument('path');
        $filters = $input->getArgument('filters');

        /** @var FilterManager filterManager */
        $filterManager = $this->getContainer()->get('liip_imagine.filter.manager');
        /** @var CacheManager cacheManager */
        $cacheManager  = $this->getContainer()->get('liip_imagine.cache.manager');
        /** @var DataManager dataManager */
        $dataManager   = $this->getContainer()->get('liip_imagine.data.manager');

        if (empty($filters)) {
            $filters = array_keys($filterManager->getFilterConfiguration()->all());
        }

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
