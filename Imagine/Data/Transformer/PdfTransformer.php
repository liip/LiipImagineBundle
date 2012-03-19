<?php

namespace Liip\ImagineBundle\Imagine\Data\Transformer;

class PdfTransformer
{
    /**
     *
     * @var \Imagick
     */
    private $imagick;
    
    public function __construct(\Imagick $imagick)
    {
        $this->imagick = $imagick;
    }
    
    public function applyTransform($absolutePath)
    {
        $info = pathinfo($absolutePath);
        if (isset($info['extension']) && strpos(strtolower($info['extension']), 'pdf') !== false) {
            //If it doesn't exists, extract the first page of the PDF
            if (!file_exists("$absolutePath.png")) {
                $this->imagick->readImage($absolutePath.'[0]');
                $this->imagick->setImageFormat('png');
                $this->imagick->writeImage("$absolutePath.png");
                $this->imagick->clear();
            }
            $absolutePath .= '.png';
        }
        return $absolutePath;
    }
}