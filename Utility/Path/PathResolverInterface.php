<?php

namespace Liip\ImagineBundle\Utility\Path;

interface PathResolverInterface
{
    /**
     * @param string $path
     * @param string $filter
     *
     * @return string
     */
    public function getFilePath($path, $filter);
    
    /**
     * @param string $path
     * @param string $filter
     *
     * @return string
     */
    public function getFileUrl($path, $filter);
    
    /**
     * @return string
     */
    public function getCacheRoot();
}
