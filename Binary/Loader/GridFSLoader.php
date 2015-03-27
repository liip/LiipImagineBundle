<?php

namespace Liip\ImagineBundle\Binary\Loader;

use Doctrine\ODM\MongoDB\DocumentManager;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;

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
     * @param string          $class
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
            throw new NotLoadableException(sprintf('Source image was not found with id "%s"', $id));
        }

        return $image->getFile()->getBytes();
    }
}
