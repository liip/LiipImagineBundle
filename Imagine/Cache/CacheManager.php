<?php

namespace Liip\ImagineBundle\Imagine\Cache;

use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Model\Filter\ConfigurationCollection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

class CacheManager
{
    /**
     * @var ConfigurationCollection
     */
    protected $configurations;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var string
     */
    protected $webRoot;

    /**
     * Constructs the cache manager to handle Resolvers based on the provided FilterConfiguration.
     *
     * @param ConfigurationCollection $configurations
     * @param RouterInterface         $router
     * @param string                  $webRoot
     */
    public function __construct(ConfigurationCollection $configurations, RouterInterface $router, $webRoot)
    {
        $this->configurations = $configurations;
        $this->router = $router;
        $this->webRoot = realpath($webRoot);
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
     * @param string $filter
     *
     * @return ResolverInterface
     */
    protected function getResolver($filter)
    {
        return $this->configurations->getConfiguration($filter)->getResolver();
    }

    /**
     * Gets filtered path for rendering in the browser.
     * It could be the cached one or an url of filter action.
     *
     * @param string  $path     The path where the resolved file is expected.
     * @param string  $filter
     * @param boolean $absolute
     *
     * @return string
     */
    public function getBrowserPath($path, $filter, $absolute = false)
    {
        return
            $this->resolve($path, $filter) ?:
            $this->generateUrl($path, $filter, $absolute)
        ;
    }

    /**
     * Returns a web accessible URL.
     *
     * @param string $path     The path where the resolved file is expected.
     * @param string $filter   The name of the imagine filter in effect.
     * @param bool   $absolute Whether to generate an absolute URL or a relative path is accepted.
     *                       In case the resolver does not support relative paths, it may ignore this flag.
     *
     * @return string
     */
    public function generateUrl($path, $filter, $absolute = false)
    {
        $config = $this->configurations->get($filter);

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
     * Checks whether the path is already stored within the respective Resolver.
     *
     * @param string $path
     * @param string $filter
     *
     * @return bool
     */
    public function isStored($path, $filter)
    {
        return $this->getResolver($filter)->isStored($path, $filter);
    }

    /**
     * Resolves filtered path for rendering in the browser.
     *
     * @param string $path
     * @param string $filter
     *
     * @return Response|boolean The response of the respective Resolver or false.
     *
     * @throws NotFoundHttpException if the path can not be resolved
     */
    public function resolve($path, $filter)
    {
        if (false !== strpos($path, '/../') || 0 === strpos($path, '../')) {
            throw new NotFoundHttpException(sprintf("Source image was searched with '%s' outside of the defined root path", $path));
        }

        try {
            $resolver = $this->getResolver($filter);
        } catch (\InvalidArgumentException $e) {
            return false;
        }

        return $resolver->resolve($path, $filter);
    }

    /**
     * Store successful responses with the cache resolver.
     *
     * @see ResolverInterface::store
     *
     * @param Response $response
     * @param string   $path
     * @param string   $filter
     *
     * @return Response
     */
    public function store(Response $response, $path, $filter)
    {
        if ($response->isSuccessful()) {
            $response = $this->getResolver($filter)->store($response, $path, $filter);
        }

        return $response;
    }

    /**
     * Remove a cached image from the storage.
     *
     * @see ResolverInterface::remove
     *
     * @param string $path
     * @param string $filter
     *
     * @return bool
     */
    public function remove($path, $filter)
    {
        return $this->getResolver($filter)->remove($path, $filter);
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
