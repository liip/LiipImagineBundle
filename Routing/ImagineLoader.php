<?php

namespace Liip\ImagineBundle\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class ImagineLoader extends Loader
{
    private $controllerAction;
    private $cachePrefix;
    private $filtersets;

    public function __construct($controllerAction, $cachePrefix, array $filtersets = array())
    {
        $this->controllerAction = $controllerAction;
        $this->cachePrefix = $cachePrefix;
        $this->filtersets = $filtersets;
    }

    public function supports($resource, $type = null)
    {
        return $type === 'imagine';
    }

    public function load($resource, $type = null)
    {
        $requirements = array('_method' => 'GET', 'filterset' => '[A-z0-9_\-]*', 'path' => '.+');
        $routes       = new RouteCollection();

        if (count($this->filtersets) > 0) {
            foreach ($this->filtersets as $filterset => $config) {
                $pattern = $this->cachePrefix;
                if (isset($config['path'])) {
                    if ('/' !== $config['path']) {
                        $pattern .= '/'.trim($config['path'], '/');
                    }
                } elseif ('' !== $filterset) {
                    $pattern .= '/'.$filterset;
                }

                $defaults = array(
                    '_controller' => empty($config['controller_action']) ? $this->controllerAction : $config['controller_action'],
                    'filterset' => $filterset,
                );

                $routeRequirements = $requirements;
                $routeDefaults = $defaults;
                $routeOptions = array();

                if (isset($config['route']['requirements'])) {
                    $routeRequirements = array_merge($routeRequirements, $config['route']['requirements']);
                }
                if (isset($config['route']['defaults'])) {
                    $routeDefaults = array_merge($routeDefaults, $config['route']['defaults']);
                }
                if (isset($config['route']['options'])) {
                    $routeOptions = array_merge($routeOptions, $config['route']['options']);
                }

                $routes->add('_imagine_'.$filterset, new Route(
                    $pattern.'/{path}',
                    $routeDefaults,
                    $routeRequirements,
                    $routeOptions
                ));
            }
        }

        return $routes;
    }
}
