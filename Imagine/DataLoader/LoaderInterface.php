<?php

namespace Avalanche\Bundle\ImagineBundle\Imagine\DataLoader;

interface LoaderInterface
{
    /**
     * @param string $path
     *
     * @return array
     */
    function find($path);
}
