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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as ContractsEventDispatcherInterface;

class CacheManager
{
    /**
     * @var FilterConfiguration
     */
    protected $filterConfig;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var ResolverInterface[]
     */
    protected $resolvers = [];

    /**
     * @var SignerInterface
     */
    protected $signer;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var string
     */
    protected $defaultResolver;

    /**
     * @var bool
     */
    private $webpGenerate;

    /**
     * Constructs the cache manager to handle Resolvers based on the provided FilterConfiguration.
     *
     * @param string $defaultResolver
     * @param bool   $webpGenerate
     */
    public function __construct(
        FilterConfiguration $filterConfig,
        RouterInterface $router,
        SignerInterface $signer,
        EventDispatcherInterface $dispatcher,
        $defaultResolver = null,
        $webpGenerate = false
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
     *
     * @param string $filter
     */
    public function addResolver($filter, ResolverInterface $resolver)
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
     * @param string $path          The path where the resolved file is expected
     * @param string $filter
     * @param string $resolver
     * @param int    $referenceType
     *
     * @return string
     */
    public function getBrowserPath($path, $filter, array $runtimeConfig = [], $resolver = null, $referenceType = UrlGeneratorInterface::ABSOLUTE_URL)
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
     *
     * @param string $path
     *
     * @return string
     */
    public function getRuntimePath($path, array $runtimeConfig)
    {
        return 'rc/'.$this->signer->sign($path, $runtimeConfig).'/'.$path;
    }

    /**
     * Returns a web accessible URL.
     *
     * @param string $path          The path where the resolved file is expected
     * @param string $filter        The name of the imagine filter in effect
     * @param string $resolver
     * @param int    $referenceType The type of reference to be generated (one of the UrlGenerator constants)
     *
     * @return string
     */
    public function generateUrl($path, $filter, array $runtimeConfig = [], $resolver = null, $referenceType = UrlGeneratorInterface::ABSOLUTE_URL)
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
     *
     * @param string $path
     * @param string $filter
     * @param string $resolver
     *
     * @return bool
     */
    public function isStored($path, $filter, $resolver = null)
    {
        return $this->getResolver($filter, $resolver)->isStored($path, $filter);
    }

    /**
     * Resolves filtered path for rendering in the browser.
     *
     * @param string $path
     * @param string $filter
     * @param string $resolver
     *
     * @throws NotFoundHttpException if the path can not be resolved
     *
     * @return string The url of resolved image
     */
    public function resolve($path, $filter, $resolver = null)
    {
        if (false !== mb_strpos($path, '/../') || 0 === mb_strpos($path, '../')) {
            throw new NotFoundHttpException(sprintf("Source image was searched with '%s' outside of the defined root path", $path));
        }

        $preEvent = new CacheResolveEvent($path, $filter);
        $this->dispatchWithBC($preEvent, ImagineEvents::PRE_RESOLVE);

        $url = $this->getResolver($preEvent->getFilter(), $resolver)->resolve($preEvent->getPath(), $preEvent->getFilter());

        $postEvent = new CacheResolveEvent($preEvent->getPath(), $preEvent->getFilter(), $url);
        $this->dispatchWithBC($postEvent, ImagineEvents::POST_RESOLVE);

        return $postEvent->getUrl();
    }

    /**
     * @see ResolverInterface::store
     *
     * @param string $path
     * @param string $filter
     * @param string $resolver
     */
    public function store(BinaryInterface $binary, $path, $filter, $resolver = null)
    {
        $this->getResolver($filter, $resolver)->store($binary, $path, $filter);
    }

    /**
     * @param string|string[]|null $paths
     * @param string|string[]|null $filters
     */
    public function remove($paths = null, $filters = null)
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

            $list = isset($mapping[$resolver]) ? $mapping[$resolver] : [];

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
     * @param string $filter
     * @param string $resolver
     *
     * @throws \OutOfBoundsException If neither a specific nor a default resolver is available
     *
     * @return ResolverInterface
     */
    protected function getResolver($filter, $resolver)
    {
        // BC
        if (!$resolver) {
            $config = $this->filterConfig->get($filter);

            $resolverName = empty($config['cache']) ? $this->defaultResolver : $config['cache'];
        } else {
            $resolverName = $resolver;
        }

        if (!isset($this->resolvers[$resolverName])) {
            throw new \OutOfBoundsException(sprintf('Could not find resolver "%s" for "%s" filter type', $resolverName, $filter));
        }

        return $this->resolvers[$resolverName];
    }

    /**
     * BC Layer for Symfony < 4.3
     */
    private function dispatchWithBC(CacheResolveEvent $event, string $eventName): void
    {
        if ($this->dispatcher instanceof ContractsEventDispatcherInterface) {
            $this->dispatcher->dispatch($event, $eventName);
        } else {
            $this->dispatcher->dispatch($eventName, $event);
        }
    }
}
