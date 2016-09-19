<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

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
     * {@inheritdoc}
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
