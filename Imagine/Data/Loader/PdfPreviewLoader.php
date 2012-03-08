<?php

namespace Liip\ImagineBundle\Imagine\Data\Loader;

class PdfPreviewLoader extends FileSystemLoader
{
  
    /**
     * Overrides FileSystemLoader's getFileInfo and creates,
     * if it doesn't exists, an image of the first page of
     * the pdf document
     *
     * @param $absolutePath
     * @return array
     */
    protected function getFileInfo($absolutePath)
    {
        $info = pathinfo($absolutePath);
        if (isset($info['extension']) && strpos(strtolower($info['extension']), 'pdf') !== false ) {
            //If it doesn't exists, extract first page of the PDF to png
            if (!file_exists("$absolutePath.png")) {
                $img = new \Imagick ( $absolutePath.'[0]' );
                $img->setImageFormat( "png" );
                $img->writeImages($absolutePath.'.png', true);
            }
            //finally update $absolutePath
            $absolutePath .= '.png';
        }
        return pathinfo($absolutePath);
    }
}
