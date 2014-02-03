<?php

namespace Liip\ImagineBundle\Imagine\Data\Loader;

use Imagine\Image\ImagineInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class AbstractDoctrineLoader implements LoaderInterface
{
    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @var string
     */
    protected $class;

    /**
     * @param ObjectManager $manager
     * @param string $class
     */
    public function __construct(ObjectManager $manager, $class = null)
    {
        $this->manager = $manager;
        $this->class = $class;
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
     * {@inheritDoc}
     */
    public function find($path)
    {
        $image = $this->manager->find($this->class, $this->mapPathToId($path));

        if (!$image) {
            throw new NotFoundHttpException(sprintf('Source image not found with id "%s"', $path));
        }

        return stream_get_contents($this->getStreamFromImage($image));
    }
}
