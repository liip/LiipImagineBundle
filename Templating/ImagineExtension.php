<?php

namespace Avalanche\Bundle\ImagineBundle\Templating;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Util\Filesystem;

class ImagineExtension extends \Twig_Extension
{
    /**
     * @var Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * Constructs by setting $container
     *
     * @param Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * (non-PHPdoc)
     * @see Twig_Extension::getFilters()
     */
    public function getFilters()
    {
        return array(
            'apply_filter' => new \Twig_Filter_Method($this, 'applyFilter'),
        );
    }

    /**
     * Gets cache path of an image to be filtered
     *
     * @param string $path
     * @param string $filter
     *
     * @return string
     */
    public function applyFilter($path, $filter)
    {
        return $this->container->get('imagine.cache.path.resolver')->getBrowserPath($path, $filter);
    }

    /**
     * (non-PHPdoc)
     * @see Twig_ExtensionInterface::getName()
     */
    public function getName()
    {
        return 'imagine';
    }
}
