<?php

namespace Liip\ImagineBundle\Imagine\Cache;

use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

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
     * @var string
     */
    protected $webRoot;

    /**
     * @var string
     */
    protected $defaultResolver;

    /**
     * @var ResolverInterface[]
     */
    protected $resolvers = array();

    /**
     * Constructs the cache manager to handle Resolvers based on the provided FilterConfiguration.
     *
     * @param FilterConfiguration $filterConfig
     * @param RouterInterface $router
     * @param string $webRoot
     * @param string $defaultResolver
     */
    public function __construct(FilterConfiguration $filterConfig, RouterInterface $router, $webRoot, $defaultResolver = null)
    {
        $this->filterConfig = $filterConfig;
        $this->router = $router;
        $this->webRoot = realpath($webRoot);
        $this->defaultResolver = $defaultResolver;
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
     * Returns the configured web root path.
     *
     * @return string
     */
    public function getWebRoot()
    {
        return $this->webRoot;
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
     * @throws \InvalidArgumentException If neither a specific nor a default resolver is available.
     */
    protected function getResolver($filter)
    {
        $config = $this->filterConfig->get($filter);

        $resolverName = empty($config['cache'])
            ? $this->defaultResolver : $config['cache'];

        if (!isset($this->resolvers[$resolverName])) {
            throw new \InvalidArgumentException(sprintf(
                'Could not find resolver for "%s" filter type', $filter
            ));
        }

        return $this->resolvers[$resolverName];
    }

    /**
     * Gets filtered path for rendering in the browser.
     *
     * @see ResolverInterface::getBrowserPath
     *
     * @param string $path The path where the resolved file is expected.
     * @param string $filter
     * @param boolean $absolute
     *
     * @return string
     */
    public function getBrowserPath($path, $filter, $absolute = false)
    {
        return $this->getResolver($filter)->getBrowserPath($path, $filter, $absolute);
    }

    /**
     * Returns a web accessible URL.
     *
     * @param string $path The path where the resolved file is expected.
     * @param string $filter The name of the imagine filter in effect.
     * @param bool $absolute Whether to generate an absolute URL or a relative path is accepted.
     *                       In case the resolver does not support relative paths, it may ignore this flag.
     *
     * @return string
     */
    public function generateUrl($path, $filter, $absolute = false)
    {
        $config = $this->filterConfig->get($filter);

        if (isset($config['format'])) {
            $pathinfo = pathinfo($path);

            // the extension should be forced and a directory is detected
            if ((!isset($pathinfo['extension']) || $pathinfo['extension'] !== $config['format'])
                && isset($pathinfo['dirname'])) {

                if ('\\' === $pathinfo['dirname']) {
                    $pathinfo['dirname'] = '';
                }

                $path = $pathinfo['dirname'].'/'.$pathinfo['filename'].'.'.$config['format'];
            }
        }

        $params = array('path' => ltrim($path, '/'));

        return str_replace(
            urlencode($params['path']),
            urldecode($params['path']),
            $this->router->generate('_imagine_'.$filter, $params, $absolute)
        );
    }

    /**
     * Resolves filtered path for rendering in the browser.
     *
     * @param Request $request
     * @param string $path
     * @param string $filter
     *
     * @return string|boolean|Response target path or false if filter has no
     *      resolver or a Response object from the resolver
     *
     * @throws NotFoundHttpException if the path can not be resolved
     */
    public function resolve(Request $request, $path, $filter)
    {
        if (false !== strpos($path, '/../') || 0 === strpos($path, '../')) {
            throw new NotFoundHttpException(sprintf("Source image was searched with '%s' outside of the defined root path", $path));
        }

        try {
            $resolver = $this->getResolver($filter);
        } catch (\InvalidArgumentException $e) {
            return false;
        }

        return $resolver->resolve($request, $path, $filter);
    }

    /**
     * Store successful responses with the cache resolver.
     *
     * @see ResolverInterface::store
     *
     * @param Response $response
     * @param string $targetPath
     * @param string $filter
     *
     * @return Response
     */
    public function store(Response $response, $targetPath, $filter)
    {
        if ($response->isSuccessful()) {
            $response = $this->getResolver($filter)->store($response, $targetPath, $filter);
        }

        return $response;
    }

    /**
     * Remove a cached image from the storage.
     *
     * @see ResolverInterface::remove
     *
     * @param string $targetPath
     * @param string $filter
     *
     * @return bool
     */
    public function remove($targetPath, $filter)
    {
        return $this->getResolver($filter)->remove($targetPath, $filter);
    }

    /**
     * Clear the cache of all resolvers.
     *
     * @see ResolverInterface::clear
     *
     * @param string $cachePrefix
     *
     * @return void
     */
    public function clearResolversCache($cachePrefix)
    {
        foreach ($this->resolvers as $resolver) {
            $resolver->clear($cachePrefix);
        }
    }
}
