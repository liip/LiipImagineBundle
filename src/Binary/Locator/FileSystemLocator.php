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
    private array $roots = [];

    /**
     * @param string[] $roots
     */
    public function __construct(array $roots = [], bool $allowUnresolvable = false)
    {
        $this->roots = array_filter(array_map(function (string $root) use ($allowUnresolvable): ?string {
            return $this->sanitizeRootPath($root, $allowUnresolvable);
        }, $roots));
    }

    /**
     * @throws NotLoadableException
     */
    public function locate(string $path): string
    {
        if (null !== $absolute = $this->locateUsingRootPlaceholder($path)) {
            return $this->sanitizeAbsolutePath($absolute);
        }

        if (null !== $absolute = $this->locateUsingRootPathsSearch($path)) {
            return $this->sanitizeAbsolutePath($absolute);
        }

        throw new NotLoadableException(sprintf('Source image not resolvable "%s" in root path(s) "%s"', $path, implode(':', $this->roots)));
    }

    protected function generateAbsolutePath(string $root, string $path): ?string
    {
        if (false !== $absolute = realpath($root.DIRECTORY_SEPARATOR.$path)) {
            return $absolute;
        }

        return null;
    }

    private function locateUsingRootPathsSearch(string $path): ?string
    {
        foreach ($this->roots as $root) {
            if (null !== $absolute = $this->generateAbsolutePath($root, $path)) {
                return $absolute;
            }
        }

        return null;
    }

    private function locateUsingRootPlaceholder(string $path): ?string
    {
        if (!str_starts_with($path, '@') || 1 !== preg_match('{^@(?<name>[^:]+):(?<path>.+)$}', $path, $match)) {
            return null;
        }

        if (isset($this->roots[$match['name']])) {
            return $this->generateAbsolutePath($this->roots[$match['name']], $match['path']);
        }

        throw new NotLoadableException(sprintf('Invalid root placeholder "@%s" for path "%s"', $match['name'], $match['path']));
    }

    /**
     * @throws InvalidArgumentException
     */
    private function sanitizeRootPath(string $path, bool $allowUnresolvable): ?string
    {
        if (!empty($path) && false !== $real = realpath($path)) {
            return $real;
        }

        if ($allowUnresolvable) {
            return null;
        }

        throw new InvalidArgumentException(sprintf('Root image path not resolvable "%s"', $path));
    }

    /**
     * @throws NotLoadableException
     */
    private function sanitizeAbsolutePath(string $path): string
    {
        $roots = array_filter($this->roots, function (string $root) use ($path): bool {
            return 0 === mb_strpos($path, $root);
        });

        if (0 === \count($roots)) {
            throw new NotLoadableException(sprintf('Source image invalid "%s" as it is outside of the defined root path(s) "%s"', $path, implode(':', $this->roots)));
        }

        if (!is_readable($path)) {
            throw new NotLoadableException(sprintf('Source image invalid "%s" as it is not readable', $path));
        }

        return $path;
    }
}
