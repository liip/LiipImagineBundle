<?php

namespace Liip\ImagineBundle\Imagine\Data\Loader;

use Imagine\Image\ImagineInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GridFSLoader implements LoaderInterface
{
    /**
     * @var ImagineInterface
     */
    protected $imagine;

    /**
     * @var DocumentManager
     */
    protected $dm;

    /**
     * @var string
     */
    protected $class;

    /**
     * Constructs
     *
     * @param ImagineInterface $imagine
     * @param DocumentManager $dm
     * @param string $class
     */
    public function __construct(ImagineInterface $imagine, DocumentManager $dm, $class)
    {
        $this->imagine = $imagine;
        $this->dm = $dm;
        $this->class = $class;
    }

    /**
     * {@inheritDoc}
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
