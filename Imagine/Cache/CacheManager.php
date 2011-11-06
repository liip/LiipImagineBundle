<?php

namespace Liip\ImagineBundle\Imagine\Cache;

use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface,
    Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

class CacheManager
{
    /**
     * @var FilterConfiguration
     */
    private $filterConfig;

    /**
     * @var string
     */
    private $defaultResolver;

    /**
     * @var array
     */
    private $resolvers = array();

    /**
     * @param FilterConfiguration $filterConfig
     * @param string $defaultResolver
     */
    public function __construct(FilterConfiguration $filterConfig, $defaultResolver = null)
    {
        $this->filterConfig = $filterConfig;
        $this->defaultResolver = $defaultResolver;
    }

    /**
     * @param $filter
     * @param ResolverInterface $resolver
     * 
     * @return void
     */
    public function addResolver($filter, ResolverInterface $resolver)
    {
        $this->resolvers[$filter] = $resolver;
    }

    /**
     * @param $filter
     * @return ResolverInterface
     */
    public function getResolver($filter)
    {
        $config = $this->filterConfig->get($filter);

        $resolverName = empty($config['cache_resolver'])
            ? $this->defaultResolver : $config['cache_resolver'];

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
     * Resolves filtered path for rendering in the browser
     *
     * @param Request $request
     * @param string $path
     * @param string $filter
     *
     * @return string
     */
    public function resolve(Request $request, $targetPath, $filter)
    {
        try {
            return $this->getResolver($filter)->resolve($request, $targetPath, $filter);
        } catch (\InvalidArgumentException $e) {
            return false;
        }
    }

    /**
     * @throws \RuntimeException
     * @param Response $response
     * @param string $targetPath
     *
     * @return Response
     */
    public function store(Response $response, $targetPath, $filter)
    {
        return $this->getResolver($filter)->store($response, $targetPath);
    }
}
