<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Events;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Symfony\Component\EventDispatcher\Event;

class CacheStoreEvent extends Event
{
    /**
     * Binary.
     *
     * @var BinaryInterface
     */
    protected $binary;

    /**
     * Resource path.
     *
     * @var string
     */
    protected $path;

    /**
     * Filter name.
     *
     * @var string
     */
    protected $filter;

    /**
     * Resolver.
     *
     * @var null|string
     */
    protected $resolver;

    /**
     * Init default event state.
     *
     * @param BinaryInterface $binary
     * @param string          $path
     * @param string          $filter
     * @param null|string     $resolver
     */
    public function __construct(BinaryInterface $binary, $path, $filter, $resolver = null)
    {
        $this->binary = $binary;
        $this->path = $path;
        $this->filter = $filter;
        $this->resolver = $resolver;
    }

    /**
     * Sets the binary
     *
     * @param BinaryInterface $binary
     */
    public function setBinary(BinaryInterface $binary)
    {
        $this->binary = $binary;
    }

    /**
     * Returns the binary
     *
     * @return BinaryInterface
     */
    public function getBinary()
    {
        return $this->binary;
    }

    /**
     * Sets resource path.
     *
     * @param $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Returns resource path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Sets filter name.
     *
     * @param $filter
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;
    }

    /**
     * Returns filter name.
     *
     * @return string
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Sets the resolver
     *
     * @param null|string $resolver
     */
    public function setResolver(?string $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Returns the resolver.
     *
     * @return null|string
     */
    public function getResolver()
    {
        return $this->resolver;
    }
}
