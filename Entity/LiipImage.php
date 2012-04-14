<?php

namespace Liip\ImagineBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Liip\ImagineBundle\Entity\LiipImage
 */
class LiipImage
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
     * @var string $uniqueIdentifier
     */
    private $uniqueIdentifier;
    
    /**
     * @var string $fileName
     */
    private $fileName;
        
    /**
     * @var string $mimetype
     */
    private $mimetype;
    
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
     * Set fileName
     *
     * @param string $fileName
     * @return Image
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
        return $this;
    }

    /**
     * Get fileName
     *
     * @return string 
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set uniqueIdentifier
     *
     * @param string $uniqueIdentifier
     * @return Image
     */
    public function setUniqueIdentifier($uniqueIdentifier)
    {
        $this->uniqueIdentifier = $uniqueIdentifier;
        return $this;
    }
    
    /**
     * Get uniqueIdentifier
     *
     * @return string 
     */
    public function getUniqueIdentifier()
    {
        return $this->uniqueIdentifier;
    }

    
    /**
     * Set mimetype
     *
     * @param string $mimetype
     * @return Image
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
     * Update the unique identifier
     */
    public function updateUniqueIdentifier()
    {
    	$this->setUniqueIdentifier(md5(time() . $this->fileName) . '.' . $this->_calculateExtention($this->mimetype));
    }
    
    /**
     * Calculate the image's mimetype.
     */
    private function _calculateExtention()
    {
	    $mimeTypes = array(
	    		'image/jpeg' => 'jpeg',
	    		'image/jpeg' => 'jpg',
	    		'image/gif' => 'gif',
	    		'image/png' => 'png',
	    		'image/vnd.wap.wbmp' => 'wbmp',
	    		'image/xbm' => 'xmb',
	    );
	    
	    return $mimeTypes[$this->mimetype];
    }
}
