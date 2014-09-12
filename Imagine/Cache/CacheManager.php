<?php

namespace Liip\ImagineBundle\Imagine\Cache;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\Event;
use Liip\ImagineBundle\ImagineEvents;
use Liip\ImagineBundle\Events\CacheResolveEvent;

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
    protected $resolvers = array();

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
     * Constructs the cache manager to handle Resolvers based on the provided FilterConfiguration.
     *
     * @param FilterConfiguration $filterConfig
     * @param RouterInterface $router
     * @param SignerInterface $signer
     * @param EventDispatcherInterface $dispatcher
     * @param string $defaultResolver
     */
    public function __construct(
        FilterConfiguration $filterConfig,
        RouterInterface $router,
        SignerInterface $signer,
        EventDispatcherInterface $dispatcher,
        $defaultResolver = null
    ) {
        $this->filterConfig = $filterConfig;
        $this->router = $router;
        $this->signer = $signer;
        $this->dispatcher = $dispatcher;
        $this->defaultResolver = $defaultResolver ?: 'default';
    }

    /**
     * Adds a resolver to handle cached images for the given filter.
     *
     * @param string $filter
     * @param ResolverInterface $resolver
     *
     * @return void
     */
    public function addResolver($filter, ResolverInterface $resolver)
    {
        $this->resolvers[$filter] = $resolver;

        if ($resolver instanceof CacheManagerAwareInterface) {
            $resolver->setCacheManager($this);
        }
    }

    /**
     * Gets a resolver for the given filter.
     *
     * In case there is no specific resolver, but a default resolver has been configured, the default will be returned.
     *
     * @param string $filter
     *
     * @return ResolverInterface
     *
     * @throws \OutOfBoundsException If neither a specific nor a default resolver is available.
     */
    protected function getResolver($filter)
    {
        $config = $this->filterConfig->get($filter);

        $resolverName = empty($config['cache']) ? $this->defaultResolver : $config['cache'];

        if (!isset($this->resolvers[$resolverName])) {
            throw new \OutOfBoundsException(sprintf(
                'Could not find resolver "%s" for "%s" filter type',
                $resolverName,
                $filter
            ));
        }

        return $this->resolvers[$resolverName];
    }

    /**
     * Gets filtered path for rendering in the browser.
     * It could be the cached one or an url of filter action.
     *
     * @param string $path The path where the resolved file is expected.
     * @param string $filter
     * @param array $runtimeConfig
     *
     * @return string
     */
    public function getBrowserPath($path, $filter, array $runtimeConfig = array())
    {
        return $this->isStored($path, $filter, $runtimeConfig) ?
            $this->resolve($path, $filter, $runtimeConfig) :
            $this->generateUrl($path, $filter, $runtimeConfig)
        ;
    }

    /**
     * Returns a web accessible URL.
     *
     * @param string $path The path where the resolved file is expected.
     * @param string $filter The name of the imagine filter in effect.
     * @param array $runtimeConfig
     *
     * @return string
     */
    public function generateUrl($path, $filter, array $runtimeConfig = array())
    {
        $params = array(
            'path' => ltrim($path, '/'),
            'filter' => $filter
        );

        if (empty($runtimeConfig)) {
            $filterUrl = $this->router->generate('liip_imagine_filter', $params, true);
        } else {
            $params['filters'] = $runtimeConfig;
            $params['hash'] = $this->signer->sign($path, $runtimeConfig);

            $filterUrl = $this->router->generate('liip_imagine_filter_runtime', $params, true);
        }

        return $filterUrl;
    }

    /**
     * Checks whether the path is already stored within the respective Resolver.
     *
     * @param string $path
     * @param string $filter
     * @param array
     *
     * @return bool
     */
    public function isStored($path, $filter, array $runtimeConfig = array())
    {
        return $this->getResolver($filter)->isStored($path, $filter, $this->getRuntimeConfigHash($path, $runtimeConfig));
    }

    /**
     * Resolves filtered path for rendering in the browser.
     *
     * @param string $path
     * @param string $filter
     * @param array $runtimeConfig
     *
     * @return string The url of resolved image.
     *
     * @throws NotFoundHttpException if the path can not be resolved
     */
    public function resolve($path, $filter, array $runtimeConfig = array())
    {
        if (false !== strpos($path, '/../') || 0 === strpos($path, '../')) {
            throw new NotFoundHttpException(sprintf("Source image was searched with '%s' outside of the defined root path", $path));
        }

        $preEvent = new CacheResolveEvent($path, $filter, $runtimeConfig);
        $this->dispatcher->dispatch(ImagineEvents::PRE_RESOLVE, $preEvent);

        $url = $this->getResolver($preEvent->getFilter())->resolve($preEvent->getPath(), $preEvent->getFilter(), $this->getRuntimeConfigHash($preEvent->getPath(), $preEvent->getRuntimeConfig()));

        $postEvent = new CacheResolveEvent($preEvent->getPath(), $preEvent->getFilter(), $preEvent->getRuntimeConfig(), $url);
        $this->dispatcher->dispatch(ImagineEvents::POST_RESOLVE, $postEvent);

        return $postEvent->getUrl();
    }

    /**
     * @see ResolverInterface::store
     *
     * @param BinaryInterface $binary
     * @param string          $path
     * @param string          $filter
     * @param array           $runtimeConfig
     */
    public function store(BinaryInterface $binary, $path, $filter, array $runtimeConfig = array())
    {
        $this->getResolver($filter)->store($binary, $path, $filter, $this->getRuntimeConfigHash($path, $runtimeConfig));
    }

    /**
     * @param string|string[]|null $paths
     * @param string|string[]|null $filters
     * @param array                $runtimeConfig
     *
     * @return void
     */
    public function remove($paths = null, $filters = null, array $runtimeConfig = array())
    {
        if (null === $filters) {
            $filters = array_keys($this->filterConfig->all());
        }
        if (!is_array($filters)) {
            $filters = array($filters);
        }
        if (!is_array($paths)) {
            $paths = array($paths);
        }

        $paths = array_filter($paths);
        $filters = array_filter($filters);

        $mapping = new \SplObjectStorage();
        foreach ($filters as $filter) {
            $resolver = $this->getResolver($filter);

            $list = isset($mapping[$resolver]) ? $mapping[$resolver] : array();

            $list[] = $filter;

            $mapping[$resolver] = $list;
        }

        foreach ($mapping as $resolver) {
            if (empty($runtimeConfig)) {
                $resolver->remove($paths, $mapping[$resolver]);
            } else {
                foreach ($paths as $path) {
                    $resolver->remove($paths, $mapping[$resolver], $this->getRuntimeConfigHash($path, $runtimeConfig));
                }
            }
        }
    }

    protected function getRuntimeConfigHash($path, array $runtimeConfig = array())
    {
        return empty($runtimeConfig) ? null : $this->signer->sign($path, $runtimeConfig);
    }
}
