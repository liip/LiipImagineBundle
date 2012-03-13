<?php

namespace Liip\ImagineBundle\Imagine\Data\Loader;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Imagine\Image\ImagineInterface;

class ExtendedFileSystemLoader extends FileSystemLoader
{
    /**
     *
     * @var array 
     */
    private $transformers;

    /**
     * Constructs
     *
     * @param ImagineInterface  $imagine
     * @param array             $formats
     * @param string            $rootPath
     * @param array             $transformers
     */
    public function __construct(ImagineInterface $imagine, $formats, $rootPath, array $transformers)
    {
        parent::__construct($imagine, $formats, $rootPath);
        $this->transformers = $transformers;
    }

    /**
     * Apply transformers to the file
     *
     * @param $absolutePath
     * @return array
     */
    protected function getFileInfo($absolutePath)
    {
        if (!empty($this->transformers)) {
            foreach ($this->transformers as $transformer) {
                $absolutePath = $transformer->applyTransform($absolutePath);
            }
        }
        return pathinfo($absolutePath);
    }
}
