<?php

namespace Liip\ImagineBundle\Imagine\Data\Transformer;

class PdfTransformer
{
    public function applyTransform($absolutePath)
    {
        $info = pathinfo($absolutePath);
        if (isset ($info['extension']) && strpos(strtolower($info['extension']), 'pdf') !== false) {
            //Check if Imagick extension is loaded
            if(!extension_loaded('Imagick'))
                throw new \ErrorException ("PHP Imagick extension is not loaded but required by the PdfTransformer");
            
            //If it doesn't exists extract the first page of the PDF
            if (!file_exists("$absolutePath.png")) {
                $img = new \Imagick ( $absolutePath.'[0]' );
                $img->setImageFormat('png');
                $img->writeImages($absolutePath.'.png', true);
            }
            //finally update $absolutePath
            $absolutePath .= '.png';
        }
        return $absolutePath;
    }
}