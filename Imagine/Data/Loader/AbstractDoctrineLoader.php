<?php

namespace Liip\ImagineBundle\Imagine\Data\Loader;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Imagine\Image\ImagineInterface;
use Doctrine\Common\Persistence\ObjectManager;

abstract class AbstractDoctrineLoader implements LoaderInterface
{
    /**
     * @var Imagine\Image\ImagineInterface
     */
    protected $imagine;

    /**
     * @var Doctrine\Common\Persistence\ObjectManager
     */
    protected $manager;

    /**
     * @var Image Class
     */
    protected $class;

    /**
     * Constructs
     *
     * @param ImagineInterface  $imagine
     * @param ObjectManager $manager
     * @param string Image class
     */
    public function __construct(ImagineInterface $imagine, ObjectManager $manager, $class = null)
    {
        $this->imagine = $imagine;
        $this->manager = $manager;
        $this->class = $class;
    }

    abstract protected function getConvertId($id);
    abstract protected function getStreamFromImage($image);

    /**
     * @param string $id
     *
     * @return Imagine\Image\ImageInterface
     */
    public function find($id)
    {
        $image = $this->manager->find($this->class, $this->getConvertId($id));

        if (!$image) {
            throw new NotFoundHttpException(sprintf('Source image not found with id "%s"', $id));
        }

        return $this->imagine->load(stream_get_contents($this->getStreamFromImage($image)));
    }
}
