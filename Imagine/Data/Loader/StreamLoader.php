<?php

namespace Liip\ImagineBundle\Imagine\Data\Loader;

use Imagine\Image\ImagineInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StreamLoader implements LoaderInterface
{
    /**
     * @var ImagineInterface
     */
    protected $imagine;

    /**
     * The wrapper prefix to append to the path to be loaded.
     *
     * @var string
     */
    protected $wrapperPrefix;

    /**
     * A stream context resource to use.
     *
     * @var resource|null
     */
    protected $context;

    /**
     * Constructor.
     *
     * @param ImagineInterface $imagine
     * @param string $wrapperPrefix
     * @param resource|null $context
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(ImagineInterface $imagine, $wrapperPrefix, $context = null)
    {
        $this->imagine = $imagine;
        $this->wrapperPrefix = $wrapperPrefix;

        if ($context && !is_resource($context)) {
            throw new \InvalidArgumentException('The given context is no valid resource.');
        }

        $this->context = $context;
    }

    /**
     * {@inheritDoc}
     */
    public function find($path)
    {
        $name = $this->wrapperPrefix.$path;

        /*
         * This looks strange, but at least in PHP 5.3.8 it will raise an E_WARNING if the 4th parameter is null.
         * fopen() will be called only once with the correct arguments.
         *
         * The error suppression is solely to determine whether the file exists.
         * file_exists() is not used as not all wrappers support stat() to actually check for existing resources.
         */
        if (($this->context && !$resource = @fopen($name, 'r', null, $this->context)) || !$resource = @fopen($name, 'r')) {
            throw new NotFoundHttpException('Source image not found.');
        }

        // Closing the opened stream to avoid locking of the resource to find.
        fclose($resource);

        return $this->imagine->load(file_get_contents($name, null, $this->context));
    }
}
