<?php

namespace Liip\ImagineBundle\Imagine\Data\Loader;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Imagine\Image\ImagineInterface;

class DbLoader implements LoaderInterface
{
    /**
     * @var Imagine\Image\ImagineInterface
     */
    protected $imagine;

    /**
     * @var Doctrine\Bundle\DoctrineBundle\Registry
     */
    protected $doctrine;

    /**
     * Constructs
     *
     * @param ImagineInterface  $imagine
     * @param array             $formats
     * @param string            $rootPath
     */
    public function __construct(ImagineInterface $imagine, \Doctrine\Bundle\DoctrineBundle\Registry $doctrine)
    {
    	$this->imagine = $imagine;
        $this->doctrine = $doctrine;
    }

    /**
     * @param string $path
     *
     * @return Imagine\Image\ImageInterface
     */
    public function find($uniqueIdentifier)
    {
    	$image = $this->doctrine->getRepository('LiipImagineBundle:LiipImage')->findOneByUniqueIdentifier($uniqueIdentifier);
        
        if( is_null($image) )
        {
        	throw new NotFoundHttpException(sprintf('Could not find image by unique identifier "%s"', $uniqueIdentifier));
        }
        
        return $this->imagine->load($image->getData());
    }
}
