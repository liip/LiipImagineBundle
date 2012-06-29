<?php

namespace Liip\ImagineBundle\Imagine\Cache;

use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface,
    Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\Routing\RouterInterface,
    Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CacheManager
{
    /**
     * @var FilterConfiguration
     */
    private $filterConfig;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var string
     */
    private $webRoot;

    /**
     * @var string
     */
    private $defaultResolver;

    /**
     * @var array
     */
    private $resolvers = array();

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
     * @return string
     */
    public function getWebRoot()
    {
        return $this->webRoot;
    }

    /**
     * @param string $filter
     * @return ResolverInterface
     */
    private function getResolver($filter)
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
     * Gets filtered path for rendering in the browser
     *
     * @param string $path
     * @param string $filter
     * @param boolean $absolute
     *
     * @return string
     */
    public function getBrowserPath($targetPath, $filter, $absolute = false)
    {
        return $this->getResolver($filter)->getBrowserPath($targetPath, $filter, $absolute);
    }

    /**
     * Returns a web accessible URL.
     *
     * @param string $targetPath The target path provided by the resolve method.
     * @param string $filter The name of the imagine filter in effect.
     * @param bool $absolute Whether to generate an absolute URL or a relative path is accepted.
     *                       In case the resolver does not support relative paths, it may ignore this flag.
     *
     * @return string
     */
    public function generateUrl($targetPath, $filter, $absolute = false)
    {
        $config = $this->filterConfig->get($filter);
        if (isset($config['format'])) {
            $pathinfo = pathinfo($targetPath);
            if ($pathinfo['extension'] !== $config['format']) {
                $targetPath = $pathinfo['dirname'].'/'.$pathinfo['filename'].'.'.$config['format'];
            }
        }

        $params = array('path' => ltrim($targetPath, '/'));

        return str_replace(
            urlencode($params['path']),
            urldecode($params['path']),
            $this->router->generate('_imagine_'.$filter, $params, $absolute)
        );
    }

    /**
     * Resolves filtered path for rendering in the browser
     *
     * @param Request $request
     * @param string $path
     * @param string $filter
     *
     * @return string target path
     */
    public function resolve(Request $request, $path, $filter)
    {
        if (false !== strpos($path, '/../') || 0 === strpos($path, '../')) {
            throw new NotFoundHttpException(sprintf("Source image was searched with '%s' out side of the defined root path", $path));
        }

        try {
            $resolver = $this->getResolver($filter);
        } catch (\InvalidArgumentException $e) {
            return false;
        }

        return $resolver->resolve($request, $path, $filter);
    }

    /**
     * Store successful responses with the cache resolver
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
     * @param string $targetPath
     * @param string $filter
     *
     * @return bool
     */
    public function remove($targetPath, $filter)
    {
        return $this->getResolver($filter)->remove($targetPath, $filter);
    }

    public function clearResolversCache($cachePrefix)
    {
        foreach ($this->resolvers as $resolver) {
            $resolver->clear($cachePrefix);
        }
    }
}
