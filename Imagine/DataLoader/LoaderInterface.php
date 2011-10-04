<?php

namespace Liip\ImagineBundle\Imagine\DataLoader;

interface LoaderInterface
{
    /**
     * @param string $path
     *
     * @return array
     */
    function find($path);
}
