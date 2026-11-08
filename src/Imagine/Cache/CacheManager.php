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

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Events\CacheResolveEvent;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Liip\ImagineBundle\ImagineEvents;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CacheManager
{
    protected FilterConfiguration $filterConfig;

    protected RouterInterface $router;

    /**
     * @var ResolverInterface[]
     */
    protected array $resolvers = [];

    protected SignerInterface $signer;

    protected EventDispatcherInterface $dispatcher;

    protected string $defaultResolver;

    private bool $webpGenerate;

    /**
     * Constructs the cache manager to handle Resolvers based on the provided FilterConfiguration.
     */
    public function __construct(
        FilterConfiguration $filterConfig,
        RouterInterface $router,
        SignerInterface $signer,
        EventDispatcherInterface $dispatcher,
        ?string $defaultResolver = null,
        bool $webpGenerate = false,
    ) {
        $this->filterConfig = $filterConfig;
        $this->router = $router;
        $this->signer = $signer;
        $this->dispatcher = $dispatcher;
        $this->defaultResolver = $defaultResolver ?: 'default';
        $this->webpGenerate = $webpGenerate;
    }

    /**
     * Adds a resolver to handle cached images for the given filter.
     */
    public function addResolver(string $filter, ResolverInterface $resolver): void
    {
        $this->resolvers[$filter] = $resolver;

        if ($resolver instanceof CacheManagerAwareInterface) {
            $resolver->setCacheManager($this);
        }
    }

    /**
     * Gets filtered path for rendering in the browser.
     * It could be the cached one or an url of filter action.
     *
     * @param string $path The path where the resolved file is expected
     */
    public function getBrowserPath(string $path, string $filter, array $runtimeConfig = [], ?string $resolver = null, int $referenceType = UrlGeneratorInterface::ABSOLUTE_URL): string
    {
        if (!empty($runtimeConfig)) {
            $rcPath = $this->getRuntimePath($path, $runtimeConfig);

            return !$this->webpGenerate && $this->isStored($rcPath, $filter, $resolver) ?
                $this->resolve($rcPath, $filter, $resolver) :
                $this->generateUrl($path, $filter, $runtimeConfig, $resolver, $referenceType);
        }

        return !$this->webpGenerate && $this->isStored($path, $filter, $resolver) ?
            $this->resolve($path, $filter, $resolver) :
            $this->generateUrl($path, $filter, [], $resolver, $referenceType);
    }

    /**
     * Get path to runtime config image.
     */
    public function getRuntimePath(string $path, array $runtimeConfig): string
    {
        $path = ltrim($path, '/');

        return 'rc/'.$this->signer->sign($path, $runtimeConfig).'/'.$path;
    }

    /**
     * Returns a web accessible URL.
     *
     * @param string $path          The path where the resolved file is expected
     * @param string $filter        The name of the imagine filter in effect
     * @param int    $referenceType The type of reference to be generated (one of the UrlGenerator constants)
     */
    public function generateUrl(string $path, string $filter, array $runtimeConfig = [], ?string $resolver = null, int $referenceType = UrlGeneratorInterface::ABSOLUTE_URL): string
    {
        $params = [
            'path' => ltrim($path, '/'),
            'filter' => $filter,
        ];

        if ($resolver) {
            $params['resolver'] = $resolver;
        }

        if (empty($runtimeConfig)) {
            $filterUrl = $this->router->generate('liip_imagine_filter', $params, $referenceType);
        } else {
            $params['filters'] = $runtimeConfig;
            $params['hash'] = $this->signer->sign($path, $runtimeConfig);

            $filterUrl = $this->router->generate('liip_imagine_filter_runtime', $params, $referenceType);
        }

        return $filterUrl;
    }

    /**
     * Checks whether the path is already stored within the respective Resolver.
     */
    public function isStored(string $path, string $filter, ?string $resolver = null): bool
    {
        return $this->getResolver($filter, $resolver)->isStored($path, $filter);
    }

    /**
     * Resolves filtered path for rendering in the browser.
     *
     * @throws NotFoundHttpException if the path can not be resolved
     *
     * @return string The url of resolved image
     */
    public function resolve(string $path, string $filter, ?string $resolver = null): string
    {
        if (false !== mb_strpos($path, '/../') || 0 === mb_strpos($path, '../')) {
            throw new NotFoundHttpException(\sprintf("Source image was searched with '%s' outside of the defined root path", $path));
        }

        $preEvent = new CacheResolveEvent($path, $filter);
        $this->dispatcher->dispatch($preEvent, ImagineEvents::PRE_RESOLVE);

        $url = $this->getResolver($preEvent->getFilter(), $resolver)->resolve($preEvent->getPath(), $preEvent->getFilter());

        $postEvent = new CacheResolveEvent($preEvent->getPath(), $preEvent->getFilter(), $url);
        $this->dispatcher->dispatch($postEvent, ImagineEvents::POST_RESOLVE);

        return $postEvent->getUrl();
    }

    /**
     * @see ResolverInterface::store
     */
    public function store(BinaryInterface $binary, string $path, string $filter, ?string $resolver = null): void
    {
        $this->getResolver($filter, $resolver)->store($binary, $path, $filter);
    }

    /**
     * @param string|string[]|null $paths
     * @param string|string[]|null $filters
     */
    public function remove($paths = null, $filters = null): void
    {
        if (null === $filters) {
            $filters = array_keys($this->filterConfig->all());
        } elseif (!\is_array($filters)) {
            $filters = [$filters];
        }
        if (!\is_array($paths)) {
            $paths = [$paths];
        }

        $paths = array_filter($paths);
        $filters = array_filter($filters);

        $mapping = new \SplObjectStorage();
        foreach ($filters as $filter) {
            $resolver = $this->getResolver($filter, null);

            $list = $mapping[$resolver] ?? [];

            $list[] = $filter;

            $mapping[$resolver] = $list;
        }

        foreach ($mapping as $resolver) {
            $resolver->remove($paths, $mapping[$resolver]);
        }
    }

    /**
     * Gets a resolver for the given filter.
     *
     * In case there is no specific resolver, but a default resolver has been configured, the default will be returned.
     *
     * @throws \OutOfBoundsException If neither a specific nor a default resolver is available
     */
    protected function getResolver(string $filter, ?string $resolverName): ResolverInterface
    {
        if (!$resolverName) {
            $config = $this->filterConfig->get($filter);
            $resolverName = $config['cache'] ?? $this->defaultResolver;
        }

        if (!\array_key_exists($resolverName, $this->resolvers)) {
            throw new \OutOfBoundsException(\sprintf('Could not find resolver "%s" for "%s" filter type', $resolverName, $filter));
        }

        return $this->resolvers[$resolverName];
    }
}
