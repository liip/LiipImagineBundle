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
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveCacheCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('liip:imagine:cache:remove')
            ->setDescription('Remove cache for given paths and set of filters.')
            ->addArgument('paths', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Image paths')
            ->addOption(
                'filters',
                'f',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Filters list'
            )
            ->setHelp(<<<'EOF'
The <info>%command.name%</info> command removes cache by specified parameters.

Paths should be separated by spaces:
<info>php app/console %command.name% path1 path2</info>
All cache for a given `paths` will be lost.

If you use --filters parameter:
<info>php app/console %command.name% --filters=thumb1 --filters=thumb2</info>
All cache for a given filters will be lost.

You can combine these parameters:
<info>php app/console %command.name% path1 path2 --filters=thumb1 --filters=thumb2</info>

<info>php app/console %command.name%</info>
Cache for all paths and filters will be lost when executing this command without parameters.
EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $paths = $input->getArgument('paths');
        $filters = $input->getOption('filters');

        if (empty($filters)) {
            $filters = null;
        }

        /* @var CacheManager cacheManager */
        $cacheManager = $this->getContainer()->get('liip_imagine.cache.manager');

        $cacheManager->remove($paths, $filters);
    }
}
