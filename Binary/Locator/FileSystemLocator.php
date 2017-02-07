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
use Symfony\Component\OptionsResolver\OptionsResolver;

class FileSystemLocator implements LocatorInterface
{
    /**
     * @var string[]
     */
    protected $roots;

    /**
     * @param array[] $options
     */
    public function setOptions(array $options = array())
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefaults(array('roots' => array()));
        $options = $optionsResolver->resolve($options);

        $this->roots = array_map(array($this, 'sanitizeRootPath'), (array) $options['roots']);
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
        foreach ($this->roots as $root) {
            if (false !== $absolute = $this->generateAbsolutePath($root, $path)) {
                return $this->sanitizeAbsolutePath($absolute);
            }
        }

        throw new NotLoadableException(sprintf('Source image not resolvable "%s" in root path(s) "%s"',
            $path, implode(':', $this->roots)));
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
    protected function sanitizeRootPath($root)
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
