<?php

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use \AmazonS3;

use Liip\ImagineBundle\Imagine\Cache\CacheManagerAwareInterface,
    Liip\ImagineBundle\Imagine\Cache\CacheManager;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\RedirectResponse,
    Symfony\Component\HttpFoundation\Response;

class AmazonS3Resolver implements ResolverInterface, CacheManagerAwareInterface
{
    /**
     * @var AmazonS3
     */
    protected $storage;

    /**
     * @var string
     */
    protected $bucket;

    /**
     * @var string
     */
    protected $acl;

    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * Constructs a cache resolver storing images on Amazon S3.
     *
     * @throws \S3_Exception While checking for existence of the bucket.
     *
     * @param \AmazonS3 $storage The Amazon S3 storage API. It's required to know authentication information.
     * @param string $bucket The bucket name to operate on.
     * @param string $acl The ACL to use when storing new objects. Default: owner read/write, public read
     */
    public function __construct(AmazonS3 $storage, $bucket, $acl = AmazonS3::ACL_PUBLIC)
    {
        $this->storage = $storage;
        $this->storage->if_bucket_exists($bucket);

        $this->bucket = $bucket;
        $this->acl = $acl;
    }

    /**
     * @param CacheManager $cacheManager
     */
    public function setCacheManager(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(Request $request, $path, $filter)
    {
        return $this->getObjectPath($path, $filter);
    }

    /**
     * {@inheritDoc}
     */
    public function store(Response $response, $targetPath, $filter)
    {
        $storageResponse = $this->storage->create_object($this->bucket, $targetPath, array(
            'body' => $response->getContent(),
            'contentType' => $response->headers->get('Content-Type'),
            'length' => strlen($response->getContent()),
            'acl' => $this->acl,
        ));

        if ($storageResponse->isOK()) {
            $response->setStatusCode(301);
            $response->headers->set('Location', $this->getObjectUrl($targetPath));
        }

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function getBrowserPath($targetPath, $filter, $absolute = false)
    {
        $objectPath = $this->getObjectPath($targetPath, $filter);
        if ($this->objectExists($objectPath)) {
            return $this->getObjectUrl($targetPath);
        }

        $params = array('path' => ltrim($targetPath, '/'));

        return str_replace(
            urlencode($params['path']),
            urldecode($params['path']),
            $this->cacheManager->getRouter()->generate('_imagine_'.$filter, $params, $absolute)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function remove($targetPath, $filter)
    {
        $objectPath = $this->getObjectPath($targetPath, $filter);
        if (!$this->objectExists($objectPath)) {
            // A non-existing object to delete: done!
            return true;
        }

        return $this->storage->delete_object($this->bucket, $objectPath)->isOK();
    }

    /**
     * Returns the object path within the bucket.
     *
     * @param string $path The base path of the resource.
     * @param string $filter The name of the imagine filter in effect.
     *
     * @return string The path of the object on S3.
     */
    protected function getObjectPath($path, $filter)
    {
        return str_replace('//', '/', $filter.'/'.$path);
    }

    /**
     * Returns the URL for an object saved on Amazon S3.
     *
     * @param string $targetPath
     *
     * @return string
     */
    protected function getObjectUrl($targetPath)
    {
        return $this->storage->get_object_url($this->bucket, $targetPath);
    }

    /**
     * Checks whether an object exists.
     *
     * @param string $objectPath
     *
     * @return bool
     */
    protected function objectExists($objectPath)
    {
        return $this->storage->if_object_exists($this->bucket, $objectPath);
    }
}
