<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Imagine\Cache;

use Liip\ImagineBundle\Imagine\Cache\Warmer\WarmerInterface;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;

/**
 * Class CacheWarmer
 *
 * @author Konstantin Tjuterev <kostik.lv@gmail.com>
 */
class CacheWarmer
{
    /**
     * @var WarmerInterface[]
     */
    protected $warmers = [];

    /**
     * Chunk size to query warmer in one step
     *
     * @var int
     */
    protected $chunkSize = 100;

    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * @var DataManager
     */
    protected $dataManager;

    /**
     * @var FilterManager
     */
    protected $filterManager;

    /**
     * @var callable
     */
    protected $loggerClosure;

    public function __construct(DataManager $dataManager, FilterManager $filterManager)
    {
        $this->dataManager = $dataManager;
        $this->filterManager = $filterManager;
    }

    /**
     * @param int $chunkSize
     *
     * @return CacheWarmer
     */
    public function setChunkSize($chunkSize)
    {
        $this->chunkSize = $chunkSize;

        return $this;
    }

    /**
     * Sets logger closure - a callable which will be passed verbose messages
     *
     * @param callable $loggerClosure
     *
     * @return CacheWarmer
     */
    public function setLoggerClosure($loggerClosure)
    {
        $this->loggerClosure = $loggerClosure;

        return $this;
    }

    /**
     * @param \Liip\ImagineBundle\Imagine\Cache\CacheManager $cacheManager
     *
     * @return CacheWarmer
     */
    public function setCacheManager($cacheManager)
    {
        $this->cacheManager = $cacheManager;

        return $this;
    }

    public function addWarmer($name, WarmerInterface $warmer)
    {
        $this->warmers[$name] = $warmer;
    }

    /**
     * @param bool       $force           If set to true, cache is warmed up for paths already stored in cached (regenerate thumbs)
     * @param array|null $selectedWarmers An optional array of warmers to process, if null - all warmers will be processed
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    public function warm($force = false, $selectedWarmers = null)
    {
        $filtersByWarmer = $this->getFiltersByWarmers();
        if (!$filtersByWarmer) {
            $this->log('No warmes are configured - add some as `warmers` param in your filter sets');

            return;
        }

        foreach ($filtersByWarmer as $warmerName => $filters) {
            if (isset($selectedWarmers) && !empty($selectedWarmers) && !in_array($warmerName, $selectedWarmers, true)) {
                $this->log(
                    sprintf(
                        'Skipping warmer %s as it\'s not listed in selected warmers: [%s]',
                        $warmerName,
                        implode(', ', $selectedWarmers)
                    )
                );
                continue;
            }
            if (!isset($this->warmers[$warmerName])) {
                throw new \InvalidArgumentException(sprintf('Could not find warmer "%s"', $warmerName));
            }

            $this->log(sprintf('Processing warmer "%s"', $warmerName));
            $start = 0;
            $warmer = $this->warmers[$warmerName];
            while ($paths = $warmer->getPaths($start, $this->chunkSize)) {
                $this->log(
                    sprintf(
                        'Processing chunk %d - %d for warmer "%s"',
                        $start,
                        $start + $this->chunkSize,
                        $warmerName
                    )
                );
                $warmedPaths = $this->warmPaths($paths, $filters, $force);
                $warmer->setWarmed($warmedPaths);
                $start += count($paths) - count($warmedPaths);
            }
            $this->log(sprintf('Finished processing warmer "%s"', $warmerName));
        }
    }

    public function clearWarmed($paths, $filters)
    {
        $filtersByWarmer = $this->getFiltersByWarmers();
        foreach ($filtersByWarmer as $warmerName => $warmerFilters) {
            if (array_intersect($filters, $warmerFilters)) {
                if (!isset($this->warmers[$warmerName])) {
                    throw new \InvalidArgumentException(sprintf('Could not find warmer "%s"', $warmerName));
                }
                $warmer = $this->warmers[$warmerName];
                $warmer->clearWarmed($paths);
            }
        }
    }

    protected function getFiltersByWarmers()
    {
        $all = $this->filterManager->getFilterConfiguration()->all();
        $warmers = [];
        foreach ($all as $filterSet => $config) {
            if (isset($config['warmers']) && $config['warmers']) {
                foreach ($config['warmers'] as $warmer) {
                    if (!isset($warmers[$warmer])) {
                        $warmers[$warmer] = [$filterSet];
                    } else {
                        $warmers[$warmer][] = $filterSet;
                    }
                }
            }
        }

        return $warmers;
    }

    /**
     * @param array $paths
     * @param array $filters
     * @param bool  $force
     *
     * @return array
     */
    protected function warmPaths($paths, $filters, $force)
    {
        $successfulWarmedPaths = [];
        foreach ($paths as $pathData) {
            $aPath = $pathData['path'];
            $binaries = [];
            foreach ($filters as $filter) {
                $this->log(sprintf('Warming up path "%s" for filter "%s"', $aPath, $filter));

                $isStored = $this->cacheManager->isStored($aPath, $filter);
                if ($force || !$isStored) {
                    // this is to avoid loading binary with the same loader for multiple filters
                    $loader = $this->dataManager->getLoader($filter);
                    $isStored = false;

                    try {
                        $hash = spl_object_hash($loader);
                        if (!isset($binaries[$hash])) {
                            // if NotLoadable is thrown - it will just bubble up
                            // everything returned by Warmer should be loadable
                            $binaries[$hash] = $this->dataManager->find($filter, $aPath);
                        }
                        $this->cacheManager->store(
                            $this->filterManager->applyFilter($binaries[$hash], $filter),
                            $aPath,
                            $filter
                        );

                        $isStored = true;
                    } catch (\RuntimeException $e) {
                        $message = sprintf('Unable to warm cache for filter "%s", due to - "%s"',
                            $filter, $e->getMessage());
                        $this->log($message, 'error');
                    }
                }

                if ($isStored) {
                    $successfulWarmedPaths[] = $pathData;
                }
            }
        }

        return $successfulWarmedPaths;
    }

    protected function log($message, $type = 'info')
    {
        if (is_callable($this->loggerClosure)) {
            $loggerClosure = $this->loggerClosure;
            $loggerClosure($message, $type);
        }
    }
}
