<?php

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotResolvableException;

interface ResolverInterface
{
    /**
     * Checks whether the given path is stored within this Resolver.
     *
     * @param string $path
     * @param string $filter
     * @param array  $runtimeConfig
     *
     * @return bool
     */
    function isStored($path, $filter, array $runtimeConfig = array());

    /**
     * Resolves filtered path for rendering in the browser.
     *
     * @param string $path   The path where the original file is expected to be.
     * @param string $filter The name of the imagine filter in effect.
     *
     * @return string The absolute URL of the cached image.
     *
     * @throws NotResolvableException
     */
    function resolve($path, $filter, array $runtimeConfig = array());

    /**
     * Stores the content of the given binary.
     *
     * @param BinaryInterface $binary The image binary to store.
     * @param string          $path     The path where the original file is expected to be.
     * @param string          $filter   The name of the imagine filter in effect.
     * @param array           $runtimeConfig
     *
     * @return void
     */
    function store(BinaryInterface $binary, $path, $filter, array $runtimeConfig = array());

    /**
     * @param string[] $paths   The paths where the original files are expected to be.
     * @param string[] $filters The imagine filters in effect.
     * @param array    $runtimeConfig
     *
     * @return void
     */
    function remove(array $paths, array $filters, array $runtimeConfig = array());
}
