<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Service;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Exception\Imagine\Filter\NonExistingFilterException;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class FilterService
{
    /**
     * @var DataManager
     */
    private $dataManager;

    /**
     * @var FilterManager
     */
    private $filterManager;

    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var bool
     */
    private $webpGenerate;

    /**
     * @var mixed[]
     */
    private $webpOptions;

    public function __construct(
        DataManager $dataManager,
        FilterManager $filterManager,
        CacheManager $cacheManager,
        bool $webpGenerate,
        array $webpOptions,
        ?LoggerInterface $logger = null
    ) {
        $this->dataManager = $dataManager;
        $this->filterManager = $filterManager;
        $this->cacheManager = $cacheManager;
        $this->webpGenerate = $webpGenerate;
        $this->webpOptions = $webpOptions;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * @param string $path
     * @param string $filter
     *
     * @return bool Returns true if we removed at least one cached image
     */
    public function bustCache($path, $filter)
    {
        $busted = false;

        foreach ($this->buildFilterPathContainers($path) as $filterPathContainer) {
            if ($this->cacheManager->isStored($filterPathContainer->getTarget(), $filter)) {
                $this->cacheManager->remove($filterPathContainer->getTarget(), $filter);

                $busted = true;
            }
        }

        return $busted;
    }

    /**
     * @param bool $forced Force warm up cache
     *
     * @return bool Returns true if the cache is warmed up
     */
    public function warmUpCache(
        string $path,
        string $filter,
        ?string $resolver = null,
        bool $forced = false
    ): bool {
        $warmedUp = false;

        foreach ($this->buildFilterPathContainers($path) as $filterPathContainer) {
            if ($this->warmUpCacheFilterPathContainer($filterPathContainer, $filter, $resolver, $forced)) {
                $warmedUp = true;
            }
        }

        return $warmedUp;
    }

    /**
     * @param string      $path
     * @param string      $filter
     * @param string|null $resolver
     *
     * @return string
     */
    public function getUrlOfFilteredImage($path, $filter, $resolver = null, bool $webpSupported = false)
    {
        foreach ($this->buildFilterPathContainers($path) as $filterPathContainer) {
            $this->warmUpCacheFilterPathContainer($filterPathContainer, $filter, $resolver);
        }

        return $this->resolveFilterPathContainer(new FilterPathContainer($path), $filter, $resolver, $webpSupported);
    }

    /**
     * @param string      $path
     * @param string      $filter
     * @param string|null $resolver
     *
     * @return string
     */
    public function getUrlOfFilteredImageWithRuntimeFilters(
        $path,
        $filter,
        array $runtimeFilters = [],
        $resolver = null,
        bool $webpSupported = false
    ) {
        $runtimePath = $this->cacheManager->getRuntimePath($path, $runtimeFilters);
        $runtimeOptions = [
            'filters' => $runtimeFilters,
        ];

        foreach ($this->buildFilterPathContainers($path, $runtimePath, $runtimeOptions) as $filterPathContainer) {
            $this->warmUpCacheFilterPathContainer($filterPathContainer, $filter, $resolver);
        }

        return $this->resolveFilterPathContainer(
            new FilterPathContainer($path, $runtimePath, $runtimeOptions),
            $filter,
            $resolver,
            $webpSupported
        );
    }

    /**
     * @param mixed[] $options
     *
     * @return FilterPathContainer[]
     */
    private function buildFilterPathContainers(string $source, string $target = '', array $options = []): array
    {
        $basePathContainer = new FilterPathContainer($source, $target, $options);
        $filterPathContainers = [$basePathContainer];

        if ($this->webpGenerate) {
            $filterPathContainers[] = $basePathContainer->createWebp($this->webpOptions);
        }

        return $filterPathContainers;
    }

    private function resolveFilterPathContainer(
        FilterPathContainer $filterPathContainer,
        string $filter,
        ?string $resolver = null,
        bool $webpSupported = false
    ): string {
        $path = $filterPathContainer->getTarget();

        if ($this->webpGenerate && $webpSupported) {
            $path = $filterPathContainer->createWebp($this->webpOptions)->getTarget();
        }

        return $this->cacheManager->resolve($path, $filter, $resolver);
    }

    /**
     * @param bool $forced Force warm up cache
     *
     * @return bool Returns true if the cache is warmed up
     */
    private function warmUpCacheFilterPathContainer(
        FilterPathContainer $filterPathContainer,
        string $filter,
        ?string $resolver = null,
        bool $forced = false
    ): bool {
        if ($forced || !$this->cacheManager->isStored($filterPathContainer->getTarget(), $filter, $resolver)) {
            $this->cacheManager->store(
                $this->createFilteredBinary($filterPathContainer, $filter),
                $filterPathContainer->getTarget(),
                $filter,
                $resolver
            );

            return true;
        }

        return false;
    }

    /**
     * @throws NonExistingFilterException
     */
    private function createFilteredBinary(FilterPathContainer $filterPathContainer, string $filter): BinaryInterface
    {
        $binary = $this->dataManager->find($filter, $filterPathContainer->getSource());

        try {
            return $this->filterManager->applyFilter($binary, $filter, $filterPathContainer->getOptions());
        } catch (NonExistingFilterException $e) {
            $this->logger->debug(sprintf(
                'Could not locate filter "%s" for path "%s". Message was "%s"',
                $filter,
                $filterPathContainer->getSource(),
                $e->getMessage()
            ));

            throw $e;
        }
    }
}
