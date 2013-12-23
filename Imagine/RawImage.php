<?php
namespace Liip\ImagineBundle\Imagine;

use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;

class RawImage
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
     */
    public function __construct($content, $mimeType)
    {
        $this->content = $content;
        $this->mimeType = $mimeType;
        $this->format = ExtensionGuesser::getInstance()->guess($mimeType);
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

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getContent();
    }
}
