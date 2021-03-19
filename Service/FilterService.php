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
     * @var array
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
     */
    public function bustCache($path, $filter)
    {
        $basePathContainer = new FilterPathContainer($path);
        $filterPathContainers = [$basePathContainer];

        if ($this->webpGenerate) {
            $filterPathContainers[] = $basePathContainer->createWebp($this->webpOptions);
        }

        foreach ($filterPathContainers as $filterPathContainer) {
            if ($this->cacheManager->isStored($filterPathContainer->getTarget(), $filter)) {
                $this->cacheManager->remove($filterPathContainer->getTarget(), $filter);
            }
        }
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
        $basePathContainer = new FilterPathContainer($path);

        return $this->getUrlOfFilteredImageByContainer($basePathContainer, $filter, $resolver, $webpSupported);
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
        $basePathContainer = new FilterPathContainer($path, $runtimePath, [
            'filters' => $runtimeFilters,
        ]);

        return $this->getUrlOfFilteredImageByContainer($basePathContainer, $filter, $resolver, $webpSupported);
    }

    private function getUrlOfFilteredImageByContainer(
        FilterPathContainer $basePathContainer,
        string $filter,
        ?string $resolver = null,
        bool $webpSupported = false
    ): string {
        $filterPathContainers = [$basePathContainer];

        if ($this->webpGenerate) {
            $webpPathContainer = $basePathContainer->createWebp($this->webpOptions);
            $filterPathContainers[] = $webpPathContainer;
        }

        foreach ($filterPathContainers as $filterPathContainer) {
            if (!$this->cacheManager->isStored($filterPathContainer->getTarget(), $filter, $resolver)) {
                $this->cacheManager->store(
                    $this->createFilteredBinary($filterPathContainer, $filter),
                    $filterPathContainer->getTarget(),
                    $filter,
                    $resolver
                );
            }
        }

        if ($webpSupported && isset($webpPathContainer)) {
            return $this->cacheManager->resolve($webpPathContainer->getTarget(), $filter, $resolver);
        }

        return $this->cacheManager->resolve($basePathContainer->getTarget(), $filter, $resolver);
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
