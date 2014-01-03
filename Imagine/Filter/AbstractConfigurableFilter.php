<?php

namespace Liip\ImagineBundle\Imagine\Filter;

use Liip\ImagineBundle\Model\Filter\ConfigurableFilterInterface;
use Liip\ImagineBundle\Model\Filter\Options;

abstract class AbstractConfigurableFilter implements ConfigurableFilterInterface
{
    /**
     * @var Options
     */
    protected $options;

    public function configure(Options $options)
    {
        $this->options = $options;
    }
}
