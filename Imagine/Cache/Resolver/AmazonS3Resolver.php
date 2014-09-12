<?php

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotStorableException;
use Liip\ImagineBundle\Imagine\Cache\SignerInterface;
use Psr\Log\LoggerInterface;

class AmazonS3Resolver implements ResolverInterface
{
    /**
     * @var \Liip\ImagineBundle\Imagine\Cache\SignerInterface
     */
    protected $signer;

    /**
     * @var \AmazonS3
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
     * @var array
     */
    protected $objUrlOptions;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructs a cache resolver storing images on Amazon S3.
     *
     * @param SignerInterface $signer
     * @param \AmazonS3 $storage The Amazon S3 storage API. It's required to know authentication information.
     * @param string $bucket The bucket name to operate on.
     * @param string $acl The ACL to use when storing new objects. Default: owner read/write, public read
     * @param array $objUrlOptions A list of options to be passed when retrieving the object url from Amazon S3.
     */
    public function __construct(SignerInterface $signer, \AmazonS3 $storage, $bucket, $acl = \AmazonS3::ACL_PUBLIC, array $objUrlOptions = array())
    {
        $this->signer = $signer;
        $this->storage = $storage;
        $this->bucket = $bucket;
        $this->acl = $acl;
        $this->objUrlOptions = $objUrlOptions;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function isStored($path, $filter, array $runtimeConfig = array())
    {
        return $this->objectExists($this->getObjectPath($path, $filter, $runtimeConfig));
    }

    /**
     * {@inheritDoc}
     */
    public function resolve($path, $filter, array $runtimeConfig = array())
    {
        return $this->getObjectUrl($this->getObjectPath($path, $filter, $runtimeConfig));
    }

    /**
     * {@inheritDoc}
     */
    public function store(BinaryInterface $binary, $path, $filter, array $runtimeConfig = array())
    {
        $objectPath = $this->getObjectPath($path, $filter, $runtimeConfig);

        $storageResponse = $this->storage->create_object($this->bucket, $objectPath, array(
            'body' => $binary->getContent(),
            'contentType' => $binary->getMimeType(),
            'length' => strlen($binary->getContent()),
            'acl' => $this->acl,
        ));

        if (!$storageResponse->isOK()) {
            $this->logError('The object could not be created on Amazon S3.', array(
                'objectPath' => $objectPath,
                'filter' => $filter,
                's3_response' => $storageResponse,
            ));

            throw new NotStorableException('The object could not be created on Amazon S3.');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function remove(array $paths, array $filters, array $runtimeConfig = array())
    {
        if (empty($paths) && empty($filters)) {
            return;
        }

        if (empty($paths)) {
            if (!$this->storage->delete_all_objects($this->bucket, sprintf('/%s/i', implode('|', $filters)))) {
                $this->logError('The objects could not be deleted from Amazon S3.', array(
                    'filters'      => implode(', ', $filters),
                    'bucket'      => $this->bucket,
                ));
            }

            return;
        }

        foreach ($filters as $filter) {
            foreach ($paths as $path) {
                $objectPath = $this->getObjectPath($path, $filter, $runtimeConfig);
                if (!$this->objectExists($objectPath)) {
                    continue;
                }

                if (!$this->storage->delete_object($this->bucket, $objectPath)->isOK()) {
                    $this->logError('The objects could not be deleted from Amazon S3.', array(
                        'filter'      => $filter,
                        'bucket'      => $this->bucket,
                        'path'        => $path,
                    ));
                }
            }
        }
    }

    /**
     * Sets a single option to be passed when retrieving an objects URL.
     *
     * If the option is already set, it will be overwritten.
     *
     * @see \AmazonS3::get_object_url() for available options.
     *
     * @param string $key The name of the option.
     * @param mixed $value The value to be set.
     *
     * @return AmazonS3Resolver $this
     */
    public function setObjectUrlOption($key, $value)
    {
        $this->objUrlOptions[$key] = $value;

        return $this;
    }

    /**
     * Returns the object path within the bucket.
     *
     * @param string $path The base path of the resource.
     * @param string $filter The name of the imagine filter in effect.
     *
     * @return string The path of the object on S3.
     */
    protected function getObjectPath($path, $filter, array $runtimeConfig = array())
    {
        if (empty($runtimeConfig)) {
            return str_replace('//', '/', $filter.'/'.$path);
        } else {
            return str_replace('//', '/', $filter.'/rc/'.$this->signer->sign($path, $runtimeConfig).'/'.$path);
        }
    }

    /**
     * Returns the URL for an object saved on Amazon S3.
     *
     * @param string $path
     *
     * @return string
     */
    protected function getObjectUrl($path)
    {
        return $this->storage->get_object_url($this->bucket, $path, 0, $this->objUrlOptions);
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

    /**
     * @param mixed $message
     * @param array $context
     */
    protected function logError($message, array $context = array())
    {
        if ($this->logger) {
            $this->logger->error($message, $context);
        }
    }
}
