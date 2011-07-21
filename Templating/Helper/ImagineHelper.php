<?php

namespace Avalanche\Bundle\ImagineBundle\Templating\Helper;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Templating\Helper\Helper;

class ImagineHelper extends Helper
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
     * Gets cache path of an image to be filtered
     *
     * @param string $path
     * @param string $filter
     *
     * @return string
     */
    public function filter($path, $filter)
    {
        return $this->container->get('imagine.cache.path.resolver')->getBrowserPath($path, $filter);
    }

    /**
     * (non-PHPdoc)
     * @see Symfony\Component\Templating\Helper.HelperInterface::getName()
     */
    public function getName()
    {
        return 'imagine';
    }
}
