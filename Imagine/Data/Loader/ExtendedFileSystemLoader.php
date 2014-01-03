<?php

namespace Liip\ImagineBundle\Imagine\Data\Loader;

use Liip\ImagineBundle\Imagine\Data\Transformer\TransformerInterface;

class ExtendedFileSystemLoader extends FileSystemLoader
{
    /**
     * @var TransformerInterface[]
     */
    protected $transformers;

    /**
     * @param array                  $formats
     * @param string                 $rootPath
     * @param TransformerInterface[] $transformers
     */
    public function __construct($formats, $rootPath, array $transformers)
    {
        parent::__construct($formats, $rootPath);

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
