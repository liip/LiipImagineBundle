<?php

namespace Liip\ImagineBundle\Routing;

use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class ImagineLoader extends Loader
{
    /**
     * @var string
     */
    private $filterControllerAction;

    /**
     * @var string
     */
    private $runtimeConfigControllerAction;

    /**
     * @var string
     */
    private $cachePrefix;

    /**
     * @var FilterConfiguration
     */
    private $filterConfig;

    /**
     * @param FilterConfiguration $filterConfig
     * @param string $filterControllerAction
     * @param string $cachePrefi
     */
    public function __construct(FilterConfiguration $filterConfig, $filterControllerAction, $runtimeConfigControllerAction, $cachePrefix)
    {
        $this->filterConfig = $filterConfig;
        $this->filterControllerAction = $filterControllerAction;
        $this->runtimeConfigControllerAction = $runtimeConfigControllerAction;
        $this->cachePrefix = $cachePrefix;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return $type === 'imagine';
    }

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        $routes  = new RouteCollection();
        $filters = $this->filterConfig->all();

        if (count($filters) > 0) {
            foreach ($filters as $filter => $config) {
                $routes->add('_imagine_rc_'.$filter, $this->getRoute($filter, $config, array(
                    '_controller' => empty($config['controller']['runtime_config_action']) ? $this->runtimeConfigControllerAction : $config['controller']['runtime_config_action'],
                    'filter' => $filter,
                ), 'rc/'));

                $routes->add('_imagine_'.$filter, $this->getRoute($filter, $config, array(
                    '_controller' => empty($config['controller']['filter_action']) ? $this->filterControllerAction : $config['controller']['filter_action'],
                    'filter' => $filter,
                )));
            }
        }

        return $routes;
    }

    /**
     * @param string $filter
     * @param array $config
     * @param array $defaults
     * @param $cacheSuffix
     * @return Route
     */
    protected function getRoute($filter, array $config, array $defaults, $cacheSuffix = null)
    {
        $requirements = array('_method' => 'GET', 'filter' => '[A-z0-9_\-]*', 'path' => '.+');
        $routeOptions = array();

        $pattern = trim($this->cachePrefix, '/');

        if (isset($config['path'])) {
            if ('/' !== $config['path']) {
                $pattern .= '/'.trim($config['path'], '/');
            }
        } elseif ('' !== $filter) {
            $pattern .= '/'.trim($filter, '/');

            if (null !== $cacheSuffix) {
                $pattern .= '/'.trim($cacheSuffix, '/');
            }
        }

        if (isset($config['route']['requirements'])) {
            $requirements = array_merge($requirements, $config['route']['requirements']);
        }

        if (isset($config['route']['defaults'])) {
            $defaults = array_merge($defaults, $config['route']['defaults']);
        }

        if (isset($config['route']['options'])) {
            $routeOptions = array_merge($routeOptions, $config['route']['options']);
        }

        return new Route(
            $pattern.'/{path}',
            $defaults,
            $requirements,
            $routeOptions
        );
    }
}
