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
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveCacheCommand extends Command
{
    use CacheCommandTrait;
    protected static $defaultName = 'liip:imagine:cache:remove';

    public function __construct(CacheManager $cacheManager, FilterManager $filterManager)
    {
        parent::__construct();

        $this->cacheManager = $cacheManager;
        $this->filterManager = $filterManager;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Remove cache entries for given paths and filters.')
            ->addArgument('paths', InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'Image file path(s) to run resolution on.')
            ->addOption('filter', 'f', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Filter(s) to use for image remove; if none explicitly passed, use all filters.')
            ->addOption('no-colors', 'C', InputOption::VALUE_NONE,
                'Write only un-styled text output; remove any colors, styling, etc.')
            ->addOption('as-script', 'S', InputOption::VALUE_NONE,
                'Write only machine-readable output; silenced verbose reporting and implies --no-colors.')
            ->setHelp(<<<'EOF'
The <comment>%command.name%</comment> command removes the passed image(s) cache entry for the
resolved filter(s), outputting results using the following basic format:
  <info>image.ext[filter] (removed|skipped|failure)[: (image-path|exception-message)]</>

<comment># bin/console %command.name% --filter=thumb1 foo.ext bar.ext</comment>
Remove cache for <options=bold>both</> <comment>foo.ext</comment> and <comment>bar.ext</comment> images for <options=bold>one</> filter (<comment>thumb1</comment>), outputting:
  <info>- foo.ext[thumb1] removed</>
  <info>- bar.ext[thumb1] removed</>

<comment># bin/console %command.name% --filter=thumb1 --filter=thumb3 foo.ext</comment>
Remove cache for <comment>foo.ext</comment> image using <options=bold>two</> filters (<comment>thumb1</comment> and <comment>thumb3</comment>), outputting:
  <info>- foo.ext[thumb1] removed</>
  <info>- foo.ext[thumb3] removed</>

<comment># bin/console %command.name% foo.ext</comment>
Remove cache for <comment>foo.ext</comment> image using <options=bold>all</> filters (as none were specified), outputting:
  <info>- foo.ext[thumb1] removed</>
  <info>- foo.ext[thumb2] removed</>
  <info>- foo.ext[thumb3] removed</>

EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->setupOutputStyle($input, $output);
        $this->outputCommandHeader();

        [$images, $filters] = $this->resolveInputFiltersAndPaths($input);

        if (empty($images)) {
            $this->cacheManager->remove(null, $filters);
        } else {
            foreach ($images as $i) {
                foreach ($filters as $f) {
                    $this->runCacheImageRemove($i, $f);
                }
            }
        }

        $this->outputCommandResult($images, $filters, 'removal');

        return $this->getResultCode();
    }

    private function runCacheImageRemove(string $image, string $filter): void
    {
        if (!$this->outputMachineReadable) {
            $this->io->text(' - ');
        }

        $this->io->group($image, $filter, 'blue');

        if ($this->cacheManager->isStored($image, $filter)) {
            $this->cacheManager->remove($image, $filter);
            $this->io->status('removed', 'green');
        } else {
            $this->io->status('skipped', 'yellow');
        }

        $this->io->newline();
    }
}
