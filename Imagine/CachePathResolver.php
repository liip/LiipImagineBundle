<?php

namespace Avalanche\Bundle\ImagineBundle\Imagine;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class CachePathResolver
{
    /**
     * @var string
     */
    private $webRoot;

    /**
     * @var Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * Constructs cache path resolver with a given web root and cache prefix
     *
     * @param string                                    $webRoot
     * @param Symfony\Component\Routing\RouterInterface $router
     */
    public function __construct($webRoot, RouterInterface $router)
    {
        $this->webRoot = $webRoot;
        $this->router  = $router;
    }

    /**
     * Gets filtered path for rendering in the browser
     *
     * @param string $path
     * @param string $filter
     */
    public function getBrowserPath($path, $filter)
    {
        // identify if current path is not under specified web root and return
        // unmodified path in that case
        $realPath = realpath($this->webRoot.$path);

        if (!0 === strpos($realPath, $this->webRoot)) {
            return $path;
        }

        $path = str_replace(
            urlencode(ltrim($path, '/')),
            urldecode(ltrim($path, '/')),
            $this->router->generate('_imagine_'.$filter, array(
                'path' => ltrim($path, '/')
            ))
        );

        return $path;
    }
}
