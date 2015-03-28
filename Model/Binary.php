<?php

namespace Liip\ImagineBundle\Model;

use Liip\ImagineBundle\Binary\BinaryInterface;

class Binary implements BinaryInterface
{
    /**
     * @var string
     */
    protected $content;

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
    public function __construct($content, $mimeType, $format = null)
    {
        $this->content = $content;
        $this->mimeType = $mimeType;
        $this->format = $format;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
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
