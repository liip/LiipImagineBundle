<?php

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use Symfony\Component\HttpFoundation\Request,
	Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\RedirectResponse,
    Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Liip\ImagineBundle\Imagine\Cache\CacheManagerAwareInterface,
    Liip\ImagineBundle\Imagine\Cache\CacheManager;

class AmazonS3Resolver extends WebPathResolver implements CacheManagerAwareInterface
{
	/**
	 * @var Doctrine\Bundle\DoctrineBundle\Registry
	 */
	protected $doctrine = NULL;
	
	/**
     * Constructs cache web path resolver
     *
     * @param Filesystem  $filesystem
     */
    public function __construct($filesystem, $doctrine)
    {
    	$this->doctrine = $doctrine;

    	// Construct the web path resolver
    	parent::__construct($filesystem);
    }	
    
	/**
     * @throws \RuntimeException
     * @param Response $response
     * @param string $targetPath
     * @param string $filter
     *
     * @return Response
     */
    public function store(Response $response, $targetPath, $filter)
    {
    	// Construct a filename to store on s3, prefixed by the filter as the folder.
    	$filename = $filter . '/' . pathinfo($targetPath, PATHINFO_BASENAME);
    	
    	// Check if the image is already queued
    	$existingImage = $this->doctrine->getRepository('LiipImagineBundle:LiipS3Image')->findOneByFilename($filename);
    	
    	// If the image isn't already queued, store it inside the db
    	if( is_null($existingImage) )
    	{    	
	    	// Create the entity object
	    	$image = new \Liip\ImagineBundle\Entity\LiipS3Image();
	    	$image->setFilename($filename);
	    	$image->setData($response->getContent());
	    	$image->setMimetype($response->headers->get('Content-Type'));
	    	
	    	// Save the record to the db
	    	$em = $this->doctrine->getEntityManager();
	    	$em->persist($image);
	    	$em->flush();
    	}
    	
    	// Store the image on the file system as well, using the WebPathResolver's store method
    	return parent::store($response, $targetPath, $filter);
    }
	
    /**
     * Resolves filtered path for rendering in the browser
     *
     * @param Request $request
     * @param string $path
     * @param string $filter
     *
     * @return string target path
     */
    public function resolve(Request $request, $path, $filter)
    {
    	// Find the image inside the s3 queue
    	$image = $this->doctrine->getRepository('LiipImagineBundle:LiipS3Image')->findOneByFilename($filter . '/' . $path);
    	
    	// If the image is not inside the queue, or it has no URL (ie. has not been uploaded to s3) then resolve to the web path
    	if( is_null($image) or is_null($image->getUrl()) )
    	{
    		return parent::resolve($request, $path, $filter);
    	}
    	else
    	{
    		// Return a 302 redirect to the image on s3,  so the browser can remember the destination
    		return new RedirectResponse($image->getUrl());
    	}    
    }
}
