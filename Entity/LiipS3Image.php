<?php

namespace Liip\ImagineBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Liip\ImagineBundle\Entity\LiipS3Image
 */
class LiipS3Image
{
    /**
     * @var integer $id
     */
    private $id;
    
    /**
     * @var text $data
     */
    private $data;
    
    /**
     * @var string $url
     */
    private $url;
    
    /**
     * @var string $mimetype
     */
    private $mimetype;
    
    /**
     * @var string $filename
     */
    private $filename;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set data
     *
     * @param text $data
     * @return Image
     */
    public function setData($data)
    {
        $this->data = base64_encode($data);
        return $this;
    }

    /**
     * Get data
     *
     * @return text 
     */
    public function getData()
    {
        return base64_decode($this->data);
    }

    /**
     * Set url
     *
     * @param string $url
     * @return LiipS3Image
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set mimetype
     *
     * @param string $mimetype
     * @return LiipS3Image
     */
    public function setMimetype($mimetype)
    {
        $this->mimetype = $mimetype;
        return $this;
    }

    /**
     * Get mimetype
     *
     * @return string 
     */
    public function getMimetype()
    {
        return $this->mimetype;
    }

    /**
     * Set filename
     *
     * @param string $filename
     * @return LiipS3Image
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * Get filename
     *
     * @return string 
     */
    public function getFilename()
    {
        return $this->filename;
    }
}