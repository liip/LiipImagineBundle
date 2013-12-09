<?php

namespace Liip\ImagineBundle\Imagine\Data\Loader;

use Imagine\Image\ImagineInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class AbstractDoctrineLoader implements LoaderInterface
{
    /**
     * @var ImagineInterface
     */
    protected $imagine;

    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var array
     */
    protected $formats;

    /**
     * Constructor.
     *
     * @param ImagineInterface $imagine
     * @param ObjectManager $manager
     * @param string $class
     * @param array $formats possible image formats to look up file ids
     */
    public function __construct(
        ImagineInterface $imagine,
        ObjectManager $manager,
        $class = null,
        array $formats = array()
    ) {
        $this->imagine = $imagine;
        $this->manager = $manager;
        $this->class = $class;
        $this->formats = $formats;
    }

    /**
     * Map the requested path (ie. subpath in the URL) to an id that can be used to lookup the image in the Doctrine store.
     *
     * @param string $path
     *
     * @return string
     */
    abstract protected function mapPathToId($path);

    /**
     * Return a stream resource from the Doctrine entity/document with the image content
     *
     * @param object $image
     *
     * @return resource
     */
    abstract protected function getStreamFromImage($image);

    /**
     * @param string $path
     *
     * @return \Imagine\Image\ImageInterface
     */
    public function find($path)
    {
        $image = $this->manager->find($this->class, $this->mapPathToId($path));

        if (!$image) {
            // try to find the image without extension
            $info = pathinfo($path);
            $name = $info['dirname'].'/'.$info['filename'];

            // attempt to determine available format
            foreach ($this->formats as $format) {
                if ($image = $this->manager->find($this->class, $this->mapPathToId($name.'.'.$format))) {
                    break;
                }
            }

            // maybe the image has an id without extension
            if (!$image) {
                $image = $this->manager->find($this->class, $this->mapPathToId($name));
            }
        }

        if (!$image) {
            throw new NotFoundHttpException(sprintf('Source image not found with id "%s"', $path));
        }

        return $this->imagine->load(stream_get_contents($this->getStreamFromImage($image)));
    }
}
