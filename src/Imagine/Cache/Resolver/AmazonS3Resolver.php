<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotStorableException;
use Psr\Log\LoggerInterface;

class AmazonS3Resolver implements ResolverInterface
{
    protected \AmazonS3 $storage;

    protected string $bucket;

    protected string $acl;

    protected array $objUrlOptions;

    protected ?LoggerInterface $logger = null;

    /**
     * Constructs a cache resolver storing images on Amazon S3.
     *
     * @param \AmazonS3 $storage       The Amazon S3 storage API. It's required to know authentication information
     * @param string    $bucket        The bucket name to operate on
     * @param string    $acl           The ACL to use when storing new objects. Default: owner read/write, public read
     * @param array     $objUrlOptions A list of options to be passed when retrieving the object url from Amazon S3
     */
    public function __construct(\AmazonS3 $storage, string $bucket, string $acl = \AmazonS3::ACL_PUBLIC, array $objUrlOptions = [])
    {
        $this->storage = $storage;
        $this->bucket = $bucket;
        $this->acl = $acl;
        $this->objUrlOptions = $objUrlOptions;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function isStored(string $path, string $filter): bool
    {
        return $this->objectExists($this->getObjectPath($path, $filter));
    }

    public function resolve(string $path, string $filter): string
    {
        return $this->getObjectUrl($this->getObjectPath($path, $filter));
    }

    public function store(BinaryInterface $binary, string $path, string $filter): void
    {
        $objectPath = $this->getObjectPath($path, $filter);

        $storageResponse = $this->storage->create_object($this->bucket, $objectPath, [
            'body' => $binary->getContent(),
            'contentType' => $binary->getMimeType(),
            'length' => mb_strlen($binary->getContent()),
            'acl' => $this->acl,
        ]);

        if (!$storageResponse->isOK()) {
            $this->logError('The object could not be created on Amazon S3.', [
                'objectPath' => $objectPath,
                'filter' => $filter,
                's3_response' => $storageResponse,
            ]);

            throw new NotStorableException('The object could not be created on Amazon S3.');
        }
    }

    public function remove(array $paths, array $filters): void
    {
        if (empty($paths) && empty($filters)) {
            return;
        }

        if (empty($paths)) {
            if (!$this->storage->delete_all_objects($this->bucket, sprintf('/%s/i', implode('|', $filters)))) {
                $this->logError('The objects could not be deleted from Amazon S3.', [
                    'filters' => implode(', ', $filters),
                    'bucket' => $this->bucket,
                ]);
            }

            return;
        }

        foreach ($filters as $filter) {
            foreach ($paths as $path) {
                $objectPath = $this->getObjectPath($path, $filter);
                if (!$this->objectExists($objectPath)) {
                    continue;
                }

                if (!$this->storage->delete_object($this->bucket, $objectPath)->isOK()) {
                    $this->logError('The objects could not be deleted from Amazon S3.', [
                        'filter' => $filter,
                        'bucket' => $this->bucket,
                        'path' => $path,
                    ]);
                }
            }
        }
    }

    /**
     * Sets a single option to be passed when retrieving an objects URL.
     *
     * If the option is already set, it will be overwritten.
     *
     * @param string $key   The name of the option
     * @param mixed  $value The value to be set
     *
     * @return AmazonS3Resolver $this
     *
     * @see \AmazonS3::get_object_url() for available options
     */
    public function setObjectUrlOption(string $key, $value): self
    {
        $this->objUrlOptions[$key] = $value;

        return $this;
    }

    /**
     * Returns the object path within the bucket.
     *
     * @param string $path   The base path of the resource
     * @param string $filter The name of the imagine filter in effect
     *
     * @return string The path of the object on S3
     */
    protected function getObjectPath(string $path, string $filter): string
    {
        return str_replace('//', '/', $filter.'/'.$path);
    }

    /**
     * Returns the URL for an object saved on Amazon S3.
     */
    protected function getObjectUrl(string $path): string
    {
        return $this->storage->get_object_url($this->bucket, $path, 0, $this->objUrlOptions);
    }

    /**
     * Checks whether an object exists.
     *
     * @throws \S3_Exception
     */
    protected function objectExists(string $objectPath): bool
    {
        return $this->storage->if_object_exists($this->bucket, $objectPath);
    }

    protected function logError($message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->error($message, $context);
        }
    }
}
