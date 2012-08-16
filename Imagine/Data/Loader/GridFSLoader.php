<?php

namespace Liip\ImagineBundle\Imagine\Data\Loader;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Imagine\Image\ImagineInterface;
use Doctrine\ODM\MongoDB\DocumentManager;

class GridFSLoader implements LoaderInterface
{
    /**
     * @var Imagine\Image\ImagineInterface
     */
    protected $imagine;

    /**
     * @var Doctrine\ODM\MongoDB\DocumentManager
     */
    protected $dm;

    /**
     * @var Image Class
     */
    protected $class;

    /**
     * Constructs
     *
     * @param ImagineInterface  $imagine
     * @param DocumentManager $dm
     * @param string Image class
     */
    public function __construct(ImagineInterface $imagine, DocumentManager $dm, $class)
    {
        $this->imagine = $imagine;
        $this->dm = $dm;
        $this->class = $class;
    }

    /**
     * @param string $id
     *
     * @return Imagine\Image\ImageInterface
     */
    public function find($id)
    {
        $image = $this->dm
            ->getRepository($this->class)
            ->findAll()
            ->getCollection()
            ->findOne(array("_id" => new \MongoId($id)));

        if (!$image) {
            throw new NotFoundHttpException(sprintf('Source image not found with id "%s"', $id));
        }

        return $this->imagine->load($image['file']->getBytes());
    }
}
