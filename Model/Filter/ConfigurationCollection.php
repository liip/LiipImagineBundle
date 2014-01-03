<?php

namespace Liip\ImagineBundle\Model\Filter;

class ConfigurationCollection
{
    /**
     * @var Configuration[]
     */
    protected $configurations = array();

    public function addConfiguration(Configuration $configuration)
    {
        $this->configurations[$configuration->getId()] = $configuration;
    }

    public function getConfiguration($id)
    {
        return $this->configurations[$id];
    }
}
