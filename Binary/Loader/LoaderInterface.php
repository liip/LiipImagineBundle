<?php

namespace Liip\ImagineBundle\Binary\Loader;

interface LoaderInterface
{
    /**
     * Retrieve the Image represented by the given path.
     *
     * The path may be a file path on a filesystem, or any unique identifier among the storage engine implemented by this Loader.
     *
     * @param mixed $path
     *
     * @return \Liip\ImagineBundle\Binary\BinaryInterface|string An image binary content
     */
    public function find($path);
}
