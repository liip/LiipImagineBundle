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

use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Service\FilterService;
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
            ->addArgument('paths', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Image paths')
            ->addOption(
                'filters',
                'f',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Filters list'
            )->setHelp(<<<'EOF'
The <info>%command.name%</info> command resolves cache by specified parameters.
It returns list of urls.

<info>php app/console %command.name% path1 path2 --filters=thumb1</info>
Cache for this two paths will be resolved with passed filter.
As a result you will get<info>
    http://localhost/media/cache/thumb1/path1
    http://localhost/media/cache/thumb1/path2</info>

You can pass few filters:
<info>php app/console %command.name% path1 --filters=thumb1 --filters=thumb2</info>
As a result you will get<info>
    http://localhost/media/cache/thumb1/path1
    http://localhost/media/cache/thumb2/path1</info>

If you omit --filters parameter then to resolve given paths will be used all configured and available filters in application:
<info>php app/console %command.name% path1</info>
As a result you will get<info>
    http://localhost/media/cache/thumb1/path1
    http://localhost/media/cache/thumb2/path1</info>
EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $paths = $input->getArgument('paths');
        $filters = $input->getOption('filters');

        /* @var FilterManager $filterManager */
        $filterManager = $this->getContainer()->get('liip_imagine.filter.manager');

        /* @var FilterService $filterService */
        $filterService = $this->getContainer()->get('liip_imagine.service.filter');

        if (empty($filters)) {
            $filters = array_keys($filterManager->getFilterConfiguration()->all());
        }

        foreach ($paths as $path) {
            foreach ($filters as $filter) {
                $output->writeln($filterService->getUrlOfFilteredImage($path, $filter));
            }
        }
    }
}
