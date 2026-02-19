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
     * @var array
     */
    private $alternativeFormats;

    /**
     * @param array|bool $alternativeFormats (previously webpGenerate)
     */
    public function __construct(
        DataManager $dataManager,
        FilterManager $filterManager,
        CacheManager $cacheManager,
        $alternativeFormats = [],
        array $webpOptions = [],
        ?LoggerInterface $logger = null
    ) {
        $this->dataManager = $dataManager;
        $this->filterManager = $filterManager;
        $this->cacheManager = $cacheManager;
        $this->logger = $logger ?: new NullLogger();

        if (\is_bool($alternativeFormats)) {
            @trigger_error('Passing a boolean as the 4th argument to '.__METHOD__.' is deprecated since 2.12 and will be removed in 3.0. Pass an array of alternative formats instead.', E_USER_DEPRECATED);
            $this->alternativeFormats = ['webp' => array_merge(['generate' => $alternativeFormats], $webpOptions)];
        } else {
            $this->alternativeFormats = $alternativeFormats;
        }
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
     * @param bool        $webpSupported
     * @param array       $alternativeFormatsSupported
     *
     * @return string
     */
    public function getUrlOfFilteredImage($path, $filter, $resolver = null, $webpSupported = false, array $alternativeFormatsSupported = [])
    {
        if (true === $webpSupported && !\in_array('webp', $alternativeFormatsSupported, true)) {
             @trigger_error('The $webpSupported argument is deprecated since 2.12 and will be removed in 3.0. Use the $alternativeFormatsSupported argument instead.', E_USER_DEPRECATED);
             $alternativeFormatsSupported[] = 'webp';
        }

        foreach ($this->buildFilterPathContainers($path) as $filterPathContainer) {
            $this->warmUpCacheFilterPathContainer($filterPathContainer, $filter, $resolver);
        }

        return $this->resolveFilterPathContainer(new FilterPathContainer($path), $filter, $resolver, $alternativeFormatsSupported);
    }

    /**
     * @param string      $path
     * @param string      $filter
     * @param string|null $resolver
     * @param bool        $webpSupported
     * @param array       $alternativeFormatsSupported
     *
     * @return string
     */
    public function getUrlOfFilteredImageWithRuntimeFilters(
        $path,
        $filter,
        array $runtimeFilters = [],
        $resolver = null,
        $webpSupported = false,
        array $alternativeFormatsSupported = []
    ) {
        if (false !== $webpSupported) {
            @trigger_error('The $webpSupported argument is deprecated since 2.12 and will be removed in 3.0. Use the $alternativeFormatsSupported argument instead.', E_USER_DEPRECATED);
            if (!\in_array('webp', $alternativeFormatsSupported, true)) {
                $alternativeFormatsSupported[] = 'webp';
            }
        }

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
            $alternativeFormatsSupported
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

        foreach ($this->alternativeFormats as $format => $formatOptions) {
            if (isset($formatOptions['generate']) && $formatOptions['generate']) {
                $cleanOptions = $formatOptions;
                unset($cleanOptions['generate']);
                $filterPathContainers[] = $basePathContainer->createAlternative($format, $cleanOptions);
            }
        }

        return $filterPathContainers;
    }

    private function resolveFilterPathContainer(
        FilterPathContainer $filterPathContainer,
        string $filter,
        ?string $resolver = null,
        array $clientSupportedFormats = []
    ): string {
        foreach ($this->alternativeFormats as $format => $formatOptions) {
            if (isset($formatOptions['generate']) && $formatOptions['generate'] && \in_array($format, $clientSupportedFormats, true)) {
                $cleanOptions = $formatOptions;
                unset($cleanOptions['generate']);
                return $this->cacheManager->resolve($filterPathContainer->createAlternative($format, $cleanOptions)->getTarget(), $filter, $resolver);
            }
        }

        return $this->cacheManager->resolve($filterPathContainer->getTarget(), $filter, $resolver);
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
            $this->logger->debug(\sprintf(
                'Could not locate filter "%s" for path "%s". Message was "%s"',
                $filter,
                $filterPathContainer->getSource(),
                $e->getMessage()
            ));

            throw $e;
        }
    }
}
