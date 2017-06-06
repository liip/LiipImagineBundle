<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Binary\Locator;

use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Liip\ImagineBundle\Exception\InvalidArgumentException;

class FileSystemLocator implements LocatorInterface
{
    /**
     * @var string[]
     */
    private $roots = array();

    public function __construct(array $roots = array())
    {
        if (count($roots) === 0) {
            throw new InvalidArgumentException('One or more data root paths must be specified');
        }

        $this->roots = array_map(array($this, 'sanitizeRootPath'), $roots);
    }

    /**
     * @param string $path
     *
     * @throws NotLoadableException
     *
     * @return string
     */
    public function locate($path)
    {
        if (false !== $absolute = $this->locateUsingRootPlaceholder($path)) {
            return $this->sanitizeAbsolutePath($absolute);
        }

        if (false !== $absolute = $this->locateUsingRootPathsSearch($path)) {
            return $this->sanitizeAbsolutePath($absolute);
        }

        throw new NotLoadableException(sprintf('Source image not resolvable "%s" in root path(s) "%s"',
            $path, implode(':', $this->roots)));
    }

    /**
     * @param string $path
     *
     * @return bool|string
     */
    private function locateUsingRootPathsSearch($path)
    {
        foreach ($this->roots as $root) {
            if (false !== $absolute = $this->generateAbsolutePath($root, $path)) {
                return $absolute;
            }
        }

        return false;
    }

    /**
     * @param string $path
     *
     * @return bool|string
     */
    private function locateUsingRootPlaceholder($path)
    {
        if (0 !== strpos($path, '@') || 1 !== preg_match('{@(?<name>[^:]+):(?<path>.+)}', $path, $matches)) {
            return false;
        }

        if (isset($this->roots[$matches['name']])) {
            return $this->generateAbsolutePath($this->roots[$matches['name']], $matches['path']);
        }

        throw new NotLoadableException(sprintf('Invalid root placeholder "%s" for path "%s"',
            $matches['name'], $matches['path']));
    }

    /**
     * @param string $root
     * @param string $path
     *
     * @return string|false
     */
    protected function generateAbsolutePath($root, $path)
    {
        return realpath($root.DIRECTORY_SEPARATOR.$path);
    }

    /**
     * @param string $root
     *
     * @throws InvalidArgumentException
     *
     * @return string
     */
    private function sanitizeRootPath($root)
    {
        if (!empty($root) && false !== $realRoot = realpath($root)) {
            return $realRoot;
        }

        throw new InvalidArgumentException(sprintf('Root image path not resolvable "%s"', $root));
    }

    /**
     * @param string $path
     *
     * @throws NotLoadableException
     *
     * @return string
     */
    private function sanitizeAbsolutePath($path)
    {
        foreach ($this->roots as $root) {
            if (0 === strpos($path, $root)) {
                return $path;
            }
        }

        throw new NotLoadableException(sprintf('Source image invalid "%s" as it is outside of the defined root path(s) "%s"',
            $path, implode(':', $this->roots)));
    }
}
