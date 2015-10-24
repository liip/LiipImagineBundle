<?php

namespace Liip\ImagineBundle\Binary\Loader;

use Doctrine\Common\Persistence\ObjectManager;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;

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
     * @param string        $class
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
     * Return a stream resource from the Doctrine entity/document with the image content.
     *
     * @param object $image
     *
     * @return resource
     */
    abstract protected function getStreamFromImage($image);

    /**
     * {@inheritdoc}
     */
    public function find($path)
    {
        $image = $this->manager->find($this->class, $this->mapPathToId($path));

        if (!$image) {
            // try to find the image without extension
            $info = pathinfo($path);
            $name = $info['dirname'].'/'.$info['filename'];

            $image = $this->manager->find($this->class, $this->mapPathToId($name));
        }

        if (!$image) {
            throw new NotLoadableException(sprintf('Source image was not found with id "%s"', $path));
        }

        return stream_get_contents($this->getStreamFromImage($image));
    }
}
