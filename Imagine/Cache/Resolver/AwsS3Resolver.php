<?php

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use Aws\S3\Enum\CannedAcl;
use Aws\S3\S3Client;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotStorableException;
use Psr\Log\LoggerInterface;

class AwsS3Resolver implements ResolverInterface
{
    /**
     * @var S3Client
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
     * @param S3Client $storage The Amazon S3 storage API. It's required to know authentication information.
     * @param string $bucket The bucket name to operate on.
     * @param string $acl The ACL to use when storing new objects. Default: owner read/write, public read
     * @param array $objUrlOptions A list of options to be passed when retrieving the object url from Amazon S3.
     */
    public function __construct(S3Client $storage, $bucket, $acl = CannedAcl::PUBLIC_READ, array $objUrlOptions = array())
    {
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
    public function isStored($path, $filter)
    {
        return $this->objectExists($this->getObjectPath($path, $filter));
    }

    /**
     * {@inheritDoc}
     */
    public function resolve($path, $filter)
    {
        return $this->getObjectUrl($this->getObjectPath($path, $filter));
    }

    /**
     * {@inheritDoc}
     */
    public function store(BinaryInterface $binary, $path, $filter)
    {
        $objectPath = $this->getObjectPath($path, $filter);

        try {
            $this->storage->putObject(array(
                'ACL'           => $this->acl,
                'Bucket'        => $this->bucket,
                'Key'           => $objectPath,
                'Body'          => $binary->getContent(),
                'ContentType'   => $binary->getMimeType()
            ));
        } catch (\Exception $e) {
            $this->logError('The object could not be created on Amazon S3.', array(
                'objectPath'  => $objectPath,
                'filter'      => $filter,
                'bucket'      => $this->bucket,
                'exception'   => $e,
            ));

            throw new NotStorableException('The object could not be created on Amazon S3.', null, $e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function remove(array $paths, array $filters)
    {
        if (empty($paths) && empty($filters)) {
            return;
        }

        if (empty($paths)) {
            try {
                $this->storage->deleteMatchingObjects($this->bucket, null, sprintf(
                    '/%s/i',
                    implode('|', $filters)
                ));
            } catch (\Exception $e) {
                $this->logError('The objects could not be deleted from Amazon S3.', array(
                    'filter'      => implode(', ', $filters),
                    'bucket'      => $this->bucket,
                    'exception'   => $e,
                ));
            }

            return;
        }

        foreach ($filters as $filter) {
            foreach ($paths as $path) {
                $objectPath = $this->getObjectPath($path, $filter);
                if (!$this->objectExists($objectPath)) {
                    continue;
                }

                try {
                    $this->storage->deleteObject(array(
                        'Bucket' => $this->bucket,
                        'Key'    => $objectPath,
                    ));
                } catch (\Exception $e) {
                    $this->logError('The object could not be deleted from Amazon S3.', array(
                        'objectPath'  => $objectPath,
                        'filter'      => $filter,
                        'bucket'      => $this->bucket,
                        'exception'   => $e,
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
     * @see Aws\S3\S3Client::getObjectUrl() for available options.
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
    protected function getObjectPath($path, $filter)
    {
        return str_replace('//', '/', $filter.'/'.$path);
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
        return $this->storage->getObjectUrl($this->bucket, $path, 0, $this->objUrlOptions);
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
        return $this->storage->doesObjectExist($this->bucket, $objectPath);
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
