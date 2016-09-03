<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Model;

use Liip\ImagineBundle\Binary\FileBinaryInterface;

class FileBinary implements FileBinaryInterface
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $mimeType;

    /**
     * @var string
     */
    protected $format;

    /**
     * @param string $content
     * @param string $mimeType
     * @param string $format
     */
    public function __construct($path, $mimeType, $format = null)
    {
        $this->path = $path;
        $this->mimeType = $mimeType;
        $this->format = $format;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return file_get_contents($this->path);
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }
}
