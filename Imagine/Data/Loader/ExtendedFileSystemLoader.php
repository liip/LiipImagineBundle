<?php

namespace Liip\ImagineBundle\Imagine\Data\Loader;

use Imagine\Image\ImagineInterface;
use Liip\ImagineBundle\Imagine\Data\Transformer\TransformerInterface;

class ExtendedFileSystemLoader extends FileSystemLoader
{
    /**
     * @var TransformerInterface[]
     */
    protected $transformers;

    /**
     * Constructor.
     *
     * @param ImagineInterface       $imagine
     * @param array                  $formats
     * @param string                 $rootPath
     * @param TransformerInterface[] $transformers
     */
    public function __construct(ImagineInterface $imagine, $formats, $rootPath, array $transformers)
    {
        parent::__construct($imagine, $formats, $rootPath);

        $this->transformers = $transformers;
    }

    /**
     * Apply transformers to the file.
     *
     * @param $absolutePath
     *
     * @return array
     */
    protected function getFileInfo($absolutePath)
    {
        if (!empty($this->transformers)) {
            foreach ($this->transformers as $transformer) {
                $absolutePath = $transformer->apply($absolutePath);
            }
        }

        return pathinfo($absolutePath);
    }
}
