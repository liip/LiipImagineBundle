<?php

namespace Liip\ImagineBundle\Imagine\Data\Loader;

use Imagine\Image\ImagineInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FileSystemLoader implements LoaderInterface
{
    /**
     * @var ImagineInterface
     */
    protected $imagine;

    /**
     * @var array
     */
    protected $formats;

    /**
     * @var string
     */
    protected $rootPath;

    /**
     * Constructor.
     *
     * @param ImagineInterface  $imagine
     * @param array             $formats
     * @param string            $rootPath
     */
    public function __construct(ImagineInterface $imagine, array $formats, $rootPath)
    {
        $this->imagine = $imagine;
        $this->formats = $formats;
        $this->rootPath = realpath($rootPath);
    }


    /**
     * Get the file info for the given path.
     *
     * This can optionally be used to generate the given file.
     *
     * @param string $absolutePath
     *
     * @return array
     */
    protected function getFileInfo($absolutePath)
    {
        return new \SplFileInfo($absolutePath);
    }

    /**
     * {@inheritDoc}
     */
    public function find($path)
    {
        if (false !== strpos($path, '/../') || 0 === strpos($path, '../')) {
            throw new NotFoundHttpException(sprintf("Source image was searched with '%s' out side of the defined root path", $path));
        }

        $file = $this->rootPath.'/'.ltrim($path, '/');
        $info = $this->getFileInfo($file);
        $dirName = $info->getPath();
        $extension = $info->getExtension();
        $absolutePath = $dirName . DIRECTORY_SEPARATOR . $info->getFilename();
        $name = $dirName . DIRECTORY_SEPARATOR . $info->getBasename('.'.$info->getExtension());
        $targetFormat = null;
        // set a format if an extension is found and is allowed
        if (isset($extension)
            && (empty($this->formats) || in_array($info->getExtension(), $this->formats))
        ) {
            $targetFormat = $info->getExtension();
        }
        if (empty($targetFormat) || !file_exists($absolutePath)) {
            // attempt to determine path and format
            $absolutePath = null;
            foreach ($this->formats as $format) {
                if ($targetFormat !== $format && file_exists($name.'.'.$format)) {
                    $absolutePath = $name.'.'.$format;

                    break;
                }
            }

            if (!$absolutePath) {
                if (!empty($targetFormat) && is_file($name)) {
                    $absolutePath = $name;
                } else {
                    throw new NotFoundHttpException(sprintf('Source image not found in "%s"', $file));
                }
            }
        }

        return $this->imagine->open($absolutePath);
    }
}
