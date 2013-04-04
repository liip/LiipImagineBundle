<?php

namespace Liip\ImagineBundle\Imagine\Data\Loader;

use Doctrine\ODM\PHPCR\DocumentManager;
use Imagine\Image\ImagineInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DoctrinePHPCRLoader extends FileSystemLoader
{
    /**
     * @var DocumentManager
     */
    protected $manager;

    /**
     * @var string
     */
    protected $class;

    /**
     * Constructor.
     *
     * @param ImagineInterface  $imagine
     * @param array             $formats
     * @param string            $rootPath
     * @param DocumentManager   $manager
     * @param string            $class
     */
    public function __construct(ImagineInterface $imagine, array $formats, $rootPath, DocumentManager $manager, $class = null)
    {
        parent::__construct($imagine, $formats, $rootPath);

        $this->manager = $manager;
        $this->class = $class;
        $this->rootPath = $rootPath;
    }

    protected function getStreamFromImage($image)
    {
        return $image->getContent();
    }

    /**
     * @param string $path
     *
     * @return \Imagine\Image\ImageInterface
     */
    public function find($path)
    {
        $file = $this->rootPath.'/'.ltrim($path, '/');
        $info = $this->getFileInfo($file);
        $name = $info['dirname'].'/'.$info['filename'];

        // consider full path as provided (with or without an extension)
        $paths = array($file);
        foreach ($this->formats as $format) {
            // consider all possible alternative extensions
            if (empty($info['extension']) || $info['extension'] !== $format) {
                $paths[] = $name.'.'.$format;
            }
        }

        // if the full path contained an extension, also consider the full path without an extension
        if ($file !== $name) {
            $paths[] = $name;
        }

        $images = $this->manager->findMany($this->class, $paths);
        if (!$images->count()) {
            throw new NotFoundHttpException(sprintf('Source image not found with id "%s"', $path));
        }

        return $this->imagine->load(stream_get_contents($this->getStreamFromImage($images->first())));
    }
}
