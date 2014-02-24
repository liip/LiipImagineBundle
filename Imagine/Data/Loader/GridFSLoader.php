<?php

namespace Liip\ImagineBundle\Imagine\Data\Loader;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GridFSLoader implements LoaderInterface
{
    /**
     * @var DocumentManager
     */
    protected $dm;

    /**
     * @var string
     */
    protected $class;

    /**
     * @param DocumentManager $dm
     * @param string $class
     */
    public function __construct(DocumentManager $dm, $class)
    {
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
            ->find(new \MongoId($id));

        if (!$image) {
            throw new NotFoundHttpException(sprintf('Source image not found with id "%s"', $id));
        }

        return $image->getFile()->getBytes();
    }
}
