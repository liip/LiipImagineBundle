<?php

namespace Liip\ImagineBundle\Imagine\Data\Loader;

use Imagine\Image\ImagineInterface;
use Doctrine\Common\Persistence\ObjectManager;

class DoctrinePHPCRLoader extends AbstractDoctrineLoader
{
    /**
     * @var string
     */
    protected $rootPath;

    /**
     * Constructor.
     *
     * @param ImagineInterface $imagine
     * @param ObjectManager $manager
     * @param string $class
     * @param string $rootPath
     */
    public function __construct(ImagineInterface $imagine, ObjectManager $manager, $class = null, $rootPath = '')
    {
        parent::__construct($imagine, $manager, $class);

        $this->rootPath = $rootPath;
    }

    protected function mapPathToId($path)
    {
        return $this->rootPath.'/'.ltrim($path, '/');
    }

    protected function getStreamFromImage($image)
    {
        return $image->getContent();
    }
}
