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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ResolveCacheCommand extends Command
{
    use CacheCommandTrait;
    protected static $defaultName = 'liip:imagine:cache:resolve';

    /**
     * @var DataManager
     */
    private $dataManager;

    public function __construct(DataManager $dataManager, CacheManager $cacheManager, FilterManager $filterManager)
    {
        parent::__construct();

        $this->dataManager = $dataManager;
        $this->cacheManager = $cacheManager;
        $this->filterManager = $filterManager;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Warms up the cache for the specified image sources with all or specified filters applied, and prints the list of cache files.')
            ->addArgument('paths', InputArgument::REQUIRED | InputArgument::IS_ARRAY,
                'Image file path(s) for which to generate the cached images.')
            ->addOption('filter', 'f', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Filter(s) to use for image resolution; if none explicitly passed, use all filters.')
            ->addOption('force', 'F', InputOption::VALUE_NONE,
                'Force generating the image and writing the cache, regardless of whether a cached version already exists.')
            ->addOption('no-colors', 'C', InputOption::VALUE_NONE,
                'Write only un-styled text output; remove any colors, styling, etc.')
            ->addOption('as-script', 'S', InputOption::VALUE_NONE,
                'Write only machine-readable output; silenced verbose reporting and implies --no-colors.')
            ->setHelp(<<<'EOF'
The <comment>%command.name%</comment> command resolves the passed image(s) for the resolved
filter(s), outputting results using the following basic format:
  <info>image.ext[filter] (resolved|cached|failed): (resolve-image-path|exception-message)</>

<comment># bin/console %command.name% --filter=thumb1 foo.ext bar.ext</comment>
Resolve <options=bold>both</> <comment>foo.ext</comment> and <comment>bar.ext</comment> images using <options=bold>one</> filter (<comment>thumb1</comment>), outputting:
  <info>- foo.ext[thumb1] status: http://localhost/media/cache/thumb1/foo.ext</>
  <info>- bar.ext[thumb1] status: http://localhost/media/cache/thumb1/bar.ext</>

<comment># bin/console %command.name% --filter=thumb1 --filter=thumb3 foo.ext</comment>
Resolve <comment>foo.ext</comment> image using <options=bold>two</> filters (<comment>thumb1</comment> and <comment>thumb3</comment>), outputting:
  <info>- foo.ext[thumb1] status: http://localhost/media/cache/thumb1/foo.ext</>
  <info>- foo.ext[thumb3] status: http://localhost/media/cache/thumb3/foo.ext</>

<comment># bin/console %command.name% foo.ext</comment>
Resolve <comment>foo.ext</comment> image using <options=bold>all</> filters (as none were specified), outputting:
  <info>- foo.ext[thumb1] status: http://localhost/media/cache/thumb1/foo.ext</>
  <info>- foo.ext[thumb2] status: http://localhost/media/cache/thumb2/foo.ext</>
  <info>- foo.ext[thumb3] status: http://localhost/media/cache/thumb2/foo.ext</>

<comment># bin/console %command.name% --force --filter=thumb1 foo.ext</comment>
Resolve <comment>foo.ext</comment> image using <options=bold>one</> filter (<comment>thumb1</comment>) and <options=bold>forcing resolution</> (regardless of cache), outputting:
  <info>- foo.ext[thumb1] resolved: http://localhost/media/cache/thumb1/foo.ext</>

EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->setupOutputStyle($input, $output);
        $this->outputCommandHeader();

        $forced = $input->getOption('force');
        [$images, $filters] = $this->resolveInputFiltersAndPaths($input);

        foreach ($images as $i) {
            foreach ($filters as $f) {
                $this->runCacheImageResolve($i, $f, $forced);
            }
        }

        $this->outputCommandResult($images, $filters, 'resolution');

        return $this->getResultCode();
    }

    private function runCacheImageResolve(string $image, string $filter, bool $forced): void
    {
        if (!$this->outputMachineReadable) {
            $this->io->text(' - ');
        }

        $this->io->group($image, $filter, 'blue');

        try {
            if ($forced || !$this->cacheManager->isStored($image, $filter)) {
                $this->cacheManager->store($this->filterManager->applyFilter($this->dataManager->find($filter, $image), $filter), $image, $filter);
                $this->io->status('resolved', 'green');
            } else {
                $this->io->status('cached', 'white');
            }

            $this->io->line(sprintf(' %s', $this->cacheManager->resolve($image, $filter)));
        } catch (\Exception $e) {
            ++$this->failures;

            $this->io->status('failed', 'red');
            $this->io->line(' %s', [$e->getMessage()]);
        }
    }
}
