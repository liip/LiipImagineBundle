<?php

namespace Liip\ImagineBundle\Routing;

use Symfony\Component\Routing\Route;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Config\Loader\Loader;

class ImagineLoader extends Loader
{
    private $controllerAction;
    private $cachePrefix;
    private $filters;

    public function __construct($controllerAction, $cachePrefix, array $filters = array())
    {
        $this->controllerAction = $controllerAction;
        $this->cachePrefix = $cachePrefix;
        $this->filters = $filters;
    }

    public function supports($resource, $type = null)
    {
        return $type === 'imagine';
    }

    public function load($resource, $type = null)
    {
        $requirements = array('_method' => 'GET', 'filter' => '[A-z0-9_\-]*', 'path' => '.+');
        $routes       = new RouteCollection();

        if (count($this->filters) > 0) {
            foreach ($this->filters as $filter => $config) {
                $pattern = $this->cachePrefix;
                if (isset($config['path'])) {
                    if ('/' !== $config['path']) {
                        $pattern .= '/'.trim($config['path'], '/');
                    }
                } elseif ('' !== $filter) {
                    $pattern .= '/'.$filter;
                }

                $defaults = array(
                    '_controller' => empty($config['controller_action']) ? $this->controllerAction : $config['controller_action'],
                    'filter' => $filter,
                );

                $routes->add('_imagine_'.$filter, new Route(
                    $pattern.'/{path}',
                    $defaults,
                    $requirements
                ));
            }
        }

        return $routes;
    }
}
