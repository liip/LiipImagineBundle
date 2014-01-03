<?php

namespace Liip\ImagineBundle\Model\Filter;

use Imagine\Filter\FilterInterface as FilterInterface;

interface ConfigurableFilterInterface extends FilterInterface
{
    /**
     * Configures the Filter with new options to be used in further apply calls.
     *
     * @param Options $options
     *
     * @return void
     */
    public function configure(Options $options);
}
