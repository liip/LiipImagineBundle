<?php

namespace Liip\ImagineBundle\Command;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Cache\CacheWarmer;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A command to warm up images cache (i.e. pre-generate thumbnails)
 *
 * @author Konstantin Tjuterev <kostik.lv@gmail.com>
 */
class WarmCacheCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('liip:imagine:cache:warm')
            ->setDescription('Warms cache for paths provided by given warmers (or all warmers, if run w/o params)')
            ->addOption('chunk-size', 'c', InputOption::VALUE_REQUIRED, 'Chunk size', 100)
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force cache warm up for already cached images')
            ->addArgument('warmers', InputArgument::OPTIONAL|InputArgument::IS_ARRAY, 'Warmers names')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command warms up cache by specified parameters.

A warmer can be configured for one or more filter set. A warmer should return a list of paths.
This command gets the paths from warmer and create cache (i.e. filtered image) for each filter configured for given warmer.

Warmers should be separated by spaces:
<info>php app/console %command.name% warmer1 warmer2</info>
All cache for a given `warmers` will be warmed up

<info>php app/console %command.name%</info>
Cache for all warmers will be warmed up when executing this command without parameters.

Note, that <info>--force</info> option will force regeneration of the cache only if warmer returns the path.
Generally, there should be NO need to use this option, instead, use <info>liip:imagine:cache:remove</info> command to clear cache.
Then run this command to warm-up the cache
EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $warmers = $input->getArgument('warmers');

        /** @var CacheWarmer $cacheWarmer */
        $cacheWarmer = $this->getContainer()->get('liip_imagine.cache.warmer');
        $cacheWarmer->setLoggerClosure($this->getLoggerClosure($output));

        if ($chunkSize = $input->getOption('chunk-size')) {
            $cacheWarmer->setChunkSize($chunkSize);
        }

        $force = false;
        if ($input->getOption('force')) {
            $force = true;
        }

        $cacheWarmer->warm($force, $warmers);
    }

    /**
     * Returns Logger Closure
     *
     * @return callable
     */
    protected function getLoggerClosure(OutputInterface $output)
    {
        $loggerClosure = function ($message, $msgType = 'info') use ($output) {
                $time = date('Y-m-d G:i:s');
                $message = sprintf(
                    '<comment>%s | Mem cur/peak: %dm/%dm </comment> | <' . $msgType . '>%s</' . $msgType . '>',
                    $time,
                    round(memory_get_usage(true) / 1024 / 1024, 1),
                    round(memory_get_peak_usage(true) / 1024 / 1024, 1),
                    $message
                );
                $output->writeln($message);
        };
        return $loggerClosure;
    }
}
